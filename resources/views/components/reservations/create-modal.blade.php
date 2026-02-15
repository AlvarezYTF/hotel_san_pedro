<div id="create-reservation-modal" class="fixed inset-0 z-50 hidden bg-black/50 p-4 notranslate" translate="no">
    <div class="mx-auto my-6 w-full max-w-6xl rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <h3 class="text-lg font-bold text-gray-900">Nueva Reserva</h3>
            <button type="button" onclick="closeCreateReservationModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="max-h-[85vh] overflow-y-auto p-5">
            @livewire('reservations.reservation-create', [
                'rooms' => $modalRooms ?? [],
                'roomsData' => $modalRoomsData ?? [],
                'customers' => $modalCustomers ?? [],
                'identificationDocuments' => $modalIdentificationDocuments ?? [],
                'legalOrganizations' => $modalLegalOrganizations ?? [],
                'tributes' => $modalTributes ?? [],
                'municipalities' => $modalMunicipalities ?? [],
            ])
        </div>
    </div>
</div>

<script>
function openCreateReservationModal() {
    const modal = document.getElementById('create-reservation-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closeCreateReservationModal() {
    const modal = document.getElementById('create-reservation-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('create-reservation-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCreateReservationModal();
            }
        });
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateReservationModal();
    }
});

</script>
