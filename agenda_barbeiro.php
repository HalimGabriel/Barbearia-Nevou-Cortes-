<?php

require_once 'config/conexao.php';
require_once 'inc/functions.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
 
    header('Location: login.php'); 
    exit;
}

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];
$is_admin = ($current_user_role === 'admin');


$servicos = [];
$barbeiros = [];

try {
    $pdo = get_db_connection();
    
    
    $stmt_servicos = $pdo->query('SELECT * FROM servicos');
    $servicos = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

    
    if ($is_admin) {
        
        $sql_barbeiros = 'SELECT id_barbeiro, nome FROM barbeiros WHERE role = "barbeiro" ORDER BY nome ASC';
        $stmt_barbeiros = $pdo->query($sql_barbeiros);
        $barbeiros = $stmt_barbeiros->fetchAll(PDO::FETCH_ASSOC);
    } else {
        
        $sql_barbeiro_proprio = 'SELECT id_barbeiro, nome FROM barbeiros WHERE id_barbeiro = :id LIMIT 1';
        $stmt_barbeiro_proprio = $pdo->prepare($sql_barbeiro_proprio);
        $stmt_barbeiro_proprio->execute(['id' => $current_user_id]);
        $barbeiros = $stmt_barbeiro_proprio->fetchAll(PDO::FETCH_ASSOC);

        
        if (empty($barbeiros)) {
             $_SESSION['flash'] = "⚠️ Seu perfil de barbeiro não foi encontrado.";
             
        }
    }

} catch (PDOException $e) {
   
    $_SESSION['flash'] = "❌ Erro ao carregar dados do sistema. Tente novamente mais tarde.";
    
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda do Barbeiro</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
        .agenda-card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <div class="max-w-4xl mx-auto">
        
        
        <header class="mb-8 flex flex-col sm:flex-row justify-between items-center sm:items-start">
            
          
            <div class="flex-grow text-center sm:text-left mb-4 sm:mb-0">
                <h1 class="text-4xl font-extrabold text-indigo-800">
                    <?php echo $is_admin ? 'Todas as Agendas' : 'Minha Agenda'; ?>
                </h1>
                <p class="text-gray-500 mt-2">Seus próximos agendamentos.</p>
            </div>

      
            <a href="actions/logout.php" 
               class="flex items-center space-x-2 px-4 py-2 bg-red-600 text-white font-semibold rounded-full shadow-lg hover:bg-red-700 transition duration-150 transform hover:scale-105 whitespace-nowrap">
                <span data-lucide="log-out" class="w-5 h-5"></span>
                <span>Sair</span>
            </a>
        </header>
        
  
        <?php if ($is_admin): ?>
            <div class="mb-6 bg-white p-4 rounded-xl agenda-card border border-indigo-100">
                <label for="barber_select" class="block text-sm font-medium text-gray-700 mb-2">Selecione o Barbeiro</label>
                <select id="barber_select" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <?php foreach($barbeiros as $b): ?>
                        <option value="<?php echo esc($b['id_barbeiro']); ?>"><?php echo esc($b['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-6" role="alert">
                <strong class="font-bold">Atenção!</strong>
                <span class="block sm:inline"><?php echo $_SESSION['flash']; ?></span>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div id="agenda-container" class="space-y-4">
 
            <p id="loading-message" class="text-center text-gray-500">Carregando agendamentos...</p>
        </div>
    </div>

 
    <script>

        const CURRENT_USER_ID = <?php echo json_encode($current_user_id); ?>;
        const CURRENT_USER_ROLE = <?php echo json_encode($current_user_role); ?>;
    </script>


    <script src="js/agenda_barbeiro.js"></script>
    <script>
  
        lucide.createIcons();
    </script>

</body>
</html>
