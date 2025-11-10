<?php

require_once 'inc/functions.php';


if (is_logged()) {
    header('Location: index.php'); 
    exit();
}


$active_panel = isset($_GET['action']) && $_GET['action'] == 'register' ? 'right-panel-active' : '';


$flash_message = esc($_SESSION['flash'] ?? '');

unset($_SESSION['flash']);

if (stripos($flash_message, 'cadastro') !== false) {
    $active_panel = 'right-panel-active';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nevou cortes | Login e Cadastro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/login.css">
</head>
<body>


<?php if (!empty($flash_message)): 
   
    $type_class = stripos($flash_message, 'sucesso') !== false ? 'toast-success' : 'toast-error';
?>

<div id="toastNotification" class="toast-fixed <?php echo $type_class; ?>">
    <i class="fas fa-<?php echo stripos($flash_message, 'sucesso') !== false ? 'check-circle' : 'exclamation-triangle'; ?> toast-icon"></i>
    <p><?php echo $flash_message; ?></p>
</div>
<?php endif; ?>

<div class="container <?php echo $active_panel; ?>" id="container">
    
    
    <div class="form-container sign-up-container">
  
        <form action="actions/cadastrar_cliente.php" method="POST">
            <h1>Criar Conta</h1>
            <div class="social-container">

                <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>

            </div>
            <span>ou use seu email para cadastro</span>
    
            <input type="text" name="nome" placeholder="Nome completo" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="tel" name="telefone" placeholder="Telefone para contato" required />
            <input type="password" name="senha" placeholder="Senha" required />
  

            <button type="submit">Cadastrar</button>
        </form>
    </div>

   
    <div class="form-container sign-in-container">
      
        <form action="actions/login_cliente.php" method="POST">
            <h1>Entrar</h1>
            <div class="social-container">
            
                <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>

            </div>
            <span>ou use sua conta</span>
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="senha" placeholder="Senha" required />
            <a href="#">Esqueceu sua senha?</a>
            <button type="submit">Entrar</button>
        </form>
    </div>

    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Bem vindo de volta!</h1>
                <p>Para se manter conectado conosco, faça login com suas informações pessoais.</p>
                <button class="ghost" id="signIn">Entrar</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Olá, amigo!</h1>
                <p>Insira seus dados pessoais e comece sua jornada conosco.</p>
                <button class="ghost" id="signUp">Cadastrar</button>
            </div>
        </div>
    </div>
</div>

<footer>
    <p><a href="index.php">Voltar para a home</a></p>
</footer>


<script>
   
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });

    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });
</script>


<?php if (!empty($flash_message)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toastNotification');

    if (toast) {
        const hideTime = 3000; 
        const transitionDuration = 500; 

        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10); 

      
        setTimeout(() => {
         
            toast.classList.remove('show');

         
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, transitionDuration);

        }, hideTime);
    }
});
</script>
<?php endif; ?>



