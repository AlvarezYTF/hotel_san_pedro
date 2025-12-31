<div class="space-y-4">
    <!-- Nombre -->
    <div>
        <label for="modal_name" class="block text-xs font-semibold text-gray-700 mb-2">
            Nombre completo <span class="text-red-500">*</span>
        </label>
        <input type="text" id="modal_name" name="name" required
               oninput="this.value = this.value.toUpperCase()"
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm uppercase"
               placeholder="EJ: JUAN PÉREZ GARCÍA">
    </div>

    <!-- Identificación y Teléfono -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="modal_identification" class="block text-xs font-semibold text-gray-700 mb-2">
                Número de identificación <span class="text-red-500">*</span>
            </label>
            <input type="text" id="modal_identification" name="identification" required
                   oninput="this.value = this.value.replace(/\D/g, '')"
                   maxlength="20"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                   placeholder="Ej: 12345678">
        </div>

        <div>
            <label for="modal_phone" class="block text-xs font-semibold text-gray-700 mb-2">
                Teléfono
            </label>
            <input type="text" id="modal_phone" name="phone"
                   oninput="this.value = this.value.replace(/\D/g, '')"
                   maxlength="10"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                   placeholder="Ej: 3001234567">
        </div>
    </div>

    <!-- Email -->
    <div>
        <label for="modal_email" class="block text-xs font-semibold text-gray-700 mb-2">
            Email
        </label>
        <input type="email" id="modal_email" name="email"
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
               placeholder="ejemplo@correo.com">
    </div>

    <!-- Checkbox para facturación electrónica -->
    <div class="flex items-center">
        <input type="checkbox" id="modal_requires_electronic_invoice" name="requires_electronic_invoice" value="1" checked
               class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
               onchange="document.getElementById('tax-fields-section').style.display = this.checked ? 'block' : 'none';">
        <label for="modal_requires_electronic_invoice" class="ml-2 text-sm text-gray-700">
            Requiere facturación electrónica
        </label>
    </div>

    <!-- Campos fiscales (mostrar si está marcado) -->
    <div id="tax-fields-section" class="space-y-4 border-t border-gray-200 pt-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <p class="text-xs text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                Complete los campos fiscales para poder facturar
            </p>
        </div>

        <!-- Tipo de Documento -->
        <div>
            <label for="modal_identification_document_id" class="block text-xs font-semibold text-gray-700 mb-2">
                Tipo de Documento <span class="text-red-500">*</span>
            </label>
            <select id="modal_identification_document_id" name="identification_document_id" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Seleccione...</option>
                @foreach($identificationDocuments as $doc)
                    <option value="{{ $doc->id }}" data-code="{{ $doc->code }}" data-requires-dv="{{ $doc->requires_dv ? '1' : '0' }}">
                        {{ $doc->name }}@if($doc->code) ({{ $doc->code }})@endif
                    </option>
                @endforeach
            </select>
        </div>


        <!-- DV (se mostrará si es necesario) -->
        <div id="dv-field" style="display: none;">
            <label for="modal_dv" class="block text-xs font-semibold text-gray-700 mb-2">
                Dígito Verificador (DV) <span class="text-red-500">*</span>
            </label>
            <input type="text" id="modal_dv" name="dv" maxlength="1" readonly
                   class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm font-bold">
        </div>

        <!-- Razón Social (para NIT) -->
        <div id="company-field" style="display: none;">
            <label for="modal_company" class="block text-xs font-semibold text-gray-700 mb-2">
                Razón Social / Empresa <span class="text-red-500">*</span>
            </label>
            <input type="text" id="modal_company" name="company"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                   placeholder="Razón social">
        </div>

        <!-- Municipio -->
        <div>
            <label for="modal_municipality_id" class="block text-xs font-semibold text-gray-700 mb-2">
                Municipio <span class="text-red-500">*</span>
            </label>
            @if($municipalities->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-xs text-yellow-800">No hay municipios disponibles. Sincronice desde Factus.</p>
                </div>
            @else
                <select id="modal_municipality_id" name="municipality_id" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Seleccione un municipio...</option>
                    @php
                        $currentDepartment = null;
                    @endphp
                    @foreach($municipalities as $municipality)
                        @if($currentDepartment !== $municipality->department)
                            @if($currentDepartment !== null)
                                </optgroup>
                            @endif
                            <optgroup label="{{ $municipality->department }}">
                            @php
                                $currentDepartment = $municipality->department;
                            @endphp
                        @endif
                        <option value="{{ $municipality->factus_id }}">
                            {{ $municipality->department }} - {{ $municipality->name }}
                        </option>
                        @if($loop->last)
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const requiresInvoice = document.getElementById('modal_requires_electronic_invoice');
    const taxFields = document.getElementById('tax-fields-section');
    const docSelect = document.getElementById('modal_identification_document_id');
    const dvField = document.getElementById('dv-field');
    const companyField = document.getElementById('company-field');
    const dvInput = document.getElementById('modal_dv');

    // Toggle tax fields
    requiresInvoice?.addEventListener('change', function() {
        if (this.checked) {
            taxFields.style.display = 'block';
        } else {
            taxFields.style.display = 'none';
        }
    });

    // Handle document type change
    docSelect?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const code = option.dataset.code;
        const requiresDv = option.dataset.requiresDv === '1';

        if (code === 'NIT') {
            companyField.style.display = 'block';
            document.getElementById('modal_company').required = true;
        } else {
            companyField.style.display = 'none';
            document.getElementById('modal_company').required = false;
        }

        if (requiresDv) {
            dvField.style.display = 'block';
            dvInput.required = true;
            calculateDV();
        } else {
            dvField.style.display = 'none';
            dvInput.required = false;
            dvInput.value = '';
        }
    });

    // Calculate DV for NIT
    const identificationInput = document.getElementById('modal_identification');
    identificationInput?.addEventListener('input', function() {
        if (docSelect?.value && docSelect.options[docSelect.selectedIndex].dataset.requiresDv === '1') {
            calculateDV();
        }
    });

    function calculateDV() {
        const nit = identificationInput.value.replace(/\D/g, '');
        if (!nit || nit.length < 6) {
            dvInput.value = '';
            return;
        }

        const weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        let sum = 0;
        
        for (let i = 0; i < nit.length; i++) {
            sum += parseInt(nit.charAt(nit.length - 1 - i)) * weights[i];
        }
        
        const remainder = sum % 11;
        dvInput.value = remainder < 2 ? remainder : 11 - remainder;
    }
});
</script>

