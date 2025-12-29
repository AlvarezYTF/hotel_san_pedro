// Reports Manager JavaScript
// Handles Chart.js initialization and TomSelect filters

let filterSelects = {};
let chartInstances = {};
let reinitTimeout = null;
let lastDataHash = null;

function initializeFilterSelects() {
    // Destroy existing TomSelect instances
    const selectIds = ['filter_receptionist_id', 'filter_room_id', 'filter_customer_id', 'filter_customer_reservations'];
    
    selectIds.forEach(selectId => {
        const selectElement = document.getElementById(selectId);
        if (selectElement) {
            if (filterSelects[selectId] && typeof filterSelects[selectId].destroy === 'function') {
                try {
                    filterSelects[selectId].destroy();
                } catch(e) {}
            }
            if (selectElement.tomselect) {
                try {
                    selectElement.tomselect.destroy();
                } catch(e) {}
            }
        }
    });

    const selectConfigs = {
        'filter_receptionist_id': { field: 'receptionist_id', placeholder: 'Buscar recepcionista...' },
        'filter_room_id': { field: 'room_id', placeholder: 'Buscar habitación...' },
        'filter_customer_id': { field: 'customer_id', placeholder: 'Buscar cliente...' },
        'filter_customer_reservations': { field: 'customer_id', placeholder: 'Buscar cliente...' }
    };
    
    Object.keys(selectConfigs).forEach(selectId => {
        const selectElement = document.getElementById(selectId);
        if (selectElement && typeof TomSelect !== 'undefined') {
            const config = selectConfigs[selectId];
            filterSelects[selectId] = new TomSelect(selectElement, {
                placeholder: config.placeholder,
                allowEmptyOption: true,
                maxItems: 1,
                plugins: ['clear_button'],
                onChange: function(value) {
                    if (window.Livewire && window.Livewire.find) {
                        const root = document.querySelector('[wire\\:id]');
                        if (root) {
                            const component = window.Livewire.find(root.getAttribute('wire:id'));
                            if (component) component.set('filters.' + config.field, value || '');
                        }
                    }
                }
            });
        }
    });
}

function updateReportsDataFromDOM() {
    const container = document.getElementById('reports-data-container');
    if (container) {
        try {
            window.reportsData = {
                reportData: JSON.parse(container.getAttribute('data-report-data') || '{}'),
                entity_type: container.getAttribute('data-entity-type') || '',
                groupBy: container.getAttribute('data-group-by') || '',
                hash: container.getAttribute('data-hash') || ''
            };
            return true;
        } catch(e) {
            console.error('Error parsing reports data from DOM:', e);
        }
    }
    return false;
}

function initializeCharts() {
    // 1. Limpiar instancias previas
    Object.values(chartInstances).forEach(chart => {
        if (chart && typeof chart.destroy === 'function') chart.destroy();
    });
    chartInstances = {};
    
    // 2. Verificar Chart.js
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está cargado.');
        return;
    }

    // 3. Obtener datos actualizados
    if (!updateReportsDataFromDOM()) return;
    
    const { reportData, entity_type, groupBy } = window.reportsData;
    const summaryData = reportData.summary || {};
    const groupedData = reportData.grouped || [];
    const detailedData = reportData.detailed_data || [];

    // Helper para verificar si un canvas existe y es visible
    const getCanvas = (id) => document.getElementById(id);

    // --- GRÁFICAS DE VENTAS ---
    if (entity_type === 'sales') {
        const paymentCtx = getCanvas('salesPaymentChart');
        if (paymentCtx && summaryData.by_payment_method) {
            const p = summaryData.by_payment_method;
            chartInstances.salesPaymentChart = new Chart(paymentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Efectivo', 'Transferencia', 'Ambos', 'Pendiente'],
                    datasets: [{
                        data: [p.efectivo || 0, p.transferencia || 0, p.ambos || 0, p.pendiente || 0],
                        backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
            });
        }

        const typeCtx = getCanvas('salesTypeChart');
        if (typeCtx) {
            chartInstances.salesTypeChart = new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Habitaciones', 'Venta Normal'],
                    datasets: [{
                        data: [summaryData.room_sales_total || 0, summaryData.individual_sales_total || 0],
                        backgroundColor: ['#3b82f6', '#10b981'],
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
            });
        }

        // Gráficas específicas cuando se agrupa por recepcionista
        const recepCtx = getCanvas('receptionistChart');
        const payCtx = getCanvas('paymentChart');
        if (recepCtx && groupedData.length > 0 && groupBy === 'receptionist') {
            chartInstances.recepChart = new Chart(recepCtx, {
                type: 'bar',
                data: {
                    labels: groupedData.map(d => d.name),
                    datasets: [{ label: 'Ventas Totales', data: groupedData.map(d => d.total_sales || 0), backgroundColor: '#8b5cf6' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }
        if (payCtx && groupedData.length > 0 && groupBy === 'receptionist') {
            chartInstances.payChart = new Chart(payCtx, {
                type: 'pie',
                data: {
                    labels: ['Efectivo', 'Transferencia'],
                    datasets: [{
                        data: [
                            groupedData.reduce((acc, d) => acc + (d.cash || 0), 0),
                            groupedData.reduce((acc, d) => acc + (d.transfer || 0), 0)
                        ],
                        backgroundColor: ['#10b981', '#3b82f6']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const barCtx = getCanvas('groupedChart-sales');
        if (barCtx) {
            let labels = [], data = [];
            if (groupBy && groupedData.length > 0) {
                labels = groupedData.map(d => d.name || d.date || 'N/D');
                data = groupedData.map(d => d.total || 0);
            } else if (detailedData.length > 0) {
                const limit = Array.isArray(detailedData) ? detailedData.slice(0, 10) : [];
                labels = limit.map(d => `Venta #${d.id}`);
                data = limit.map(d => d.total || 0);
            }
            
            if (labels.length > 0) {
                chartInstances.barSales = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ label: 'Ventas', data: data, backgroundColor: '#8b5cf6' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }
        }
    }

    // --- GRÁFICAS DE HABITACIONES ---
    if (entity_type === 'rooms') {
        const roomCtx = getCanvas('summaryPieChart');
        if (roomCtx && summaryData) {
            chartInstances.roomStatus = new Chart(roomCtx, {
                type: 'pie',
                data: {
                    labels: ['Ocupadas', 'Libres', 'Limpieza', 'Mantenimiento'],
                    datasets: [{
                        data: [summaryData.occupied_rooms || 0, summaryData.available_rooms || 0, summaryData.cleaning_rooms || 0, summaryData.maintenance_rooms || 0],
                        backgroundColor: ['#ef4444', '#10b981', '#3b82f6', '#f59e0b']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
            });
        }

        const barCtx = getCanvas('groupedChart-rooms');
        if (barCtx) {
            let labels = [], data = [];
            if (groupBy && groupedData.length > 0) {
                labels = groupedData.map(d => d.name || 'N/D');
                data = groupedData.map(d => d.count || 0);
            } else if (detailedData.length > 0) {
                const limit = Array.isArray(detailedData) ? detailedData.slice(0, 10) : [];
                labels = limit.map(d => `Hab. ${d.room_number}`);
                data = limit.map(d => d.reservations_count || 0);
            }

            if (labels.length > 0) {
                chartInstances.barRooms = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ label: 'Reservas', data: data, backgroundColor: '#3b82f6' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
        }
    }

    // --- GRÁFICAS DE RESERVACIONES ---
    if (entity_type === 'reservations') {
        const resCtx = getCanvas('pieChart-reservations');
        if (resCtx && summaryData) {
            chartInstances.resStatus = new Chart(resCtx, {
                type: 'pie',
                data: {
                    labels: ['Pagado', 'Pendiente'],
                    datasets: [{
                        data: [summaryData.total_deposit || 0, summaryData.total_pending || 0],
                        backgroundColor: ['#10b981', '#ef4444']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
            });
        }

        const barCtx = getCanvas('groupedChart-reservations');
        if (barCtx) {
            let labels = [], data = [];
            if (groupBy && groupedData.length > 0) {
                labels = groupedData.map(d => d.name || d.date || 'N/D');
                data = groupedData.map(d => d.count || 0);
            } else if (detailedData.length > 0) {
                const limit = Array.isArray(detailedData) ? detailedData.slice(0, 10) : [];
                labels = limit.map(d => d.customer_name);
                data = limit.map(d => d.total_amount || 0);
            }

            if (labels.length > 0) {
                chartInstances.barRes = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ label: 'Reservas', data: data, backgroundColor: '#8b5cf6' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
        }
    }

    // --- GRÁFICAS DE LIMPIEZA ---
    if (entity_type === 'cleaning') {
        const cleanCtx = getCanvas('cleaningStatusChart');
        if (cleanCtx && summaryData) {
            chartInstances.cleaningStatus = new Chart(cleanCtx, {
                type: 'doughnut',
                data: {
                    labels: ['En Limpieza', 'Sucia'],
                    datasets: [{
                        data: [summaryData.limpieza || 0, summaryData.sucia || 0],
                        backgroundColor: ['#3b82f6', '#6b7280']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const barCtx = getCanvas('groupedChart-cleaning');
        if (barCtx && detailedData.length > 0) {
            chartInstances.barCleaning = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: detailedData.slice(0, 10).map(d => `Hab. ${d.room_number}`),
                    datasets: [{ label: 'Estado', data: detailedData.slice(0, 10).map(d => 1), backgroundColor: '#3b82f6' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }
    }

    // --- GRÁFICAS DE RECEPCIONISTAS ---
    if (entity_type === 'receptionists') {
        const barCtx = getCanvas('groupedChart-receptionists');
        if (barCtx) {
            let labels = [], data = [];
            if (detailedData.length > 0) {
                const limit = Array.isArray(detailedData) ? detailedData.slice(0, 10) : [];
                labels = limit.map(d => d.name);
                data = limit.map(d => d.total_sales || 0);
            }

            if (labels.length > 0) {
                chartInstances.barReceptionists = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ label: 'Ventas Totales', data: data, backgroundColor: '#8b5cf6' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
        }
    }

    // --- GRÁFICAS DE CLIENTES ---
    if (entity_type === 'customers') {
        const barCtx = getCanvas('groupedChart-customers');
        if (barCtx) {
            let labels = [], data = [];
            if (detailedData.length > 0) {
                const limit = Array.isArray(detailedData) ? detailedData.slice(0, 10) : [];
                labels = limit.map(d => d.name || 'N/D');
                data = limit.map(d => d.total || d.reservations_count || 0);
            }

            if (labels.length > 0) {
                chartInstances.barCustomers = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ 
                            label: 'Inversión Total', 
                            data: data, 
                            backgroundColor: '#8b5cf6',
                            borderRadius: 8
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' Total: $' + new Intl.NumberFormat('es-CO').format(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + new Intl.NumberFormat('es-CO', { notation: 'compact' }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }

    // --- GRÁFICAS DE PRODUCTOS ---
    if (entity_type === 'products') {
        const barCtx = getCanvas('groupedChart-products');
        if (barCtx) {
            let labels = [], data = [];
            if (detailedData.length > 0) {
                const limit = Array.isArray(detailedData) ? detailedData.slice(0, 10) : [];
                labels = limit.map(d => d.name);
                data = limit.map(d => d.value_total || 0);
            }

            if (labels.length > 0) {
                chartInstances.barProducts = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ label: 'Valor Total', data: data, backgroundColor: '#10b981' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
        }
    }

    // --- GRÁFICAS DE FACTURAS ELECTRÓNICAS ---
    if (entity_type === 'electronic_invoices') {
        const barCtx = getCanvas('groupedChart-electronic_invoices');
        if (barCtx) {
            let labels = [], data = [];
            if (detailedData.length > 0) {
                const limit = Array.isArray(detailedData) ? detailedData.slice(0, 10) : [];
                labels = limit.map(d => d.customer_name);
                data = limit.map(d => d.total || 0);
            }

            if (labels.length > 0) {
                chartInstances.barInvoices = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{ label: 'Total Facturado', data: data, backgroundColor: '#8b5cf6' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }
        }
    }
}

function getDataHash(data) {
    if (!data) return '';
    const h = typeof data.hash === 'string' ? data.hash : '';
    return h;
}

// Sistema de reintentos para asegurar que las gráficas se dibujen
function forceInitialize() {
    let attempts = 0;
    const interval = setInterval(() => {
        attempts++;
        initializeCharts();
        // Si detectamos que las gráficas ya tienen instancia de Chart.js, paramos
        if (Object.keys(chartInstances).length > 0 || attempts > 10) {
            clearInterval(interval);
        }
    }, 150);
}

function debouncedReinit() {
    if (reinitTimeout) clearTimeout(reinitTimeout);
    reinitTimeout = setTimeout(() => {
        updateReportsDataFromDOM();
        const currentData = window.reportsData || {};
        const currentHash = getDataHash(currentData);
        
        // Si el hash cambia o el tipo de entidad cambia, reinicializamos
        if (currentHash !== lastDataHash) {
            forceInitialize();
            initializeFilterSelects();
            lastDataHash = currentHash;
        }
    }, 50);
}

// Escuchar cambios de Livewire
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', (...args) => {
        // Livewire v3 hook signatures can vary between releases/builds:
        // - (el, component)
        // - ({ el, component })
        const maybePayload = args[0];
        const component = args[1] ?? (maybePayload && typeof maybePayload === 'object' ? maybePayload.component : undefined);
        const componentName = component && typeof component.name === 'string' ? component.name : null;

        if (componentName !== 'reports-manager') {
            return;
        }

        debouncedReinit();
    });

    Livewire.on('report-refreshed', () => debouncedReinit());
});

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', () => {
    updateReportsDataFromDOM();
    forceInitialize();
    initializeFilterSelects();
});

// Para navegación SPA
document.addEventListener('livewire:navigated', () => {
    lastDataHash = null;
    updateReportsDataFromDOM();
    forceInitialize();
    initializeFilterSelects();
});
