<?php

namespace App\Interfaces\Http\Controllers;

use App\Infrastructure\Persistence\CompanyRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    private CompanyRepository $repository;

    public function __construct(CompanyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        return Inertia::render('Organization/Company/Index', [
            'companies' => $this->repository->all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:companies,code',
            'address' => 'nullable|string'
        ]);

        $this->repository->create($validated);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:companies,code,' . $id,
            'address' => 'nullable|string'
        ]);

        $this->repository->update($id, $validated);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
        return redirect()->back();
    }
}
