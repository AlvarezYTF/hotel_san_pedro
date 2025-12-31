<?php

namespace App\Http\Controllers;

use App\Models\ShiftProductOut;
use App\Models\Product;
use App\Models\ShiftHandover;
use App\Models\Shift;
use App\Enums\ShiftProductOutReason;
use App\Enums\ShiftType;
use App\Enums\ShiftHandoverStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

class ShiftProductOutController extends Controller
{
    /**
     * Display a listing of the product outflows.
     */
    public function index()
    {
        $activeShift = Auth::user()->turnoActivo;

        $productOuts = ShiftProductOut::with(['product', 'user'])
            ->when($activeShift, function ($query) use ($activeShift) {
                return $query->where('shift_handover_id', $activeShift->id);
            })
            ->latest()
            ->paginate(15);

        return view('shift-product-outs.index', compact('productOuts', 'activeShift'));
    }

    /**
     * Show the form for creating a new product outflow.
     */
    public function create()
    {
        $activeShift = Auth::user()->turnoActivo;
        $operationalShift = Shift::openOperational()->first();
        if (!$activeShift || !$operationalShift || (int) ($activeShift->from_shift_id ?? $activeShift->id) !== (int) $operationalShift->id) {
            return redirect()->route('shift-product-outs.index')
                ->with('error', 'Debe haber un turno operativo abierto para registrar una salida de producto.');
        }

        $products = Product::active()->where('quantity', '>', 0)->get();
        $reasons = ShiftProductOutReason::cases();

        return view('shift-product-outs.create', compact('products', 'reasons', 'activeShift'));
    }

    /**
     * Store a newly created product outflow in storage.
     */
    public function store(Request $request)
    {
        $activeShift = Auth::user()->turnoActivo;
        $operationalShift = Shift::openOperational()->first();
        if (!$activeShift || !$operationalShift || (int) ($activeShift->from_shift_id ?? $activeShift->id) !== (int) $operationalShift->id) {
            return back()->with('error', 'Debe haber un turno operativo abierto para registrar una salida de producto.')->withInput();
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
            'observations' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->quantity < $request->quantity) {
            return back()->with('error', "No hay suficiente stock. Disponible: {$product->quantity}")->withInput();
        }

        try {
            DB::beginTransaction();

            // Tomar tipo y fecha desde el turno operativo
            $shiftType = $activeShift->shift_type;
            $shiftDate = $activeShift->shift_date;

            $productOut = ShiftProductOut::create([
                'shift_handover_id' => $activeShift->id,
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'observations' => $request->observations,
                'shift_type' => $shiftType,
                'shift_date' => $shiftDate,
            ]);

            // Descontar del inventario y registrar movimiento
            $product->recordMovement(
                -$request->quantity,
                'salida',
                "Salida por " . ShiftProductOutReason::from($request->reason)->label() . ($request->observations ? ": " . $request->observations : "")
            );

            // Auditoría específica para salida de producto
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'shift_product_out',
                'description' => "Salida de producto registrada: {$product->name} (Qty: {$request->quantity}). Motivo: " . ShiftProductOutReason::from($request->reason)->label(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'product_out_id' => $productOut->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'reason' => $request->reason,
                    'shift_handover_id' => $activeShift?->id
                ]
            ]);

            DB::commit();

            return redirect()->route('shift-product-outs.index')
                ->with('success', 'Salida de producto registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar la salida: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified product outflow from storage.
     */
    public function destroy($id)
    {
        $productOut = ShiftProductOut::findOrFail($id);

        // Solo permitir eliminar si el turno aún está activo o si es admin
        if ($productOut->shiftHandover && $productOut->shiftHandover->status !== ShiftHandoverStatus::ACTIVE && !Auth::user()->hasRole('Administrador')) {
            return back()->with('error', 'No se puede eliminar una salida de un turno ya cerrado o entregado.');
        }

        try {
            DB::beginTransaction();

            $product = $productOut->product;

            // Reintegrar al inventario
            $product->recordMovement(
                $productOut->quantity,
                'entrada',
                "Anulación de salida ID: {$productOut->id} ({$productOut->reason})"
            );

            // Auditoría
            AuditLog::create([
                'user_id' => Auth::id(),
                'event' => 'shift_product_out_deleted',
                'description' => "Salida de producto eliminada: {$product->name} (Qty: {$productOut->quantity}). ID: {$productOut->id}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'product_out_id' => $productOut->id,
                    'product_id' => $product->id,
                    'quantity' => $productOut->quantity,
                    'reason' => $productOut->reason
                ]
            ]);

            $productOut->delete();

            DB::commit();

            return redirect()->route('shift-product-outs.index')
                ->with('success', 'Salida de producto eliminada y stock reintegrado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la salida: ' . $e->getMessage());
        }
    }
}
