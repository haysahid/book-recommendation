<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceRepository
{
    public static function getInvoices(
        $storeId = null,
        $userId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $categoryId = null,
    ) {
        $invoices = Invoice::query();

        $invoices->with([
            'store',
            'transaction.user',
            'transaction.payment_method',
            'transaction.shipping_method',
            'transaction.items',
            'voucher',
        ]);

        if ($storeId) {
            $invoices->where('store_id', $storeId);
        }

        if ($categoryId) {
            $invoices->whereHas('transaction.items.book', function ($query) use ($categoryId) {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            });
        }

        if ($userId) {
            $invoices->where('user_id', $userId);
        }

        if ($search) {
            $invoices->where(function ($query) use ($search) {
                $query->where('code', 'like', '%' . $search . '%')
                    ->orWhereHas('transaction', function ($q) use ($search) {
                        $q->whereHas('user', function ($qq) use ($search) {
                            $qq->where('name', 'like', '%' . $search . '%');
                        });
                    })
                    ->orWhereHas('transaction.items.book', function ($q) use ($search) {
                        $q->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        return $invoices->orderBy($orderBy, $orderDirection)->paginate($limit);
    }

    public static function getInvoiceDetail($invoiceId)
    {
        $invoice = Invoice::with([
            'store',
            'voucher',
            'transaction',
            'transaction.user',
            'transaction.payment_method',
            'transaction.shipping_method',
        ])->findOrFail($invoiceId);

        $items = TransactionItem::with(['book'])
            ->where('store_id', $invoice->store_id)
            ->where('transaction_id', $invoice->transaction_id)
            ->get();

        $payments = Payment::where('transaction_id', $invoice->transaction_id)->get();

        return [
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
        ];
    }

    public static function createInvoice(array $data): Invoice
    {
        try {
            $invoice = Invoice::create($data);
            return $invoice;
        } catch (Exception $e) {
            Log::error("Failed to create invoice: " . $e);
            throw new Exception('Failed to create invoice: ' . $e);
        }
    }
}
