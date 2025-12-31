<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\DianCustomerTribute;
use App\Models\DianMeasurementUnit;
use App\Models\DianProductStandard;
use App\Models\Service;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class ServiceController extends Controller
{
    public function index(Request $request): ViewContract
    {
        $query = Service::with(['standardCode', 'unitMeasure', 'tribute']);

        // Filtros
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('standard_code_id')) {
            $query->where('standard_code_id', $request->standard_code_id);
        }

        if ($request->filled('unit_measure_id')) {
            $query->where('unit_measure_id', $request->unit_measure_id);
        }

        $services = $query->orderBy('name')->paginate(15)->withQueryString();

        $standardCodes = DianProductStandard::orderBy('name')->get();
        $unitMeasures = DianMeasurementUnit::orderBy('name')->get();

        return View::make('services.index', [
            'services' => $services,
            'standardCodes' => $standardCodes,
            'unitMeasures' => $unitMeasures,
            'filters' => $request->only(['search', 'status', 'standard_code_id', 'unit_measure_id']),
        ]);
    }

    public function create(): ViewContract
    {
        $standardCodes = DianProductStandard::orderBy('name')->get();
        $unitMeasures = DianMeasurementUnit::orderBy('name')->get();
        $tributes = DianCustomerTribute::orderBy('name')->get();

        return View::make('services.create', [
            'standardCodes' => $standardCodes,
            'unitMeasures' => $unitMeasures,
            'tributes' => $tributes,
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        Service::create($data);

        return Redirect::route('services.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    public function show(Service $service): ViewContract
    {
        $service->load(['standardCode', 'unitMeasure', 'tribute']);

        return View::make('services.show', compact('service'));
    }

    public function edit(Service $service): ViewContract
    {
        $standardCodes = DianProductStandard::orderBy('name')->get();
        $unitMeasures = DianMeasurementUnit::orderBy('name')->get();
        $tributes = DianCustomerTribute::orderBy('name')->get();

        return View::make('services.edit', [
            'service' => $service,
            'standardCodes' => $standardCodes,
            'unitMeasures' => $unitMeasures,
            'tributes' => $tributes,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $service->update($data);

        return Redirect::route('services.show', $service)
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return Redirect::route('services.index')
            ->with('success', 'Servicio eliminado exitosamente.');
    }
}

