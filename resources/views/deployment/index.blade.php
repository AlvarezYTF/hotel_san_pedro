<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deployment - MovilTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-success { color: #10b981; }
        .status-error { color: #ef4444; }
        .status-warning { color: #f59e0b; }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">🚀 Deployment Center</h1>
            <p class="text-gray-600">Ejecuta migraciones y seeders de forma segura</p>
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded">
                <p class="text-sm text-blue-800 font-semibold mb-2">
                    ✅ SEGURIDAD: Los comandos disponibles son seguros y no afectan tus datos existentes.
                </p>
                <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                    <li><strong>Seeders DIAN:</strong> Usan updateOrInsert - Solo actualizan o crean catálogos</li>
                    <li><strong>Sincronizaciones Factus:</strong> Usan updateOrCreate - Solo actualizan o crean datos de catálogos</li>
                    <li><strong>Migraciones:</strong> Solo ejecuta pendientes - No re-ejecuta migraciones ya aplicadas</li>
                </ul>
            </div>
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm text-yellow-800">
                    <strong>⚠️ IMPORTANTE:</strong> Estas rutas son temporales. Elimínalas después del despliegue.
                </p>
            </div>
            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded">
                <p class="text-sm text-green-800">
                    <strong>💡 RECOMENDACIÓN:</strong> Para mayor seguridad, haz un backup de la base de datos antes de ejecutar migraciones nuevas.
                </p>
            </div>
        </div>

        <!-- Migration Status -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Estado de Migraciones</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 border rounded-lg">
                    <div class="font-medium">Total de Migraciones</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $migrationStatus['total'] ?? 0 }}</div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium">Ejecutadas</div>
                    <div class="text-2xl font-bold status-success">{{ $migrationStatus['executed'] ?? 0 }}</div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium">Pendientes</div>
                    <div class="text-2xl font-bold {{ ($migrationStatus['pending'] ?? 0) > 0 ? 'status-warning' : 'status-success' }}">
                        {{ $migrationStatus['pending'] ?? 0 }}
                    </div>
                </div>
            </div>
            @if(isset($migrationStatus['pending_list']) && count($migrationStatus['pending_list']) > 0)
            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm font-semibold text-yellow-800 mb-2">Migraciones Pendientes:</p>
                <ul class="text-sm text-yellow-700 list-disc list-inside">
                    @foreach($migrationStatus['pending_list'] as $migration)
                    <li>{{ $migration }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <!-- Catalog Status -->
        @if(isset($catalogCounts) && count($catalogCounts) > 0)
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Estado de Catálogos DIAN</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Documentos ID</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['identification_documents'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['identification_documents'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Organizaciones</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['legal_organizations'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['legal_organizations'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Tributos</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['customer_tributes'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['customer_tributes'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Tipos Doc.</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['document_types'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['document_types'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Tipos Op.</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['operation_types'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['operation_types'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Métodos Pago</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['payment_methods'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['payment_methods'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Formas Pago</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['payment_forms'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['payment_forms'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Estándares</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['product_standards'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['product_standards'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Municipios</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['municipalities'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['municipalities'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Rangos Total</div>
                    <div class="text-xl font-bold">{{ $catalogCounts['numbering_ranges'] ?? 0 }}</div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Rangos Activos</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['active_ranges'] ?? 0) > 0 ? 'status-success' : 'status-warning' }}">
                        {{ $catalogCounts['active_ranges'] ?? 0 }}
                    </div>
                </div>
                <div class="p-4 border rounded-lg">
                    <div class="font-medium text-sm">Unidades Med.</div>
                    <div class="text-xl font-bold {{ ($catalogCounts['measurement_units'] ?? 0) > 0 ? 'status-success' : 'status-error' }}">
                        {{ $catalogCounts['measurement_units'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Acciones</h2>
            
            <!-- Migrations -->
            <div class="mb-6 pb-6 border-b">
                <h3 class="text-lg font-medium mb-3">Migraciones</h3>
                <div class="space-y-3">
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded mb-3">
                        <p class="text-sm text-amber-800 font-semibold mb-1">⚠️ Antes de ejecutar migraciones:</p>
                        <ul class="text-sm text-amber-700 list-disc list-inside space-y-1">
                            <li>Revisa las migraciones pendientes listadas abajo</li>
                            <li>Verifica que no tengan código destructivo (dropColumn, dropTable) en el método <code>up()</code></li>
                            <li><strong>Recomendación:</strong> Haz un backup de la base de datos antes</li>
                        </ul>
                    </div>
                    <div>
                        <button
                            onclick="runMigrations()"
                            class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                        >
                            🔄 Ejecutar Migraciones Pendientes
                        </button>
                        <p class="text-sm text-gray-600 mt-2">
                            Ejecuta todas las migraciones pendientes. Solo se ejecutarán las que no han sido ejecutadas antes.
                            <strong class="text-amber-600">Laravel NO vuelve a ejecutar migraciones ya aplicadas.</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Seeders -->
            <div class="mb-6 pb-6 border-b">
                <h3 class="text-lg font-medium mb-3">Seeders Disponibles</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Todos los seeders son seguros y pueden ejecutarse múltiples veces sin duplicar datos (usan firstOrCreate/updateOrCreate).
                </p>
                
                <!-- Sistema y Roles -->
                <div class="mb-4">
                    <h4 class="text-md font-semibold mb-2 text-gray-700">Sistema y Roles</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <button onclick="runSeeder('RoleSeeder')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            🔐 Roles y Permisos
                        </button>
                        <button onclick="runSeeder('UserSeeder')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            👤 Usuarios del Sistema
                        </button>
                        <button onclick="runSeeder('SalesPermissionSeeder')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            📊 Permisos de Ventas
                        </button>
                    </div>
                </div>

                <!-- Catálogos de Negocio -->
                <div class="mb-4">
                    <h4 class="text-md font-semibold mb-2 text-gray-700">Catálogos de Negocio</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <button onclick="runSeeder('CategorySeeder')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            📁 Categorías
                        </button>
                        <button onclick="runSeeder('SupplierSeeder')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            🏭 Proveedores
                        </button>
                        <button onclick="runSeeder('ProductSeeder')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            📦 Productos
                        </button>
                        <button onclick="runSeeder('CustomerSeeder')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                            👥 Clientes
                        </button>
                    </div>
                </div>

                <!-- Catálogos DIAN -->
                <div class="mb-4">
                    <h4 class="text-md font-semibold mb-2 text-gray-700">Catálogos DIAN</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <button onclick="runSeeder('DianIdentificationDocumentSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            📄 Documentos de Identificación
                        </button>
                        <button onclick="runSeeder('DianLegalOrganizationSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            🏢 Organizaciones Legales
                        </button>
                        <button onclick="runSeeder('DianCustomerTributeSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            💰 Tributos de Cliente
                        </button>
                        <button onclick="runSeeder('DianDocumentTypeSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            📋 Tipos de Documento
                        </button>
                        <button onclick="runSeeder('DianOperationTypeSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            ⚙️ Tipos de Operación
                        </button>
                        <button onclick="runSeeder('DianPaymentMethodSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            💳 Métodos de Pago
                        </button>
                        <button onclick="runSeeder('DianPaymentFormSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            💵 Formas de Pago
                        </button>
                        <button onclick="runSeeder('DianProductStandardSeeder')" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors text-sm">
                            📦 Estándares de Producto
                        </button>
                    </div>
                </div>

                <!-- Seeders Compuestos -->
                <div>
                    <h4 class="text-md font-semibold mb-2 text-gray-700">Seeders Compuestos</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <button onclick="runSeeder('DatabaseSeeder')" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors text-sm">
                            🗄️ Database Seeder (Todos los básicos)
                        </button>
                        <button onclick="runSeeder('ProductionSeeder')" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors text-sm">
                            🏭 Production Seeder (Solo DIAN)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Factus Sync -->
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-3">Sincronización desde Factus</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Sincroniza datos desde la API de Factus. Estos comandos son seguros y pueden ejecutarse múltiples veces.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <button onclick="syncMunicipalities()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        🏘️ Sincronizar Municipios
                    </button>
                    <button onclick="syncNumberingRanges()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        🔢 Sincronizar Rangos de Numeración
                    </button>
                    <button onclick="syncMeasurementUnits()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        📏 Sincronizar Unidades de Medida
                    </button>
                </div>
            </div>

            <!-- Status -->
            <div class="mt-4">
                <button
                    onclick="checkStatus()"
                    class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors"
                >
                    📊 Ver Estado Completo
                </button>
                <p class="text-sm text-gray-600 mt-2">
                    Verifica el estado actual de migraciones y tablas
                </p>
            </div>
        </div>

        <!-- Results -->
        <div id="results" class="bg-white rounded-lg shadow-lg p-6 hidden">
            <h2 class="text-xl font-semibold mb-4">Resultados</h2>
            <pre id="output" class="bg-gray-100 p-4 rounded text-sm overflow-auto max-h-96"></pre>
        </div>
    </div>

    <script>
        const token = new URLSearchParams(window.location.search).get('token') || '{{ $token ?? '' }}';

        function showResults(data) {
            const resultsDiv = document.getElementById('results');
            const outputPre = document.getElementById('output');
            resultsDiv.classList.remove('hidden');
            
            if (typeof data === 'string') {
                outputPre.textContent = data;
            } else {
                outputPre.textContent = JSON.stringify(data, null, 2);
            }
            
            // Scroll to results
            resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function showSuccess(message) {
            showResults({ success: true, message: message });
            setTimeout(() => location.reload(), 2000);
        }

        function showError(message) {
            showResults({ success: false, error: message });
        }

        function runMigrations() {
            if (!confirm('¿Estás seguro de ejecutar las migraciones pendientes?')) {
                return;
            }
            
            fetch('{{ route("deployment.migrate") }}?token=' + token, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            })
                .then(response => response.json())
                .then(data => {
                    showResults(data);
                    if (data.success) {
                        setTimeout(() => location.reload(), 3000);
                    }
                })
                .catch(error => {
                    showError('Error: ' + error.message);
                });
        }

        function runSeeder(seederName) {
            if (!confirm(`¿Ejecutar el seeder ${seederName}?`)) {
                return;
            }
            
            fetch('{{ route("deployment.seed") }}?token=' + token + '&seeder=' + seederName, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            })
                .then(response => response.json())
                .then(data => {
                    showResults(data);
                    if (data.success) {
                        setTimeout(() => location.reload(), 2000);
                    }
                })
                .catch(error => {
                    showError('Error: ' + error.message);
                });
        }

        function syncMunicipalities() {
            if (!confirm('¿Sincronizar municipios desde Factus?')) {
                return;
            }
            
            fetch('{{ route("deployment.sync-municipalities") }}?token=' + token, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            })
                .then(response => response.json())
                .then(data => {
                    showResults(data);
                    if (data.success) {
                        setTimeout(() => location.reload(), 2000);
                    }
                })
                .catch(error => {
                    showError('Error: ' + error.message);
                });
        }

        function syncNumberingRanges() {
            if (!confirm('¿Sincronizar rangos de numeración desde Factus?')) {
                return;
            }
            
            fetch('{{ route("deployment.sync-numbering-ranges") }}?token=' + token, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            })
                .then(response => response.json())
                .then(data => {
                    showResults(data);
                    if (data.success) {
                        setTimeout(() => location.reload(), 2000);
                    }
                })
                .catch(error => {
                    showError('Error: ' + error.message);
                });
        }

        function syncMeasurementUnits() {
            if (!confirm('¿Sincronizar unidades de medida desde Factus?')) {
                return;
            }
            
            fetch('{{ route("deployment.sync-measurement-units") }}?token=' + token, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            })
                .then(response => response.json())
                .then(data => {
                    showResults(data);
                    if (data.success) {
                        setTimeout(() => location.reload(), 2000);
                    }
                })
                .catch(error => {
                    showError('Error: ' + error.message);
                });
        }

        function checkStatus() {
            fetch('{{ route("deployment.status") }}?token=' + token)
                .then(response => response.json())
                .then(data => {
                    showResults(data);
                })
                .catch(error => {
                    showError('Error: ' + error.message);
                });
        }
    </script>
</body>
</html>

