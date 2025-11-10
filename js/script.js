document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const tipo = document.getElementById("tipo").value;

    if (tipo === "cliente") {
        window.location.href = "cliente.html";
    } else if (tipo === "barbeiro") {
        window.location.href = "admin.html";
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const whatsappButton = document.querySelector('.btn-whatsapp');
  
    whatsappButton.addEventListener('click', (e) => {
      e.preventDefault();
      window.open('https://api.whatsapp.com/send?phone=5511999999999&text=Quero%20agendar%20um%20horário', '_blank');
    });
  });
  

  //-- js da pagina de loguin // -- 


  const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
	container.classList.add("right-panel-active");
});

signInButton.addEventListener('click', () => {
	container.classList.remove("right-panel-active");
});



/* modal */ 

const modal = document.getElementById("modalAgendamento");
  const card = document.getElementById("cardBarbearia");

  card.addEventListener("click", () => {
    modal.style.display = "flex";
  });

  function fecharModal() {
    modal.style.display = "none";
  }

  // Fecha o modal ao clicar fora dele
  window.onclick = function(event) {
    if (event.target === modal) {
      fecharModal();
    }
  };

  const dataInput = document.getElementById("data_hora");

// Limitar hora
const horaInicio = 7; // 7h
const horaFim = 18;   // 18h

// ajustar min e max para hoje
function ajustarLimites() {
    const now = new Date();
    const ano = now.getFullYear();
    const mes = String(now.getMonth() + 1).padStart(2, '0');
    const dia = String(now.getDate()).padStart(2, '0');
    
    dataInput.min = `${ano}-${mes}-${dia}T${horaInicio.toString().padStart(2,'0')}:00`;
    dataInput.max = `${ano}-${mes}-${dia}T${horaFim.toString().padStart(2,'0')}:00`;
}

// verificar dias válidos (terça=2, ... sábado=6)
function validarDia() {
    const selected = new Date(this.value);
    const day = selected.getDay();
    
    if (day < 2 || day > 6) { 
        this.value = "";
        alert("Agendamentos permitidos apenas de terça a sábado!");
    }
}

// limitar hora
function validarHora() {
    const selected = new Date(this.value);
    const h = selected.getHours();
    if (h < horaInicio || h > horaFim) {
        this.value = "";
        alert(`Horário permitido: ${horaInicio}:00 às ${horaFim}:00`);
    }
}

dataInput.addEventListener("input", validarDia);
dataInput.addEventListener("input", validarHora);

ajustarLimites();
