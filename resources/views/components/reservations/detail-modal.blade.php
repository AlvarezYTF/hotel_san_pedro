<div id="reservation-detail-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md overflow-y-auto h-full w-full hidden z-50 transition-all duration-300 px-3 py-4 sm:p-4 flex items-start sm:items-center justify-center">
    <div id="reservation-detail-card" class="relative mx-auto w-full max-w-lg max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] shadow-2xl rounded-[28px] bg-white overflow-hidden transform transition-all duration-200 border border-white/20 scale-95 opacity-0 flex flex-col">
        <div class="p-5 sm:p-6 pb-0 flex justify-between items-start">
            <div class="flex items-center space-x-4 min-w-0">
                <div class="h-12 w-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-emerald-200 shrink-0">
                    <span id="modal-initials">NN</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight truncate" id="modal-customer-name-header">Cliente</h3>
                    <p class="hidden" id="modal-customer-name">Cliente</p>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider rounded-md" id="modal-status">Activa</span>
                        <span class="text-slate-400 text-xs font-medium" id="modal-reservation-id">#RES</span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeReservationDetail()" class="p-2 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-xl transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="p-5 sm:p-6 pt-5 sm:pt-5 space-y-5 sm:space-y-6 overflow-y-auto">
            <div class="flex items-center justify-between p-3 sm:p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="text-center flex-1 border-r border-slate-200">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Noches</p>
                    <p class="text-sm font-bold text-slate-700" id="modal-nights">-</p>
                </div>
                <div class="text-center flex-1 border-r border-slate-200">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Huespedes</p>
                    <p class="text-sm font-bold text-slate-700" id="modal-guests-count">0</p>
                </div>
                <div class="text-center flex-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Habitacion</p>
                    <p class="text-sm font-bold text-slate-700 truncate px-1" id="modal-room-info">-</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-x-4 sm:gap-x-6 gap-y-4 sm:gap-y-5">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Check-in</label>
                    <p class="text-sm font-semibold text-slate-900 flex items-center">
                        <i class="far fa-calendar-alt mr-2 text-emerald-500"></i>
                        <span id="modal-checkin-date">-</span>
                    </p>
                    <p class="text-xs text-slate-500 ml-6" id="modal-checkin-time">-</p>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Check-out</label>
                    <p class="text-sm font-semibold text-slate-900 flex items-center">
                        <i class="far fa-calendar-alt mr-2 text-rose-500"></i>
                        <span id="modal-checkout-date">-</span>
                    </p>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Identificacion</label>
                    <p class="text-sm font-semibold text-slate-900" id="modal-customer-id">-</p>
                </div>
                <div class="space-y-1 text-right">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Telefono</label>
                    <p class="text-sm font-semibold text-slate-900" id="modal-customer-phone">-</p>
                </div>
            </div>

            <div class="bg-slate-900 rounded-3xl p-4 sm:p-5 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Resumen de Cobro</span>
                        <span class="text-[10px] font-black bg-white/10 px-2 py-1 rounded">COP</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-white/10">
                        <span class="text-sm text-slate-300">Total Estancia</span>
                        <span class="text-lg font-bold" id="modal-total">$0</span>
                    </div>
                    <div class="flex justify-between items-center pt-4">
                        <div>
                            <span class="text-[10px] font-bold text-rose-400 uppercase tracking-widest block mb-1">Saldo Pendiente</span>
                            <span class="text-2xl font-bold text-white" id="modal-balance">$0</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Pagado</span>
                            <span class="text-sm font-bold text-emerald-400" id="modal-deposit">$0</span>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl"></div>
            </div>

            <div class="space-y-1.5">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Notas</span>
                <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-2xl border border-slate-100 min-h-[48px]" id="modal-notes">Sin notas adicionales</p>
            </div>

            <div class="flex flex-col space-y-3">
                <button id="modal-checkin-btn" type="button"
                    class="hidden w-full bg-emerald-600 text-white py-3.5 rounded-2xl font-bold items-center justify-center space-x-2 hover:bg-emerald-700 transition-all">
                    <i class="fas fa-door-open text-sm"></i>
                    <span>Registrar Llegada (Check-In)</span>
                </button>
                <button id="modal-payment-btn" type="button"
                    class="hidden w-full bg-indigo-600 text-white py-3.5 rounded-2xl font-bold items-center justify-center space-x-2 hover:bg-indigo-700 transition-all">
                    <i class="fas fa-money-bill-wave text-sm"></i>
                    <span>Registrar Pago / Abono</span>
                </button>
                <button id="modal-cancel-payment-btn" type="button"
                    class="hidden w-full bg-rose-50 text-rose-700 py-3.5 rounded-2xl font-bold items-center justify-center space-x-2 hover:bg-rose-100 transition-all border border-rose-200">
                    <i class="fas fa-undo text-sm"></i>
                    <span>Anular Ultimo Pago</span>
                </button>
                <div class="flex space-x-3">
                    <a id="modal-edit-btn" href="#" class="flex-1 bg-slate-900 text-white py-3.5 rounded-2xl font-bold flex items-center justify-center space-x-2 hover:bg-slate-800 transition-all">
                        <i class="fas fa-edit text-sm"></i>
                        <span>Editar Reserva</span>
                    </a>
                    <a id="modal-pdf-btn" href="#" class="px-5 bg-slate-100 text-slate-600 py-3.5 rounded-2xl hover:bg-slate-200 transition-all flex items-center justify-center">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
                <div class="grid grid-cols-3 gap-2.5">
                    <a id="modal-view-document-btn" href="#" target="_blank" rel="noopener" class="flex flex-col items-center py-3 px-2 bg-slate-50 text-slate-600 rounded-2xl hover:bg-slate-100 transition-all border border-slate-100">
                        <i class="fas fa-eye mb-1 text-xs"></i>
                        <span class="text-[9px] font-bold uppercase">Ver</span>
                    </a>
                    <a id="modal-download-document-btn" href="#" class="flex flex-col items-center py-3 px-2 bg-slate-50 text-slate-600 rounded-2xl hover:bg-slate-100 transition-all border border-slate-100">
                        <i class="fas fa-download mb-1 text-xs"></i>
                        <span class="text-[9px] font-bold uppercase">Bajar</span>
                    </a>
                    <button id="modal-delete-btn" type="button" onclick="" class="hidden flex-col items-center py-3 px-2 bg-rose-50 text-rose-600 rounded-2xl hover:bg-rose-100 transition-all border border-rose-100">
                        <i class="fas fa-ban mb-1 text-xs"></i>
                        <span class="text-[9px] font-bold uppercase tracking-tight">Cancelar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="checkin-confirm-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-[60] px-3 py-4 sm:p-4 flex items-center justify-center">
    <div id="checkin-confirm-card" class="w-full max-w-md rounded-3xl bg-white border border-slate-100 shadow-2xl p-5 sm:p-6 transform transition-all duration-200 scale-95 opacity-0">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i class="fas fa-door-open text-base"></i>
                </div>
                <div>
                    <h4 class="text-base sm:text-lg font-bold text-slate-900">Confirmar llegada</h4>
                    <p class="text-xs text-slate-500">Registrar check-in real del huesped</p>
                </div>
            </div>
            <button type="button" onclick="closeCheckInConfirmModal()" class="p-2 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-xl transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="mt-4 space-y-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] font-black tracking-widest uppercase text-slate-400">Reserva</p>
                <p id="checkin-confirm-reservation" class="text-sm font-bold text-slate-800 mt-1">Reserva #---</p>
                <p id="checkin-confirm-customer" class="text-sm text-slate-600 mt-1">Cliente</p>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs text-emerald-700">
                <p class="font-semibold">
                    Se registrara la llegada con la fecha y hora actual del sistema.
                </p>
                <p class="mt-1 text-emerald-600">
                    Esta accion cambia la reserva a estado "Llego" en el calendario y listado.
                </p>
            </div>

            <div id="checkin-confirm-error" class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700"></div>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button type="button" onclick="closeCheckInConfirmModal()" class="w-full py-3 rounded-2xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-colors">
                Volver
            </button>
            <button id="checkin-confirm-submit-btn" type="button" onclick="confirmReservationCheckIn()" class="w-full py-3 rounded-2xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <svg id="checkin-confirm-submit-spinner" class="hidden animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span id="checkin-confirm-submit-text">Confirmar check-in</span>
            </button>
        </div>
    </div>
</div>

<div id="cancel-payment-confirm-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-[61] px-3 py-4 sm:p-4 flex items-center justify-center">
    <div id="cancel-payment-confirm-card" class="w-full max-w-md rounded-3xl bg-white border border-slate-100 shadow-2xl p-5 sm:p-6 transform transition-all duration-200 scale-95 opacity-0">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center">
                    <i class="fas fa-undo text-base"></i>
                </div>
                <div>
                    <h4 class="text-base sm:text-lg font-bold text-slate-900">Confirmar anulacion</h4>
                    <p class="text-xs text-slate-500">Revertir el ultimo pago registrado</p>
                </div>
            </div>
            <button type="button" onclick="closeCancelPaymentConfirmModal()" class="p-2 bg-slate-50 text-slate-400 hover:text-slate-600 rounded-xl transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="mt-4 space-y-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-[11px] font-black tracking-widest uppercase text-slate-400">Reserva</p>
                <p id="cancel-payment-confirm-reservation" class="text-sm font-bold text-slate-800 mt-1">Reserva #---</p>
                <p id="cancel-payment-confirm-customer" class="text-sm text-slate-600 mt-1">Cliente</p>
            </div>

            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                <p class="font-semibold">
                    Se anulara el ultimo pago y se recalcularan las noches pagadas de esta reserva.
                </p>
                <p class="mt-1 text-rose-700">
                    Monto a anular: <span id="cancel-payment-confirm-amount" class="font-black">$0</span>
                </p>
            </div>

            <div id="cancel-payment-confirm-error" class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700"></div>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button type="button" onclick="closeCancelPaymentConfirmModal()" class="w-full py-3 rounded-2xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-colors">
                Volver
            </button>
            <button id="cancel-payment-confirm-submit-btn" type="button" onclick="confirmReservationPaymentCancellation()" class="w-full py-3 rounded-2xl bg-rose-600 text-white font-bold hover:bg-rose-700 transition-colors inline-flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <svg id="cancel-payment-confirm-submit-spinner" class="hidden animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span id="cancel-payment-confirm-submit-text">Confirmar anulacion</span>
            </button>
        </div>
    </div>
</div>
