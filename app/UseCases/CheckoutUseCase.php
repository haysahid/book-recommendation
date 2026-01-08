<?php

namespace App\UseCases;

use App\Models\Book;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\Store;
use App\Models\TransactionItem;
use App\Models\User;
use App\Repositories\InvoiceRepository;
use App\Repositories\MidtransRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\RajaongkirRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoucherRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutUseCase
{
    private RajaongkirRepository $rajaongkirRepository;
    private MidtransRepository $midtransRepository;
    private VoucherRepository $voucherRepository;
    private TransactionRepository $transactionRepository;
    private InvoiceRepository $invoiceRepository;
    private PaymentRepository $paymentRepository;
    private UserRepository $userRepository;

    private int $weight = 1000; // default weight in grams
    private string $courier = 'jne'; // default courier

    public function __construct()
    {
        $this->rajaongkirRepository = new RajaongkirRepository();
        $this->midtransRepository = new MidtransRepository();
        $this->voucherRepository = new VoucherRepository();
        $this->transactionRepository = new TransactionRepository();
        $this->invoiceRepository = new InvoiceRepository();
        $this->paymentRepository = new PaymentRepository();
        $this->userRepository = new UserRepository();
    }

    public function execute(
        array $data,
        bool $isGuestCheckout = false,
        bool $isStoreCheckout = false,
    ) {
        try {
            DB::beginTransaction();

            if ($isStoreCheckout) {
                if (isset($data['customer_id'])) {
                    $customer = User::find($data['customer_id']);
                } elseif (isset($data['guest_name'], $data['guest_email'], $data['guest_phone'])) {
                    $customer = $this->userRepository->createGuestUser([
                        'name' => $data['guest_name'],
                        'email' => $data['guest_email'],
                        'phone' => $data['guest_phone'],
                    ]);
                } else {
                    $customer = User::find(Auth::id());
                }
            } elseif ($isGuestCheckout) {
                $customer = $this->userRepository->createGuestUser([
                    'name' => $data['guest_name'],
                    'email' => $data['guest_email'],
                    'phone' => $data['guest_phone'],
                ]);
            } else {
                $customer = User::find(Auth::id());
            }

            // Get transaction voucher if provided
            $transactionVoucher = null;
            if (isset($data['voucher_code'])) {
                $transactionVoucher = $this->voucherRepository->getVoucherByCode($data['voucher_code']);
                if (!$transactionVoucher) {
                    DB::rollBack();
                    throw new Exception('Voucher not found: ' . $data['voucher_code'], 400);
                }
            }

            $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);
            $shippingMethod = ShippingMethod::findOrFail($data['shipping_method_id']);

            $transaction = null;
            $totalPayment = 0;
            $itemDetails = [];
            $invoices = [];

            // [START] Processing each cart group
            foreach ($data['cart_groups'] as $key => $group) {
                $store = Store::findOrFail($group['store_id']);
                $originId = $store->rajaongkir_origin_id;

                $cartItems = $group['items'];

                if ($shippingMethod->slug === 'courier') {
                    $rajaongkirShipping = Validator::make($data, [
                        'destination_id' => 'required|integer',
                        'address' => 'required|string',
                    ], [
                        'destination_id.required' => 'ID tujuan harus diisi',
                        'destination_id.integer' => 'ID tujuan harus berupa angka',
                        'address.required' => 'Alamat harus diisi',
                        'address.string' => 'Alamat harus berupa string',
                    ])->validate();

                    // Get shipping cost
                    $weight = $this->weight;
                    $courier = $this->courier;
                    $destinationId = $rajaongkirShipping['destination_id'];
                    $shipping = $this->rajaongkirRepository->getShipping($originId, $destinationId, $weight, $courier);

                    if (!$shipping) {
                        DB::rollBack();
                        Log::error('Failed to get shipping cost for origin_id: ' . $originId . ', destination_id: ' . $destinationId);
                        throw new Exception('Failed to get shipping cost', 500);
                    }

                    $rajaongkirDestinationId = $data['destination_id'] ?? null;
                    $rajaongkirDestinationLabel = $data['destination_label'] ?? null;
                    $provinceName = $data['province_name'] ?? null;
                    $cityName = $data['city_name'] ?? null;
                    $districtName = $data['district_name'] ?? null;
                    $subdistrictName = $data['subdistrict_name'] ?? null;
                    $zipCode = $data['zip_code'] ?? null;
                    $address = $data['address'] ?? null;
                    $shippingCost = $shipping['cost'] ?? 0;
                    $shippingEstimate = $shipping['etd'] ?? null;
                } else {
                    // For non-courier shipping methods, set default values
                    $rajaongkirDestinationId = null;
                    $rajaongkirDestinationLabel = null;
                    $provinceName = null;
                    $cityName = null;
                    $districtName = null;
                    $subdistrictName = null;
                    $zipCode = null;
                    $address = null;
                    $shippingCost = 0;
                    $shippingEstimate = null;
                }

                // Create transaction
                if (!$transaction) {
                    $transaction = $this->transactionRepository->createTransaction([
                        'user_id' => $customer->id,
                        'type_id' => 2, // sale
                        'code' => 'SL-' . date('YmdHis'),
                        'note' => $data['note'] ?? null,
                        'payment_method_id' => $data['payment_method_id'],
                        'shipping_method_id' => $data['shipping_method_id'],
                        'rajaongkir_destination_id' => $rajaongkirDestinationId,
                        'rajaongkir_destination_label' => $rajaongkirDestinationLabel,
                        'province_name' => $provinceName,
                        'city_name' => $cityName,
                        'district_name' => $districtName,
                        'subdistrict_name' => $subdistrictName,
                        'zip_code' => $zipCode,
                        'address' => $address,
                        'shipping_cost' => 0,
                        'shipping_estimate' => $shippingEstimate,
                        'status' => 'pending',
                    ]);
                }

                // Create transaction items
                $subTotal = 0;
                foreach ($cartItems as $item) {
                    $book = Book::findOrFail($item['book_id']);
                    $itemSubTotal = $item['quantity'] * $book->final_price;
                    $subTotal += $itemSubTotal;

                    $this->transactionRepository->createTransactionItem([
                        'store_id' => $store->id,
                        'transaction_id' => $transaction->id,
                        'user_id' => $customer->id,
                        'book_id' => $book->id,
                        'quantity' => $item['quantity'],
                        'unit_base_price' => $book->slice_price,
                        'unit_discount_type' => 'percentage',
                        'unit_discount' => $book->discount,
                        'unit_final_price' => $book->final_price,
                        'subtotal' => $itemSubTotal,
                        'fullfillment_status' => 'pending',
                    ]);
                }

                // Calculate total
                $baseTotal = $subTotal;
                $total = $subTotal + $shippingCost;

                // Create invoice
                if ($paymentMethod->slug === 'transfer') {
                    $itemDetails = TransactionItem::with(
                        [
                            'book',
                            'store' => function ($query) {
                                $query->select('id', 'name');
                            },
                        ]
                    )->where('transaction_id', $transaction->id)->get()->map(function ($item) {
                        return [
                            'id' => $item->book_id,
                            'price' => $item->unit_final_price,
                            'quantity' => $item->quantity,
                            'name' => $item->book->sku,
                            'brand' => null,
                            'merchant_name' => $item->store->name,
                            'url' => null,
                        ];
                    })->toArray();

                    $invoice = $this->invoiceRepository->createInvoice([
                        'store_id' => $store->id,
                        'transaction_id' => $transaction->id,
                        'user_id' => $customer->id,
                        'code' => 'INV-' . date('YmdHis') . '-' . $key,
                        'base_amount' => $baseTotal,
                        'shipping_cost' => $shippingCost,
                        'shipping_estimate' => $shippingEstimate,
                        'tax' => 0,
                        'amount' => $total,
                        'due_date' => now()->addDays(1),
                        'snap_token' => null,
                    ])->load('store');
                } else {
                    $invoice = $this->invoiceRepository->createInvoice([
                        'store_id' => $store->id,
                        'user_id' => $customer->id,
                        'transaction_id' => $transaction->id,
                        'code' => 'INV-' . date('YmdHis') . '-' . $key,
                        'base_amount' => $baseTotal,
                        'shipping_cost' => 0,
                        'tax' => 0,
                        'amount' => $total,
                        'due_date' => now()->addDays(1),
                    ])->load('store');
                }

                // Get store voucher if provided
                $storeVoucher = null;
                if (isset($group['voucher_code'])) {
                    $storeVoucher = $this->voucherRepository->getVoucherByCode(
                        code: $group['voucher_code'],
                        storeId: $store->id,
                    );

                    if (!$storeVoucher) {
                        DB::rollBack();
                        Log::error('Store voucher not found: ' . $group['voucher_code']);
                        throw new Exception('Store voucher not found: ' . $group['voucher_code'], 400);
                    }
                }

                if ($storeVoucher) {
                    // Redeem voucher
                    $redeemed = $this->voucherRepository->redeemVoucher(
                        voucher: $storeVoucher,
                        user: $customer,
                        use: true,
                    );

                    $discountAmount = 0;
                    if ($redeemed) {
                        // Apply voucher discount
                        $discountAmount = $this->voucherRepository->calculateVoucherAmount(
                            voucher: $storeVoucher,
                            amount: $invoice->base_amount,
                        );
                    }

                    // Update invoice with discount
                    $invoice->voucher_id = $storeVoucher->id;
                    $invoice->voucher_amount = $discountAmount;
                    $invoice->amount = $invoice->amount - $discountAmount;
                    $invoice->save();
                    $invoice->load('voucher');

                    // Update transaction total payment
                    $totalPayment = $totalPayment + $invoice->amount;

                    $itemDetails[] = [
                        'id' => $storeVoucher->id,
                        'price' => -$discountAmount,
                        'quantity' => 1,
                        'name' => 'Discount - ' . $storeVoucher->code,
                        'brand' => null,
                        'merchant_name' => $store->name,
                        'url' => null,
                    ];
                }

                $invoices[] = $invoice;

                // Update transaction with shipping cost
                $transaction->shipping_cost = $transaction->shipping_cost + $shippingCost;
                $transaction->save();

                if ($shippingMethod->slug === 'courier') {
                    $itemDetails[] = [
                        'id' => 'shipping',
                        'price' => $shippingCost,
                        'quantity' => 1,
                        'name' => 'Biaya Pengiriman',
                        'brand' => null,
                        'merchant_name' => null,
                        'url' => null,
                    ];
                }
            }
            // [END] Processing each cart group

            if ($transactionVoucher) {
                // Redeem voucher
                $redeemed = $this->voucherRepository->redeemVoucher(
                    voucher: $transactionVoucher,
                    user: $customer,
                    use: true,
                );

                $discountAmount = 0;
                if ($redeemed) {
                    // Apply transaction voucher discount
                    $discountAmount = VoucherRepository::calculateVoucherAmount(
                        voucher: $transactionVoucher,
                        amount: $totalPayment - $transaction->shipping_cost,
                    );
                }

                $totalPayment = $totalPayment - $discountAmount;

                // Update transaction with discount
                $transaction->voucher_id = $transactionVoucher->id;
                $transaction->voucher_amount = $discountAmount;
                $transaction->save();
                $transaction->load('voucher');
            }

            // Create payment record
            $grossAmount = $totalPayment;

            if ($paymentMethod->slug === 'transfer') {
                // Create snap token for Midtrans
                $snapToken = $this->midtransRepository->createSnapToken(
                    $transaction->code,
                    $itemDetails,
                    $customer,
                    $grossAmount
                );

                if (!$snapToken) {
                    DB::rollBack();
                    Log::error('Failed to create snap token for transaction: ' . $transaction->code);
                    throw new Exception('Failed to create snap token', 500);
                }

                // Create payment record
                $payment = $this->paymentRepository->createPayment([
                    'transaction_id' => $transaction->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $grossAmount,
                    'note' => $data['note'] ?? null,
                    'status' => 'pending',
                    'midtrans_snap_token' => $snapToken,
                ]);
            } else {
                $payment = $this->paymentRepository->createPayment([
                    'transaction_id' => $transaction->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $grossAmount,
                    'note' => $data['note'] ?? null,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return [
                'transaction' => $transaction,
                'invoices' => $invoices,
                'payment' => $payment,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to checkout: ' . $e->getMessage());
            throw $e;
        }
    }
}
