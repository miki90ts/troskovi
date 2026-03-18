<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ReportPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('reports/Index');
    }
}
