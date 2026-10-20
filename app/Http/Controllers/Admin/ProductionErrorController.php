<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductionError;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionErrorController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'guard' => ['nullable', 'in:web,dosen,mahasiswa'],
        ]);

        $errors = ProductionError::query()
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('message', 'like', '%'.$search.'%')
                        ->orWhere('actor_name', 'like', '%'.$search.'%')
                        ->orWhere('actor_identifier', 'like', '%'.$search.'%')
                        ->orWhere('route_name', 'like', '%'.$search.'%');
                });
            })
            ->when($validated['guard'] ?? null, fn ($query, string $guard) => $query
                ->where('actor_guard', $guard))
            ->latest('last_seen_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.production-errors.index', compact('errors'));
    }

    public function show(ProductionError $productionError): View
    {
        $productionError->load([
            'events' => fn ($query) => $query
                ->latest('occurred_at')
                ->latest('id')
                ->limit(50),
        ]);

        return view('admin.production-errors.show', [
            'error' => $productionError,
        ]);
    }
}
