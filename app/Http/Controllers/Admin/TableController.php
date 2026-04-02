<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TableController extends Controller
{
    public function __construct(private QrCodeService $qrCode) {}

    public function index()
    {
        return Inertia::render('Tables/Admin', [
            'tables' => RestaurantTable::withCount(['orders as today_orders' => function ($q) {
                $q->whereDate('created_at', today());
            }])->orderBy('number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'number'   => 'required|string|max:20|unique:restaurant_tables,number',
            'capacity' => 'required|integer|min:1|max:50',
            'location' => 'nullable|string|max:100',
        ]);

        $table = RestaurantTable::create($data);
        $this->qrCode->generateForTable($table);

        return back()->with('success', 'Table created with QR code.');
    }

    public function update(Request $request, RestaurantTable $table)
    {
        $data = $request->validate([
            'number'   => 'required|string|max:20|unique:restaurant_tables,number,' . $table->id,
            'capacity' => 'required|integer|min:1|max:50',
            'location' => 'nullable|string|max:100',
            'status'   => 'in:available,occupied,reserved,cleaning',
        ]);

        $table->update($data);

        return back()->with('success', 'Table updated.');
    }

    public function destroy(RestaurantTable $table)
    {
        $table->delete();
        return back()->with('success', 'Table deleted.');
    }

    public function regenerateQr(RestaurantTable $table)
    {
        $this->qrCode->generateForTable($table);
        return back()->with('success', 'QR code regenerated.');
    }
}
