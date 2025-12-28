<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\UseCases\CheckoutUseCase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private CheckoutUseCase $checkoutUseCase;

    public function __construct()
    {
        $this->checkoutUseCase = new CheckoutUseCase();
    }

    public function checkoutStore(Request $request)
    {
        $validated = $request->validate([
            'cart_groups' => 'required|array',
            'cart_groups.*.store_id' => 'required|integer|exists:stores,id',
            'cart_groups.*.voucher_code' => 'nullable|string|exists:vouchers,code',
            'cart_groups.*.items' => 'required|array',
            'cart_groups.*.items.*.book_id' => 'required|integer|exists:products,id',
            'cart_groups.*.items.*.quantity' => 'required|integer|min:1',
            'payment_method_id' => 'required|integer|exists:payment_methods,id',
            'shipping_method_id' => 'required|integer|exists:shipping_methods,id',
            'destination_id' => 'nullable|integer',
            'destination_label' => 'nullable|string',
            'province_name' => 'nullable|string',
            'city_name' => 'nullable|string',
            'district_name' => 'nullable|string',
            'subdistrict_name' => 'nullable|string',
            'zip_code' => 'nullable|string',
            'address' => 'nullable|string',
            'note' => 'nullable|string',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
            'customer_id' => 'nullable|integer|exists:users,id',
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
        ], [
            'cart_groups.required' => 'Cart groups is required',
            'cart_groups.*.store_id.required' => 'Store ID is required',
            'cart_groups.*.store_id.exists' => 'Store not found',
            'cart_groups.*.voucher_code.string' => 'Voucher code must be a string',
            'cart_groups.*.voucher_code.exists' => 'Voucher code not found',
            'cart_groups.*.items.required' => 'Cart items are required',
            'cart_groups.*.items.*.book_id.required' => 'Book ID is required',
            'cart_groups.*.items.*.book_id.exists' => 'Book not found',
            'cart_groups.*.items.*.quantity.min' => 'Book quantity must be at least 1',
            'payment_method_id.required' => 'Payment method is required',
            'payment_method_id.exists' => 'Payment method not found',
            'shipping_method_id.required' => 'Shipping method is required',
            'shipping_method_id.exists' => 'Shipping method not found',
            'destination_id.integer' => 'Destination ID must be an integer',
            'destination_label.string' => 'Destination label must be a string',
            'province_name.string' => 'Province name must be a string',
            'city_name.string' => 'City name must be a string',
            'district_name.string' => 'District name must be a string',
            'subdistrict_name.string' => 'Subdistrict name must be a string',
            'zip_code.string' => 'Zip code must be a string',
            'address.string' => 'Address must be a string',
            'note.string' => 'Note must be a string',
            'voucher_code.string' => 'Voucher code must be a string',
            'voucher_code.exists' => 'Voucher code not found',
            'customer_id.integer' => 'Customer ID must be an integer',
            'customer_id.exists' => 'Customer not found',
            'guest_name.string' => 'Name must be a string',
            'guest_name.max' => 'Name may not be greater than 255 characters',
            'guest_email.email' => 'Invalid email address',
            'guest_email.max' => 'Email may not be greater than 255 characters',
            'guest_phone.string' => 'Phone number must be a string',
            'guest_phone.max' => 'Phone number may not be greater than 20 characters',
        ]);

        return $this->checkoutUseCase->execute(
            data: $validated,
            isStoreCheckout: true,
        )->fold(
            onSuccess: fn($data, $code) => ResponseFormatter::success($data, 'Order created successfully', $code),
            onError: fn($error, $code) => ResponseFormatter::error($error, $code)
        );
    }

    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'status' => 'required|string|in:pending,paid,processing,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $invoice->status = $validated['status'];
            $invoice->save();

            DB::commit();

            return ResponseFormatter::success(
                $invoice,
                'Transaction status updated successfully',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Change status failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'invoice_id' => $validated['invoice_id'],
            ]);

            return ResponseFormatter::error(
                'Failed to update transaction status: ' . $e->getMessage(),
                500
            );
        }
    }
}
