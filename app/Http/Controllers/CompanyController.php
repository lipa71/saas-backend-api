<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // GET /api/companies
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Company::all(),
        ]);
    }

    // POST /api/companies
    public function store(Request $request)
    {
        // Tymczasowo puste pod testy
    }

    // GET /api/companies/{id}
    public function show(Company $company)
    {
        return response()->json([
            'status' => 'success',
            'data' => $company,
        ]);
    }

    // PUT/PATCH /api/companies/{id}
    public function update(Request $request, Company $company)
    {
        // Tymczasowo puste pod testy
    }

    // DELETE /api/companies/{id}
    public function destroy(Company $company)
    {
        // Tymczasowo puste pod testy
    }
}
