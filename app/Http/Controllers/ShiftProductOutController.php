<?php

namespace App\Http\Controllers;

use App\Enums\ShiftHandoverStatus;
use App\Enums\ShiftType;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ShiftProductOut;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftProductOutController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = ShiftProductOut::with(['user', 'product', 'shiftHandover'])->latest();
        
        if (!$user->hasRole('Administrador')) {
            $query->where('user_id', $user->id);
        }

        $productOuts = $query->paginate(15);
        
        return view('shift-product-outs.index', compact('productOuts'));
    }

    public function create()
    {
        $user = Auth::user();
        $activeShift = $user->turnoActivo()->first();
        
        // Cargar productos con stock disponible
        $products = Product::where('status', 'active')->where('quantity', '>', 0)->orderBy('name')->get();
        
        return view('shift-product-outs.create', compact('activeShift', 'products'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $activeShift = $user->turnoActivo()->first();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|in:merma,consumo_interno,perdida,donacion,ajuste_inventario,otro',
        ], [
            'product_id.required' => 'Debe seleccionar un producto.',
            'quantity.min' => 'La cantidad debe ser mayor a cero.',
            'reason.required' => 'Debe seleccionar una razón para la salida.',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->quantity > $product->quantity) {
            return back()->with('error', "Stock insuficiente. Disponible: {$product->quantity}")->withInput();
        }

        DB::beginTransaction();
        try {
            // 1. Determinar tipo de turno
            $shiftType = null;
            if ($activeShift) {
                $shiftType = $activeShift->shift_type;
            } else {
                $hour = (int) now()->format('H');
                $shiftType = ($hour >= 22 || $hour < 6) ? ShiftType::NIGHT : ShiftType::DAY;
            }

            // 2. Registrar salida
            $productOut = ShiftProductOut::create([
                'shift_handover_id' => $activeShift ? $activeShift->id : null,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'observations' => $request->observations,
                'shift_type' => $shiftType,
                'shift_date' => Carbon::today(),
            ]);

            // 3. Descontar stock y registrar movimiento de inventario
            $product->recordMovement(
                -$request->quantity, 
                'output', 
                "Salida de Turno ({$productOut->readable_reason}): " . ($request->observations ?? 'Sin observaciones')
            );

            // 4. Auditoría
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'product_out_create',
                'description' => "Salida de producto: {$product->name} x {$request->quantity}. Razón: {$productOut->readable_reason}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => ['product_out_id' => $productOut->id, 'product_id' => $product->id]
            ]);

            DB::commit();
            return redirect()->route('shift-product-outs.index')->with('success', 'Salida de producto registrada y stock actualizado.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar la salida: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $productOut = ShiftProductOut::findOrFail($id);
        $user = Auth::user();

        // Validar permisos
        if (!$user->hasRole('Administrador') && (int) $productOut->user_id !== (int) $user->id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $product = $productOut->product;
            
            // Restaurar stock
            $product->recordMovement(
                $productOut->quantity, 
                'adjustment', 
                "Anulación de salida de turno #{$id}"
            );

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'product_out_delete',
                'description' => "Eliminó salida de producto #{$id}: {$product->name} x {$productOut->quantity}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['product_out_id' => $id, 'product_id' => $product->id]
            ]);

            $productOut->delete();

            DB::commit();
            return back()->with('success', 'Salida de producto eliminada y stock restaurado.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}
