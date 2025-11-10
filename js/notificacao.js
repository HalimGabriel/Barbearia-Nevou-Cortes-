// Aguarda o carregamento completo do DOM
document.addEventListener('DOMContentLoaded', function() {
    // 1. Obtém o elemento de notificação pelo ID
    const toast = document.getElementById('toastNotification');
    
    // Verifica se o elemento existe (só existe se o PHP setou uma mensagem flash)
    if (toast) {
        // Tempo que a notificação ficará visível (3000ms = 3 segundos)
        const displayTime = 3000; 
        // Tempo da transição CSS para fade out (deve ser igual ao CSS: 500ms)
        const transitionTime = 500; 

        // 2. Exibe o toast: Adiciona a classe 'show' para iniciar o movimento/opacidade
        // O card é inicialmente: opacity: 0; transform: translateX(100%);
        toast.classList.add('show');

        // 3. Define o temporizador (3000ms) para esconder
        setTimeout(() => {
            // Remove a classe 'show' para iniciar a transição de saída (desaparecer)
            toast.classList.remove('show');
            
            // 4. Remove o elemento totalmente do DOM após a transição de saída terminar (500ms)
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, transitionTime); 
            
        }, displayTime);
    }
});