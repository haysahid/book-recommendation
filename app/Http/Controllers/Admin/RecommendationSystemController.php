<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecommendationSystemController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/RecommendationSystem/Index');
    }
}
