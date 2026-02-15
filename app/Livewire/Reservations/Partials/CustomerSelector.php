<?php

namespace App\Livewire\Reservations\Partials;

use App\Models\Customer;
use Livewire\Component;

class CustomerSelector extends Component
{
    // Props desde el padre
    public $customerId = '';
    public $customers = [];
    public $datesCompleted = false;
    public $showCustomerDropdown = false;
    public $customerSearchTerm = '';

    // Estados para nuevo cliente
    public $newMainCustomer = [
        'name' => '',
        'identification' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'requiresElectronicInvoice' => false,
        'identificationDocumentId' => '',
        'dv' => '',
        'company' => '',
        'tradeName' => '',
        'municipalityId' => '',
        'legalOrganizationId' => '',
        'tributeId' => ''
    ];

    protected $listeners = [
        'customerUpdated' => 'handleCustomerUpdate',
        'customer-created' => 'handleCustomerCreated',
    ];

    public function mount(): void
    {
        $this->refreshCustomers();
    }

    private function refreshCustomers(): void
    {
        $this->customers = Customer::query()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get([
                'id',
                'name',
                'phone',
                'identification_number',
            ])
            ->map(function (Customer $customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone ?: 'S/N',
                    'taxProfile' => [
                        'identification' => $customer->identification_number ?: 'S/N',
                    ],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Listar clientes filtrados segun busqueda
     */
    public function getFilteredCustomersProperty(): array
    {
        $allCustomers = $this->customers ?? [];

        // Si no hay termino de busqueda, retornar los primeros 5
        if (empty($this->customerSearchTerm)) {
            return array_slice($allCustomers, 0, 5);
        }

        $searchTerm = $this->normalizeSearchValue($this->customerSearchTerm);
        $filtered = [];

        foreach ($allCustomers as $customer) {
            $name = $this->normalizeSearchValue((string) ($customer['name'] ?? ''));
            $identification = $this->normalizeSearchValue((string) ($customer['taxProfile']['identification'] ?? ''));
            $phone = $this->normalizeSearchValue((string) ($customer['phone'] ?? ''));

            // Buscar en nombre, identificacion o telefono
            if (str_contains($name, $searchTerm) ||
                str_contains($identification, $searchTerm) ||
                str_contains($phone, $searchTerm)) {
                $filtered[] = $customer;
            }

            // Limitar a 20 resultados
            if (count($filtered) >= 20) {
                break;
            }
        }

        return $filtered;
    }

    private function normalizeSearchValue(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        return $value ?? '';
    }

    /**
     * Obtener informacion del cliente seleccionado
     */
    public function getSelectedCustomerInfoProperty()
    {
        if (empty($this->customerId)) {
            return null;
        }

        try {
            if (empty($this->customers) || !is_array($this->customers)) {
                return null;
            }

            $customer = collect($this->customers)->first(function ($customer) {
                $customerId = $customer['id'] ?? null;
                return (string) $customerId === (string) $this->customerId;
            });

            if (!$customer || !is_array($customer)) {
                return null;
            }

            $phone = $customer['phone'] ?? 'S/N';
            $identification = 'S/N';

            if (isset($customer['taxProfile']) && is_array($customer['taxProfile'])) {
                $identification = $customer['taxProfile']['identification'] ?? 'S/N';
            }

            return [
                'id' => $identification,
                'phone' => $phone,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function openCustomerDropdown()
    {
        $this->showCustomerDropdown = true;
    }

    public function closeCustomerDropdown()
    {
        $this->showCustomerDropdown = false;
    }

    public function updatedCustomerSearchTerm($value)
    {
        $this->showCustomerDropdown = true;
    }

    public function selectCustomer($customerId)
    {
        $this->customerId = (string) $customerId;
        $this->customerSearchTerm = '';
        $this->showCustomerDropdown = false;

        // Emitir evento al padre
        $this->dispatch('customerSelected', customerId: $customerId);
    }

    public function clearCustomerSelection()
    {
        $this->customerId = '';
        $this->customerSearchTerm = '';
        $this->showCustomerDropdown = false;

        // Emitir evento al padre
        $this->dispatch('customerCleared');
    }

    public function handleCustomerCreated($customerId, $customerData = [])
    {
        $this->refreshCustomers();
        $this->selectCustomer($customerId);
    }

    public function handleCustomerUpdate()
    {
        $this->refreshCustomers();
        $this->dispatch('customerListUpdated');
    }

    public function render()
    {
        return view('livewire.reservations.partials.customer-selector', [
            'filteredCustomers' => $this->filteredCustomers,
            'selectedCustomerInfo' => $this->selectedCustomerInfo,
        ]);
    }
}
