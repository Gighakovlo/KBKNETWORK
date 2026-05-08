<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetMovement;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetMovementController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        
        // Tarik semua data mutasi beserta relasi asetnya, urutkan dari yang paling baru
        $allMovements = AssetMovement::with('asset')->orderBy('created_at', 'desc')->get();

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $movements = new LengthAwarePaginator(
            $allMovements->forPage($page, $perPage),
            $allMovements->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('inventory.movements.index', compact('movements', 'perPage'));
    }
}