<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    private const ASEO_KEYWORDS = [
        'aseo',
        'limpieza',
        'amenities',
        'insumo',
        'papel',
        'jabon',
        'cloro',
        'mantenimiento',
    ];

    public string $search = '';
    public string $category_id = '';
    public string $status = '';

    public bool $createModalOpen = false;
    public bool $editModalOpen = false;
    public bool $showModalOpen = false;
    public bool $stockModalOpen = false;

    public ?int $selectedProductId = null;
    public ?int $editingProductId = null;
    public ?int $stockProductId = null;

    public string $inventoryGroup = 'ventas';

    public array $form = [
        'name' => '',
        'sku' => '',
        'category_id' => '',
        'quantity' => 0,
        'low_stock_threshold' => 10,
        'price' => 0,
        'status' => 'active',
    ];

    public array $stockForm = [
        'movement_type' => 'adjustment',
        'direction' => 'increase',
        'quantity' => 1,
        'reason' => '',
    ];

    public array $showProduct = [];
    public array $showMovements = [];
    public array $stockModalProduct = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'category_id' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->resetProductForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->ensurePermission('create_products', 'No tiene permisos para crear productos.');

        $this->editModalOpen = false;
        $this->showModalOpen = false;
        $this->stockModalOpen = false;
        $this->resetValidation();
        $this->resetProductForm();
        $this->createModalOpen = true;
    }

    public function closeCreateModal(): void
    {
        $this->createModalOpen = false;
        $this->resetValidation();
        $this->resetProductForm();
    }

    public function storeProduct(): void
    {
        $this->ensurePermission('create_products', 'No tiene permisos para crear productos.');

        $payload = $this->validateProductForm();
        $initialQuantity = (int) $payload['quantity'];
        $payload['quantity'] = 0;

        DB::transaction(function () use ($payload, $initialQuantity): void {
            $product = Product::query()->create($payload);

            if ($initialQuantity > 0) {
                $product->recordMovement($initialQuantity, 'input', 'Carga inicial desde modal de inventario');
            }
        });

        $this->clearProductsCache();
        $this->closeCreateModal();
        $this->resetPage();

        session()->flash('success', 'Producto creado exitosamente.');
    }

    public function openEditModal(int $productId): void
    {
        $this->ensurePermission('edit_products', 'No tiene permisos para editar productos.');

        $this->createModalOpen = false;
        $this->showModalOpen = false;
        $this->stockModalOpen = false;

        $product = Product::query()
            ->with('category:id,name')
            ->findOrFail($productId);

        $this->editingProductId = $product->id;
        $this->inventoryGroup = $this->detectInventoryGroup($product->category?->name);
        $this->form = [
            'name' => (string) $product->name,
            'sku' => (string) ($product->sku ?? ''),
            'category_id' => (string) $product->category_id,
            'quantity' => (int) $product->quantity,
            'low_stock_threshold' => (int) ($product->low_stock_threshold ?? 10),
            'price' => (float) $product->price,
            'status' => (string) $product->status,
        ];

        $this->resetValidation();
        $this->editModalOpen = true;
    }

    public function closeEditModal(): void
    {
        $this->editModalOpen = false;
        $this->editingProductId = null;
        $this->resetValidation();
        $this->resetProductForm();
    }

    public function updateProduct(): void
    {
        $this->ensurePermission('edit_products', 'No tiene permisos para editar productos.');

        if (!$this->editingProductId) {
            return;
        }

        $payload = $this->validateProductForm($this->editingProductId);
        $newQuantity = (int) $payload['quantity'];
        unset($payload['quantity']);

        $productId = $this->editingProductId;
        DB::transaction(function () use ($productId, $payload, $newQuantity): void {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            $oldQuantity = (int) $product->quantity;
            $product->update($payload);

            $difference = $newQuantity - $oldQuantity;
            if ($difference !== 0) {
                $direction = $difference > 0 ? 'aumento' : 'disminucion';
                $product->recordMovement(
                    $difference,
                    'adjustment',
                    "Ajuste manual desde edicion en modal ({$direction})"
                );
            }
        });

        $this->clearProductsCache();
        $this->closeEditModal();
        $this->refreshShowModalIfNeeded($productId);

        session()->flash('success', 'Producto actualizado exitosamente.');
    }

    public function openShowModal(int $productId): void
    {
        $this->ensurePermission('view_products', 'No tiene permisos para ver productos.');

        $this->createModalOpen = false;
        $this->editModalOpen = false;
        $this->stockModalOpen = false;
        $this->loadShowData($productId);
        $this->showModalOpen = true;
    }

    public function closeShowModal(): void
    {
        $this->showModalOpen = false;
        $this->selectedProductId = null;
        $this->showProduct = [];
        $this->showMovements = [];
    }

    public function openStockModal(int $productId): void
    {
        $this->ensurePermission('edit_products', 'No tiene permisos para ajustar stock.');

        $this->createModalOpen = false;
        $this->editModalOpen = false;
        $this->showModalOpen = false;

        $product = Product::query()
            ->with('category:id,name')
            ->findOrFail($productId);

        $this->stockProductId = $product->id;
        $this->stockModalProduct = [
            'id' => $product->id,
            'name' => (string) $product->name,
            'quantity' => (int) $product->quantity,
            'category' => (string) ($product->category?->name ?? 'Sin categoria'),
        ];

        $this->stockForm = [
            'movement_type' => 'adjustment',
            'direction' => 'increase',
            'quantity' => 1,
            'reason' => '',
        ];

        $this->resetValidation();
        $this->stockModalOpen = true;
    }

    public function closeStockModal(): void
    {
        $this->stockModalOpen = false;
        $this->stockProductId = null;
        $this->stockForm = [
            'movement_type' => 'adjustment',
            'direction' => 'increase',
            'quantity' => 1,
            'reason' => '',
        ];
        $this->stockModalProduct = [];
        $this->resetValidation();
    }

    public function updatedStockFormMovementType(string $value): void
    {
        if ($value === 'input') {
            $this->stockForm['direction'] = 'increase';
            return;
        }

        if ($value === 'output') {
            $this->stockForm['direction'] = 'decrease';
        }
    }

    public function updatedInventoryGroup(string $value): void
    {
        if ($value === 'aseo') {
            $this->form['price'] = 0;
            return;
        }

        if ($value !== 'ventas') {
            return;
        }

        $categories = $this->getCategoriesCollection();
        [, $saleCategories] = $this->splitCategories($categories);

        if ($saleCategories->isEmpty()) {
            $this->form['category_id'] = '';
            return;
        }

        $currentCategoryId = (int) ($this->form['category_id'] ?? 0);
        $isValidSaleCategory = $saleCategories->contains(
            fn ($category) => (int) $category->id === $currentCategoryId
        );

        if (!$isValidSaleCategory) {
            $this->form['category_id'] = (string) $saleCategories->first()->id;
        }
    }

    public function applyStockAdjustment(): void
    {
        $this->ensurePermission('edit_products', 'No tiene permisos para ajustar stock.');

        if (!$this->stockProductId) {
            return;
        }

        $validated = Validator::make(
            $this->stockForm,
            [
                'movement_type' => ['required', Rule::in(['input', 'output', 'adjustment'])],
                'direction' => ['required', Rule::in(['increase', 'decrease'])],
                'quantity' => ['required', 'integer', 'min:1'],
                'reason' => ['nullable', 'string', 'max:255'],
            ],
            [
                'quantity.required' => 'La cantidad es obligatoria.',
                'quantity.min' => 'La cantidad debe ser mayor a 0.',
                'movement_type.required' => 'Debe seleccionar un tipo de movimiento.',
                'direction.required' => 'Debe seleccionar una direccion de ajuste.',
            ]
        )->validate();

        if ($validated['movement_type'] === 'input') {
            $validated['direction'] = 'increase';
        } elseif ($validated['movement_type'] === 'output') {
            $validated['direction'] = 'decrease';
        }

        $signedQuantity = (int) $validated['quantity'];
        if ($validated['direction'] === 'decrease') {
            $signedQuantity *= -1;
        }

        $reason = trim((string) ($validated['reason'] ?? ''));
        if ($reason === '') {
            $reason = $this->defaultReasonForMovement($validated['movement_type'], $signedQuantity);
        }

        try {
            $currentStock = $this->executeStockMovement(
                $this->stockProductId,
                $signedQuantity,
                $validated['movement_type'],
                $reason
            );
        } catch (ValidationException $e) {
            $message = $this->extractValidationMessage($e);
            $this->addError('stockForm.quantity', $message);
            return;
        }

        $productId = $this->stockProductId;
        $this->clearProductsCache();
        $this->closeStockModal();
        $this->refreshShowModalIfNeeded($productId);

        session()->flash('success', "Movimiento registrado. Stock actual: {$currentStock}.");
    }

    public function deleteProduct(int $id): void
    {
        $this->ensurePermission('delete_products', 'No tiene permisos para eliminar productos.');

        $product = Product::query()->findOrFail($id);
        $product->delete();

        if ($this->selectedProductId === $id) {
            $this->closeShowModal();
        }

        if ($this->editingProductId === $id) {
            $this->closeEditModal();
        }

        if ($this->stockProductId === $id) {
            $this->closeStockModal();
        }

        $this->clearProductsCache();
        session()->flash('success', 'Producto eliminado exitosamente.');
    }

    public function increaseStock(int $productId): void
    {
        $this->runQuickStockChange(
            $productId,
            1,
            'adjustment',
            'Ajuste rapido (+1) desde gestion de inventario'
        );
    }

    public function decreaseStock(int $productId): void
    {
        $this->runQuickStockChange(
            $productId,
            -1,
            'adjustment',
            'Ajuste rapido (-1) desde gestion de inventario'
        );
    }

    public function render()
    {
        $query = Product::query()->with('category');

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        if ($this->category_id !== '') {
            $query->where('category_id', $this->category_id);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $products = $query
            ->orderBy('name')
            ->paginate(15);

        $categories = $this->getCategoriesCollection();
        [$aseoCategories, $saleCategories] = $this->splitCategories($categories);

        return view('livewire.product-list', [
            'products' => $products,
            'categories' => $categories,
            'aseoCategories' => $aseoCategories,
            'saleCategories' => $saleCategories,
        ]);
    }

    private function runQuickStockChange(int $productId, int $signedQuantity, string $type, string $reason): void
    {
        $this->ensurePermission('edit_products', 'No tiene permisos para ajustar stock.');

        if ($productId <= 0) {
            throw new \DomainException('ID de producto invalido.');
        }

        try {
            $currentStock = $this->executeStockMovement($productId, $signedQuantity, $type, $reason);
        } catch (ValidationException $e) {
            session()->flash('error', $this->extractValidationMessage($e));
            return;
        }

        $this->clearProductsCache();
        $this->refreshShowModalIfNeeded($productId);

        if ($signedQuantity > 0) {
            session()->flash('success', "Stock actualizado (+{$signedQuantity}). Actual: {$currentStock}.");
            return;
        }

        session()->flash('success', "Stock actualizado ({$signedQuantity}). Actual: {$currentStock}.");
    }

    private function executeStockMovement(int $productId, int $signedQuantity, string $type, string $reason): int
    {
        return DB::transaction(function () use ($productId, $signedQuantity, $type, $reason): int {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($productId);

            $available = (int) $product->quantity;
            $newStock = $available + $signedQuantity;
            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'stockForm.quantity' => "Stock insuficiente. Disponible: {$available}.",
                ]);
            }

            $product->recordMovement($signedQuantity, $type, $reason);

            return $newStock;
        });
    }

    private function validateProductForm(?int $ignoreProductId = null): array
    {
        $categoryId = $this->resolveCategoryIdFromGroup();
        $sanitizedSku = Str::upper(trim((string) ($this->form['sku'] ?? '')));

        $normalizedPrice = $this->normalizeCurrencyToFloat($this->form['price'] ?? 0);
        if ($this->inventoryGroup === 'aseo') {
            $normalizedPrice = 0;
        }

        $payload = [
            'name' => trim((string) ($this->form['name'] ?? '')),
            'sku' => $sanitizedSku !== '' ? $sanitizedSku : null,
            'category_id' => $categoryId,
            'quantity' => (int) ($this->form['quantity'] ?? 0),
            'low_stock_threshold' => (int) ($this->form['low_stock_threshold'] ?? 10),
            'price' => $normalizedPrice,
            'status' => (string) ($this->form['status'] ?? 'active'),
        ];

        $nameRule = Rule::unique('products', 'name');
        $skuRule = Rule::unique('products', 'sku');
        if ($ignoreProductId) {
            $nameRule = $nameRule->ignore($ignoreProductId);
            $skuRule = $skuRule->ignore($ignoreProductId);
        }

        return Validator::make(
            $payload,
            [
                'name' => ['required', 'string', 'max:255', $nameRule],
                'sku' => ['nullable', 'string', 'max:255', $skuRule],
                'category_id' => ['required', 'exists:categories,id'],
                'quantity' => ['required', 'integer', 'min:0'],
                'low_stock_threshold' => ['required', 'integer', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                'status' => ['required', Rule::in(['active', 'inactive', 'discontinued'])],
            ],
            [
                'name.required' => 'El nombre del producto es obligatorio.',
                'name.unique' => 'Ya existe un producto con este nombre.',
                'category_id.required' => 'Debe seleccionar una categoria.',
                'category_id.exists' => 'La categoria seleccionada no es valida.',
                'quantity.required' => 'La cantidad de stock es obligatoria.',
                'quantity.min' => 'La cantidad no puede ser negativa.',
                'low_stock_threshold.required' => 'El umbral de stock bajo es obligatorio.',
                'low_stock_threshold.min' => 'El umbral no puede ser negativo.',
                'price.required' => 'El precio es obligatorio.',
                'price.min' => 'El precio no puede ser negativo.',
                'status.required' => 'Debe seleccionar un estado.',
            ]
        )->validate();
    }

    private function resolveCategoryIdFromGroup(): ?int
    {
        if ($this->inventoryGroup === 'aseo') {
            return $this->findOrCreateAseoCategoryId();
        }

        $categoryId = (int) ($this->form['category_id'] ?? 0);
        return $categoryId > 0 ? $categoryId : null;
    }

    private function findOrCreateAseoCategoryId(): int
    {
        $category = Category::query()
            ->where(function ($query): void {
                foreach (self::ASEO_KEYWORDS as $keyword) {
                    $query->orWhere('name', 'like', '%' . $keyword . '%');
                }
            })
            ->first();

        if (!$category) {
            $category = Category::query()->create([
                'name' => 'Insumos de Aseo',
                'description' => 'Categoria automatica para productos de limpieza',
                'color' => '#6366f1',
                'is_active' => true,
            ]);
        }

        return (int) $category->id;
    }

    private function splitCategories(Collection $categories): array
    {
        $aseoCategories = $categories
            ->filter(fn ($category) => $this->isAseoCategoryName((string) $category->name))
            ->values();

        $saleCategories = $categories
            ->filter(fn ($category) => !$this->isAseoCategoryName((string) $category->name))
            ->values();

        return [$aseoCategories, $saleCategories];
    }

    private function isAseoCategoryName(string $name): bool
    {
        $normalized = Str::lower($name);
        foreach (self::ASEO_KEYWORDS as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function detectInventoryGroup(?string $categoryName): string
    {
        if (!$categoryName) {
            return 'ventas';
        }

        return $this->isAseoCategoryName($categoryName) ? 'aseo' : 'ventas';
    }

    private function getCategoriesCollection(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'color']);
    }

    private function loadShowData(int $productId): void
    {
        $product = Product::query()
            ->with('category:id,name,color')
            ->findOrFail($productId);

        $this->selectedProductId = $product->id;
        $this->showProduct = [
            'id' => $product->id,
            'name' => (string) $product->name,
            'sku' => (string) ($product->sku ?? ''),
            'category_name' => (string) ($product->category?->name ?? 'Sin categoria'),
            'category_color' => (string) ($product->category?->color ?? '#6B7280'),
            'quantity' => (int) $product->quantity,
            'low_stock_threshold' => (int) ($product->low_stock_threshold ?? 10),
            'price' => (float) $product->price,
            'status' => (string) $product->status,
            'created_at' => $product->created_at?->format('d/m/Y H:i'),
            'updated_at' => $product->updated_at?->format('d/m/Y H:i'),
        ];

        $this->showMovements = InventoryMovement::query()
            ->with('user:id,name')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (InventoryMovement $movement): array {
                return [
                    'id' => $movement->id,
                    'type' => $movement->type,
                    'translated_type' => $movement->translated_type,
                    'quantity' => (int) $movement->quantity,
                    'previous_stock' => (int) $movement->previous_stock,
                    'current_stock' => (int) $movement->current_stock,
                    'reason' => (string) ($movement->reason ?? ''),
                    'created_at' => $movement->created_at?->format('d/m/Y H:i'),
                    'user_name' => (string) ($movement->user?->name ?? 'Sistema'),
                ];
            })
            ->all();
    }

    private function refreshShowModalIfNeeded(int $productId): void
    {
        if ($this->showModalOpen && $this->selectedProductId === $productId) {
            $this->loadShowData($productId);
        }
    }

    private function resetProductForm(): void
    {
        $categories = $this->getCategoriesCollection();
        [$aseoCategories, $saleCategories] = $this->splitCategories($categories);

        $this->inventoryGroup = $saleCategories->isNotEmpty() ? 'ventas' : 'aseo';
        $categoryId = '';

        if ($this->inventoryGroup === 'ventas' && $saleCategories->isNotEmpty()) {
            $categoryId = (string) $saleCategories->first()->id;
        } elseif ($aseoCategories->isNotEmpty()) {
            $categoryId = (string) $aseoCategories->first()->id;
        }

        $this->form = [
            'name' => '',
            'sku' => '',
            'category_id' => $categoryId,
            'quantity' => 0,
            'low_stock_threshold' => 10,
            'price' => 0,
            'status' => 'active',
        ];
    }

    private function normalizeCurrencyToFloat(mixed $rawValue): float
    {
        if (is_numeric($rawValue)) {
            return (float) $rawValue;
        }

        $value = trim((string) $rawValue);
        if ($value === '') {
            return 0;
        }

        $value = str_replace(' ', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function defaultReasonForMovement(string $movementType, int $signedQuantity): string
    {
        return match ($movementType) {
            'input' => 'Entrada manual desde modal de inventario',
            'output' => 'Salida manual desde modal de inventario',
            default => $signedQuantity > 0
                ? 'Ajuste manual (+) desde modal de inventario'
                : 'Ajuste manual (-) desde modal de inventario',
        };
    }

    private function extractValidationMessage(ValidationException $exception): string
    {
        $messages = collect($exception->errors())->flatten();
        return (string) ($messages->first() ?? 'No fue posible completar la operacion.');
    }

    private function ensurePermission(string $permission, string $message): void
    {
        abort_unless(auth()->check() && auth()->user()?->can($permission), 403, $message);
    }

    private function clearProductsCache(): void
    {
        app(ProductRepository::class)->clearCache();
    }
}
