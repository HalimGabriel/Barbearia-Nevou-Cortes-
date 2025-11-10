const API_URL = 'actions/get_barbeiro_agendamentos.php';
const UPDATE_STATUS_API = 'actions/update_agendamento_status.php'; 
const agendaContainer = document.getElementById('agenda-container');
const barberSelect = document.getElementById('barber_select');
let BARBER_ID = null;

async function confirmarOuCancelarAgendamento(agendamentoId, novoStatus) {
    const actionName = novoStatus === 'concluido' ? 'CONCLUIR' : 'CANCELAR';
    if (!confirm(`[ATENÇÃO] Tem certeza que deseja ${actionName.toLowerCase()} o agendamento ${agendamentoId}?`)) return;

    try {
        const response = await fetch(UPDATE_STATUS_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: agendamentoId, status: novoStatus }),
        });
        const data = await response.json();
        if (!response.ok || data.error) {
            alert(`Falha ao ${actionName.toLowerCase()} agendamento: ` + (data.error || 'Erro de rede.'));
        } else {
            alert(`Sucesso! Agendamento ${agendamentoId} atualizado para ${novoStatus.toUpperCase()}!`); 
            fetchAppointments(BARBER_ID);
        }
    } catch (error) {
        alert('Erro de conexão ao tentar atualizar o agendamento.');
    }
}

function attachActionListeners() {
    agendaContainer.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', e => {
            const id = e.currentTarget.dataset.id;
            const status = e.currentTarget.dataset.status; 
            confirmarOuCancelarAgendamento(id, status);
        });
    });
}

function getButtonHtml(id, currentStatus) {
    if (currentStatus === 'agendado') {
        return `
            <button class="action-btn text-sm bg-green-500 text-white px-3 py-1.5 rounded-full hover:bg-green-600 transition duration-150 shadow-md mr-2" data-id="${id}" data-status="concluido">
                <span data-lucide="check" class="w-4 h-4 inline-block align-middle"></span>
                Confirmar
            </button>
            <button class="action-btn text-sm bg-red-500 text-white px-3 py-1.5 rounded-full hover:bg-red-600 transition duration-150 shadow-md" data-id="${id}" data-status="cancelado">
                <span data-lucide="x" class="w-4 h-4 inline-block align-middle"></span>
                Cancelar
            </button>
        `;
    } else if (currentStatus === 'concluido') {
        return `<span class="text-sm font-semibold text-green-600 border border-green-300 px-3 py-1 rounded-full bg-green-50">CONCLUÍDO</span>`;
    } else if (currentStatus === 'cancelado') {
        return `<span class="text-sm font-semibold text-red-600 border border-red-300 px-3 py-1 rounded-full bg-red-50">CANCELADO</span>`;
    }
    return '';
}

function formatAppointmentCard(item) {
    const cardClass = item.status === 'concluido' ? 'border-green-600' : item.status === 'cancelado' ? 'border-red-600' : 'border-indigo-600';
    return `
        <div class="bg-white p-4 sm:p-6 rounded-xl agenda-card border-l-4 ${cardClass} flex flex-col sm:flex-row justify-between items-start sm:items-center transition duration-200 hover:shadow-lg">
            <div class="flex items-center space-x-4 mb-3 sm:mb-0 sm:w-1/4">
                <span data-lucide="calendar" class="text-indigo-600 w-6 h-6"></span>
                <div>
                    <p class="text-lg font-bold text-gray-800">${item.hora_formatada}</p>
                    <p class="text-sm text-gray-500">${item.data_formatada}</p>
                </div>
            </div>
            <div class="sm:w-2/4">
                <p class="text-base font-semibold text-gray-700 flex items-center">
                    <span data-lucide="user" class="w-5 h-5 mr-2 text-indigo-400"></span>
                    ${item.nome_cliente}
                </p>
                <p class="text-sm text-gray-600 ml-7 mt-0.5">${item.nome_servico} - Status: ${item.status.toUpperCase()} - Tel: ${item.telefone || 'N/A'}</p>
            </div>
            <div class="w-full sm:w-1/4 flex justify-end mt-3 sm:mt-0">
                ${getButtonHtml(item.id, item.status)}
            </div>
        </div>
    `;
}

function renderAppointments(appointments) { 
    if (!agendaContainer) return;
    if (appointments.length === 0) {
        agendaContainer.innerHTML = `
            <div class="text-center bg-gray-50 p-6 rounded-xl border border-gray-200 mt-8">
                <span data-lucide="inbox" class="w-8 h-8 text-gray-400 mx-auto block mb-2"></span>
                <p class="text-lg font-semibold text-gray-700">Nenhum Agendamento Encontrado</p>
            </div>
        `;
    } else {
        agendaContainer.innerHTML = appointments.map(formatAppointmentCard).join('');
        attachActionListeners();
    }
    lucide.createIcons(); 
}

async function fetchAppointments(id) {
    if (!id || !agendaContainer) return;
    agendaContainer.innerHTML = '<p class="text-center text-indigo-500 font-semibold mt-8">Carregando agendamentos...</p>';
    
    try {
        const response = await fetch(`${API_URL}?id_barbeiro=${id}&_t=${Date.now()}`);
        const text = await response.text();
        const data = JSON.parse(text);
        renderAppointments(data);
    } catch (error) {
        agendaContainer.innerHTML = `
            <div class="text-center bg-red-50 p-6 rounded-xl border border-red-200">
                <p class="text-lg font-semibold text-red-800">Erro ao carregar agenda</p>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons(); 
    const is_admin = CURRENT_USER_ROLE === 'admin';
    if (is_admin && barberSelect) {
        BARBER_ID = barberSelect.value;
        barberSelect.addEventListener('change', e => {
            BARBER_ID = e.target.value;
            fetchAppointments(BARBER_ID);
        });
    } else if (CURRENT_USER_ID) {
        BARBER_ID = CURRENT_USER_ID;
    } else {
        return;
    }
    fetchAppointments(BARBER_ID);
});
