<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Repositories\CategoryRepository;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $categoryId = $request->input('category_id');

        $invoices = InvoiceRepository::getInvoices(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            categoryId: $categoryId
        );

        $categories = CategoryRepository::getCategoryDropdown();

        return Inertia::render('Admin/Order/Index', [
            'invoices' => $invoices,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Order/AddOrder');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoiceDetail = InvoiceRepository::getInvoiceDetail(
            invoiceId: $invoice->id,
        );

        return Inertia::render('Admin/Order/EditInvoice', $invoiceDetail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
