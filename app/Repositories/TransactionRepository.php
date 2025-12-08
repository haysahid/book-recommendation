<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionRepository
{
    public static function getTransactions(
        $storeId = null,
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $typeId = null,
        $brandId = null,
    ) {
        $transactions = Transaction::query();

        $transactions->with([
            'type',
            'user',
            'payment_method',
            'shipping_method',
            'items.book',
            'invoices' => function ($query) use ($storeId) {
                if ($storeId) {
                    $query->where('store_id', $storeId);
                }
            },
        ]);

        if ($typeId) {
            $transactions->where('type_id', $typeId);
        }

        if ($brandId) {
            $transactions->whereHas('items.book', function ($query) use ($brandId) {
                $query->where('id', $brandId);
            });
        }

        if ($search) {
            $transactions->where(function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('items.book', function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%')
                            ->orWhere('author', 'like', '%' . $search . '%');
                    });
            });
        }

        $transactions->orderBy($orderBy, $orderDirection);
        return $transactions->paginate($limit);
    }

    public static function getTransactionDetail($transactionCode, $storeId = null)
    {
        $transaction = Transaction::with([
            'type',
            'user',
            'payment_method',
            'shipping_method',
            'payments',
        ])
            ->where('code', $transactionCode)
            ->firstOrFail();
        $invoices = Invoice::with(['store'])->where(function ($query) use ($transaction, $storeId) {
            $query->where('transaction_id', $transaction->id);
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
        })->get();
        $groups = $invoices->map(function ($invoice) {
            $items = TransactionItem::where('transaction_id', $invoice->transaction_id)
                ->where('store_id', $invoice->store_id)
                ->get();

            return [
                'store_id' => $invoice->store_id,
                'store' => $invoice->store,
                'invoice' => $invoice->makeHidden(['store']),
                'items' => $items,
            ];
        })->filter(function ($item) {
            return $item['items']->isNotEmpty();
        });

        return [
            'transaction' => $transaction,
            'groups' => $groups,
        ];
    }

    public static function createTransaction(array $data): Transaction
    {
        try {
            $transaction = Transaction::create($data);
            return $transaction;
        } catch (Exception $e) {
            Log::error('Error creating transaction: ' . $e);
            throw new Exception('Failed to create transaction: ' . $e);
        }
    }

    public static function createTransactionItem(array $data): TransactionItem
    {
        try {
            $transactionItem = TransactionItem::create($data);
            return $transactionItem;
        } catch (Exception $e) {
            Log::error('Error creating transaction item: ' . $e);
            throw new Exception('Failed to create transaction item: ' . $e);
        }
    }

    public static function setPaidNow(Transaction $transaction): void
    {
        try {
            DB::beginTransaction();

            // Update invoice status
            Invoice::where('transaction_id', $transaction->id)
                ->update([
                    'paid_at' => now(),
                    'status' => 'paid',
                ]);

            // Update transaction status
            $transaction->paid_at = now();
            $transaction->status = 'paid';
            $transaction->save();

            // Update transaction items status
            TransactionItem::where('transaction_id', $transaction->id)
                ->update(['fullfillment_status' => 'paid']);

            // TODO: Update stock for each transaction item here if needed


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to change transaction status to paid: ' . $e);
            throw new Exception('Failed to change transaction status to paid: ' . $e);
        }
    }

    public static function setCancelled(Transaction $transaction): void
    {
        try {
            DB::beginTransaction();

            // Update invoice status
            Invoice::where('transaction_id', $transaction->id)
                ->update([
                    'status' => 'cancelled',
                ]);

            // Update transaction status
            $transaction->status = 'cancelled';
            $transaction->save();

            // Update transaction items status
            TransactionItem::where('transaction_id', $transaction->id)
                ->update(['fullfillment_status' => 'cancelled']);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to change transaction status to cancelled: ' . $e);
            throw new Exception('Failed to change transaction status to cancelled: ' . $e);
        }
    }
}
