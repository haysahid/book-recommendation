<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransactionItemsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionItemController extends Controller
{
    public function export()
    {
        return Excel::download(new TransactionItemsExport, 'transaction_items.xlsx');
    }
}
