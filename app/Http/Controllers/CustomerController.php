<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Customer;
use App\Models\CustomerTaxProfile;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\DianCustomerTribute;
use App\Models\DianIdentificationDocument;
use App\Models\DianLegalOrganization;
use App\Models\DianMunicipality;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        // Filtros
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->orderBy('name')->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     * Loads all necessary catalogs for electronic invoice configuration.
     */
    public function create(): View
    {
        return view('customers.create', $this->getTaxCatalogs());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['requires_electronic_invoice'] = $request->boolean('requires_electronic_invoice');

        $customer = Customer::create($data);

        $this->syncTaxProfile($customer, $request->validated(), $data['requires_electronic_invoice']);

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            $customer->load('taxProfile.identificationDocument');
            $customerData = [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ];
            
            // Include tax profile data if exists
            if ($customer->taxProfile) {
                $customerData['tax_profile'] = [
                    'identification' => $customer->taxProfile->identification,
                    'dv' => $customer->taxProfile->dv,
                    'document_type' => $customer->taxProfile->identificationDocument?->code,
                ];
            }
            
            return response()->json([
                'success' => true,
                'customer' => $customerData,
                'message' => 'Cliente creado exitosamente.'
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): View
    {
        $customer->load('taxProfile');

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        $customer->load(['taxProfile.municipality', 'taxProfile.identificationDocument']);

        return view('customers.edit', array_merge(
            ['customer' => $customer],
            $this->getTaxCatalogs()
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['requires_electronic_invoice'] = $request->boolean('requires_electronic_invoice');

        // Update customer
        $customer->update($data);

        $this->syncTaxProfile($customer, $request->validated(), $data['requires_electronic_invoice']);

        return redirect()->route('customers.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Get tax profile data for a customer (API endpoint)
     */
    public function getTaxProfile(Customer $customer): JsonResponse
    {
        try {
            $customer->load('taxProfile.identificationDocument');

            $catalogs = $this->getTaxCatalogs();

            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'requires_electronic_invoice' => (bool) $customer->requires_electronic_invoice,
                    'tax_profile' => $customer->taxProfile ? [
                        'identification_document_id' => $customer->taxProfile->identification_document_id,
                        'identification' => $customer->taxProfile->identification,
                        'dv' => $customer->taxProfile->dv,
                        'legal_organization_id' => $customer->taxProfile->legal_organization_id,
                        'company' => $customer->taxProfile->company,
                        'trade_name' => $customer->taxProfile->trade_name,
                        'names' => $customer->taxProfile->names,
                        'address' => $customer->taxProfile->address,
                        'email' => $customer->taxProfile->email,
                        'phone' => $customer->taxProfile->phone,
                        'tribute_id' => $customer->taxProfile->tribute_id,
                        'municipality_id' => $customer->taxProfile->municipality_id,
                    ] : null,
                ],
                'catalogs' => [
                    'identification_documents' => $catalogs['identificationDocuments']->map(fn($doc) => [
                        'id' => $doc->id,
                        'code' => $doc->code,
                        'name' => $doc->name,
                        'requires_dv' => (bool) $doc->requires_dv,
                    ])->values(),
                    'legal_organizations' => $catalogs['legalOrganizations']->map(fn($org) => [
                        'id' => $org->id,
                        'name' => $org->name,
                    ])->values(),
                    'tributes' => $catalogs['tributes']->map(fn($t) => [
                        'id' => $t->id,
                        'code' => $t->code,
                        'name' => $t->name,
                    ])->values(),
                    'municipalities' => $catalogs['municipalities']->groupBy('department')->map(function ($municipalities) {
                        return $municipalities->map(fn($m) => [
                            'factus_id' => $m->factus_id,
                            'name' => $m->name,
                            'department' => $m->department,
                        ])->values();
                    }),
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Error al obtener perfil fiscal del cliente', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Error al cargar los datos del cliente: ' . $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Save tax profile for a customer (API endpoint)
     */
    public function saveTaxProfile(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'requires_electronic_invoice' => 'required|boolean',
            'identification_document_id' => 'required_if:requires_electronic_invoice,true|exists:dian_identification_documents,id',
            'identification' => 'required_if:requires_electronic_invoice,true|string|max:20',
            'dv' => 'nullable|string|max:1',
            'legal_organization_id' => 'nullable|exists:dian_legal_organizations,id',
            'company' => 'nullable|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'names' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'tribute_id' => 'nullable|exists:dian_customer_tributes,id',
            'municipality_id' => [
                'required_if:requires_electronic_invoice,true',
                function ($attribute, $value, $fail) {
                    if ($value && !\App\Models\DianMunicipality::where('factus_id', $value)->exists()) {
                        $fail('El municipio seleccionado no es válido.');
                    }
                },
            ],
        ]);

        // Update customer
        $customer->update([
            'requires_electronic_invoice' => (bool) $validated['requires_electronic_invoice'],
        ]);

        $this->syncTaxProfile(
            $customer,
            $validated,
            (bool) $validated['requires_electronic_invoice']
        );

        $customer->load('taxProfile');

        return response()->json([
            'success' => true,
            'message' => 'Configuración fiscal actualizada correctamente',
            'customer' => [
                'requires_electronic_invoice' => $customer->requires_electronic_invoice,
                'has_complete_tax_profile' => $customer->hasCompleteTaxProfileData(),
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function getTaxCatalogs(): array
    {
        return [
            'identificationDocuments' => DianIdentificationDocument::orderBy('id')->get(),
            'legalOrganizations' => DianLegalOrganization::orderBy('id')->get(),
            'tributes' => DianCustomerTribute::orderBy('id')->get(),
            'municipalities' => DianMunicipality::orderBy('department')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function syncTaxProfile(Customer $customer, array $input, bool $requiresElectronicInvoice): void
    {
        if (!$requiresElectronicInvoice) {
            if ($customer->taxProfile) {
                $customer->taxProfile->delete();
            }

            return;
        }

        $attributes = $this->buildTaxProfileData($input);

        if ($customer->taxProfile) {
            $customer->taxProfile->update($attributes);

            return;
        }

        CustomerTaxProfile::create(array_merge(
            ['customer_id' => $customer->id],
            $attributes
        ));
    }

    private function buildTaxProfileData(array $input): array
    {
        return [
            'identification_document_id' => $input['identification_document_id'] ?? null,
            'identification' => $input['identification'] ?? null,
            'municipality_id' => $input['municipality_id'] ?? null,
            'dv' => $input['dv'] ?? null,
            'legal_organization_id' => $input['legal_organization_id'] ?? null,
            'company' => $input['company'] ?? null,
            'trade_name' => $input['trade_name'] ?? null,
            'names' => $input['names'] ?? null,
            'address' => $input['tax_address'] ?? $input['address'] ?? null,
            'email' => $input['tax_email'] ?? $input['email'] ?? null,
            'phone' => $input['tax_phone'] ?? $input['phone'] ?? null,
            'tribute_id' => $input['tribute_id'] ?? null,
        ];
    }
}
