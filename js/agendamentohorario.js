const DAYS_DATA = [
    { id: 0, label: 'Hoje', date: new Date(Date.now()).toISOString().split('T')[0] },
    { id: 1, label: 'Amanhã', date: new Date(Date.now() + 86400000).toISOString().split('T')[0] },
    { id: 2, label: 'Dia 3', date: new Date(Date.now() + 2 * 86400000).toISOString().split('T')[0] },
    { id: 3, label: 'Dia 4', date: new Date(Date.now() + 3 * 86400000).toISOString().split('T')[0] },
    { id: 4, label: 'Dia 5', date: new Date(Date.now() + 4 * 86400000).toISOString().split('T')[0] },
    { id: 5, label: 'Dia 6', date: new Date(Date.now() + 5 * 86400000).toISOString().split('T')[0] },
];

const TIMES_DATA = [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    '18:00'
];

const API_URL = 'actions/get_agendamentos.php';
let occupiedTimes = [];
let selectedDayDate = null;
let selectedTime = null;

const daySelectionEl = document.getElementById('day-selection');
const timeSelectionEl = document.getElementById('time-selection');
const finalDatetimeInput = document.getElementById('final_datetime_input');

const selectBarbeiro = document.querySelector('select[name="id_barbeiro"]');
const submitButton = document.getElementById('submit_button');

const getCurrentTimeStr = () => {
    const now = new Date();
    return now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit', 
        hour12: false 
    });
};

const TODAY_DATE_STR = DAYS_DATA[0].date;

async function fetchAndRenderSlots() {
    const idBarbeiro = selectBarbeiro ? selectBarbeiro.value : null;

    if (!idBarbeiro || !selectedDayDate) {
        occupiedTimes = [];
        renderTimes();
        return;
    }

    try {
        timeSelectionEl.innerHTML = '<p class="col-span-full text-center text-indigo-500 font-semibold text-sm mt-4">Carregando disponibilidade...</p>';

        const response = await fetch(`${API_URL}?id_barbeiro=${idBarbeiro}&data=${selectedDayDate}`);

        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        occupiedTimes = data;
        console.log(`Horários ocupados para ${selectedDayDate}:`, occupiedTimes);

    } catch (error) {
        console.error('Falha ao buscar horários:', error);
        occupiedTimes = [];
        timeSelectionEl.innerHTML = '<p class="col-span-full text-center text-red-500 text-sm mt-4">Erro ao carregar horários. Tente novamente.</p>';
    }

    renderTimes();
}

function renderDays() {
    daySelectionEl.innerHTML = DAYS_DATA.map(day => {
        const isSelected = selectedDayDate === day.date;
        const uniqueId = `day_${day.id}`;
        const dateObj = new Date(day.date + 'T00:00:00');
        const dayOfMonth = dateObj.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' }).replace('.', '');
        
        const selectedClass = isSelected ? 'bg-indigo-600 text-white shadow-lg' : 'hover:bg-gray-100';

        return `
            <div class="slot-container">
                <input type="radio" id="${uniqueId}" name="selected_day" value="${day.date}" ${isSelected ? 'checked' : ''} class="hidden">
                <label for="${uniqueId}" class="slot-label text-sm p-3 border border-gray-300 rounded-lg text-center font-semibold text-gray-600 transition duration-150 cursor-pointer ${selectedClass}" onclick="handleDaySelection('${day.date}')">
                    <strong>${day.label}</strong><br>
                    <span class="text-xs font-normal">${dayOfMonth}</span>
                </label>
            </div>
        `;
    }).join('');
}

function renderTimes() {
    if (!selectedDayDate) {
        timeSelectionEl.innerHTML = '<p class="col-span-full text-center text-gray-500 text-sm mt-2">Selecione um dia e um barbeiro primeiro.</p>';
        return;
    }
    
    if (!selectBarbeiro || !selectBarbeiro.value) {
           timeSelectionEl.innerHTML = '<p class="col-span-full text-center text-red-500 text-sm mt-2">Selecione um Barbeiro para ver os horários.</p>';
           return;
    }

    const isToday = selectedDayDate === TODAY_DATE_STR;
    const currentTime = isToday ? getCurrentTimeStr() : '';

    timeSelectionEl.innerHTML = TIMES_DATA.map(time => {
        const timeOnly = time; 
        
        let isOccupied = occupiedTimes.includes(timeOnly);
        let isPastSlot = false;

        if (isToday) {
             if (timeOnly < currentTime) {
                isPastSlot = true;
                isOccupied = true; 
             }
        }

        const isSelected = selectedTime === time && !isOccupied;
        const uniqueId = `time_${time.replace(':', '')}`;
        
        const disabledAttr = isOccupied ? 'disabled' : '';
        const defaultClasses = "slot-label p-2 border rounded-lg text-center font-semibold text-sm transition duration-150";
        
        let customClasses = '';
        let action = '';

        if (isOccupied) {
            if (isPastSlot) {
                customClasses = 'bg-gray-100 text-gray-400 border-gray-300 opacity-70 cursor-not-allowed line-through';
            } else {
                customClasses = 'bg-red-100 text-red-500 border-red-300 opacity-90 cursor-not-allowed';
            }
            action = '';
            
            if (isPastSlot && isSelected) selectedTime = null;

        } else if (isSelected) {
            customClasses = 'bg-indigo-600 text-white border-indigo-700 shadow-lg';
            action = `onclick="handleTimeSelection('${time}')"`;
        } else {
            customClasses = 'bg-white text-gray-600 border-gray-300 hover:bg-indigo-50';
            action = `onclick="handleTimeSelection('${time}')"`;
        }
        
        return `
            <div class="slot-container">
                <input type="radio" id="${uniqueId}" name="selected_time" value="${time}" ${isSelected ? 'checked' : ''} ${disabledAttr} class="hidden">
                <label for="${uniqueId}" class="${defaultClasses} ${customClasses}" ${action}>
                    ${time}
                </label>
            </div>
        `;
    }).join('');
    
    updateFormState();
}

function handleDaySelection(date) {
    selectedDayDate = date;
    selectedTime = null;
    
    renderDays(); 

    fetchAndRenderSlots();
}

function handleTimeSelection(time) {
    selectedTime = time;

    renderTimes();

    updateFormState();
}

function updateFormState() {
    const isValid = selectedDayDate && selectedTime;
    
    if (isValid) {
        const finalValue = `${selectedDayDate}T${selectedTime}:00`;
        finalDatetimeInput.value = finalValue;
        
        const dayLabel = DAYS_DATA.find(d => d.date === selectedDayDate)?.label || selectedDayDate;
        
        submitButton.disabled = false;
        submitButton.textContent = `Agendar para ${dayLabel} às ${selectedTime}`;
        submitButton.classList.remove('bg-indigo-600/50', 'cursor-not-allowed');
        submitButton.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    } else {
        finalDatetimeInput.value = '';
        
        submitButton.disabled = true;
        submitButton.textContent = 'Selecione Dia e Horário';
        submitButton.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        submitButton.classList.add('bg-indigo-600/50', 'cursor-not-allowed');
    }
}

window.handleDaySelection = handleDaySelection;
window.handleTimeSelection = handleTimeSelection;

document.addEventListener('DOMContentLoaded', () => {
    if (selectBarbeiro) {
        selectBarbeiro.addEventListener('change', () => {
            selectedTime = null; 
            fetchAndRenderSlots(); 
        });
    }

    selectedDayDate = DAYS_DATA[0].date;
    
    renderDays();

    if (selectBarbeiro && selectBarbeiro.value) {
        fetchAndRenderSlots();
    } else {
        renderTimes();
    }
});