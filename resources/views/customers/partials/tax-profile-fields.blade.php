<div class="space-y-4" x-data="taxProfileForm()">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
        <p class="text-xs text-blue-800">
            <i class="fas fa-info-circle mr-1"></i>
            Complete los campos obligatorios para poder facturar
        </p>
    </div>

    <!-- Tipo de Documento -->
    <div>
        <label for="tax_identification_document_id" class="block text-xs font-semibold text-gray-700 mb-2">
            Tipo de Documento <span class="text-red-500">*</span>
        </label>
        <select id="tax_identification_document_id" name="identification_document_id" required
                x-model="identificationDocumentId"
                @change="updateRequiredFields()"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Seleccione...</option>
            @foreach($identificationDocuments as $doc)
                <option value="{{ $doc->id }}" 
                        data-code="{{ $doc->code }}" 
                        data-requires-dv="{{ $doc->requires_dv ? '1' : '0' }}">
                    {{ $doc->name }}@if($doc->code) ({{ $doc->code }})@endif
                </option>
            @endforeach
        </select>
    </div>

    <!-- Identificación -->
    <div>
        <label for="tax_identification" class="block text-xs font-semibold text-gray-700 mb-2">
            Identificación <span class="text-red-500">*</span>
        </label>
        <input type="text" id="tax_identification" name="identification" required
               x-model="identification"
               @input="identification = identification.replace(/\D/g, ''); calculateDV()"
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
               placeholder="Número de identificación">
    </div>

    <!-- DV -->
    <div x-show="requiresDV" x-cloak>
        <label for="tax_dv" class="block text-xs font-semibold text-gray-700 mb-2">
            Dígito Verificador (DV) <span class="text-red-500">*</span>
        </label>
        <input type="text" id="tax_dv" name="dv" maxlength="1" readonly
               x-model="dv"
               class="block w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm font-bold">
    </div>

    <!-- Razón Social (para NIT) -->
    <div x-show="isJuridicalPerson" x-cloak>
        <label for="tax_company" class="block text-xs font-semibold text-gray-700 mb-2">
            Razón Social / Empresa <span class="text-red-500">*</span>
        </label>
        <input type="text" id="tax_company" name="company" required
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
               placeholder="Razón social">
    </div>

    <!-- Nombre Comercial -->
    <div>
        <label for="tax_trade_name" class="block text-xs font-semibold text-gray-700 mb-2">
            Nombre Comercial
        </label>
        <input type="text" id="tax_trade_name" name="trade_name"
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
               placeholder="Nombre comercial">
    </div>

    <!-- Municipio -->
    <div>
        <label for="tax_municipality_id" class="block text-xs font-semibold text-gray-700 mb-2">
            Municipio <span class="text-red-500">*</span>
        </label>
        @if($municipalities->isEmpty())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-xs text-yellow-800">No hay municipios disponibles. Sincronice desde Factus.</p>
            </div>
        @else
            <select id="tax_municipality_id" name="municipality_id" required
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

    <!-- Tipo de Organización Legal -->
    <div>
        <label for="tax_legal_organization_id" class="block text-xs font-semibold text-gray-700 mb-2">
            Tipo de Organización Legal
        </label>
        <select id="tax_legal_organization_id" name="legal_organization_id"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Seleccione...</option>
            @foreach($legalOrganizations as $org)
                <option value="{{ $org->id }}">
                    {{ $org->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Régimen Tributario -->
    <div>
        <label for="tax_tribute_id" class="block text-xs font-semibold text-gray-700 mb-2">
            Régimen Tributario
        </label>
        <select id="tax_tribute_id" name="tribute_id"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Seleccione...</option>
            @foreach($tributes as $tribute)
                <option value="{{ $tribute->id }}">
                    {{ $tribute->name }} ({{ $tribute->code }})
                </option>
            @endforeach
        </select>
    </div>
</div>

<script>
function taxProfileForm() {
    return {
        identificationDocumentId: '',
        identification: '',
        dv: '',
        requiresDV: false,
        isJuridicalPerson: false,
        
        updateRequiredFields() {
            const select = document.getElementById('tax_identification_document_id');
            const option = select?.options[select?.selectedIndex];
            
            if (option) {
                this.requiresDV = option.dataset.requiresDv === '1';
                this.isJuridicalPerson = option.dataset.code === 'NIT';
                
                if (this.isJuridicalPerson) {
                    this.calculateDV();
                } else {
                    this.dv = '';
                }
            }
        },
        
        calculateDV() {
            if (!this.isJuridicalPerson || !this.identification) {
                this.dv = '';
                return;
            }
            
            const nit = this.identification.replace(/\D/g, '');
            if (nit.length < 6) {
                this.dv = '';
                return;
            }
            
            const weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
            let sum = 0;
            
            for (let i = 0; i < nit.length; i++) {
                sum += parseInt(nit.charAt(nit.length - 1 - i)) * weights[i];
            }
            
            const remainder = sum % 11;
            this.dv = remainder < 2 ? remainder : 11 - remainder;
        }
    }
}
</script>

