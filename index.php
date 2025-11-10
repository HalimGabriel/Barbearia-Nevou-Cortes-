<?php
require_once 'config/conexao.php';
require_once 'inc/functions.php';

$servicos = [];
$barbeiros = [];

try {
    $pdo = get_db_connection();
    
    $stmt_servicos = $pdo->query('SELECT * FROM servicos');
    $servicos = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_barbeiros = $pdo->query('SELECT id_barbeiro, nome FROM barbeiros WHERE role = "barbeiro"');
    $barbeiros = $stmt_barbeiros->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['flash'] = "❌ Erro ao carregar dados do sistema. Tente novamente mais tarde.";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nevou Cortes - Agendamento</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/index.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
</head>
<body>

<div id="loader">
    <img src="img/logo.png" alt="Logo Nevou Cortes" class="loader-logo">
    <p>Carregando...</p>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
<div id="toastNotification" class="toast-notification">
    <p><?php echo esc(strip_tags($_SESSION['flash'])); ?></p>
</div>
<?php unset($_SESSION['flash']); endif; ?>

<header class="header">
    <img src="img/logo.png" alt="Nevou Cortes" class="logo">
    <nav>
        <ul class="nav-links">
            <li><a href="#servicos">Serviços</a></li>
            <li><a href="#barbearias">Localização</a></li>
            <li><a href="#agendar">Agendar</a></li>
            
            <?php if (!is_logged()): ?>
                <li><a href="login.php" class="btn">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<section class="hero">
    <h1>A barbearia para quem dita o próprio ritmo</h1>
    <p>Transformamos minutos de cuidado em horas de confiança, estilo e presença.</p>
    <a href="#agendar" class="btn">Agende seu Horário</a>
</section>

<section class="sobre">
    <h2>Por que a Nevou Cortes?</h2>
    <p>Mais do que estética, criamos experiências que fortalecem a atitude de homens que lideram e inspiram.</p>
    <ul class="benefits">
        <li>✔ Confiança visível</li>
        <li>✔ Tempo otimizado</li>
        <li>✔ Sucesso impulsionado</li>
        <li>✔ Ambientes premium</li>
    </ul>
</section>

<section id="barbearias" class="barbearias">
    <h2>Nossa Localização</h2>

    <?php
    $barbearias = [
        ['img'=>'img/salao1.png','nome'=>'Barbearia Nevou Cortes','local'=>'Mooca, São Paulo','avaliacao'=>'4.8','contato'=>['4002-8922'],'servicos'=>['Corte Adulto - R$ 45','Corte Infantil - R$ 35','Barba - R$ 20']],
    ];

    foreach($barbearias as $b):
    ?>
    <div class="card-horizontal">
        <img src="<?php echo $b['img']; ?>" alt="<?php echo $b['nome']; ?>" onerror="this.onerror=null; this.src='https://placehold.co/400x250/000/fff?text=Local+Nevou+Cortes';">
        <div class="info">
            <h3><?php echo $b['nome']; ?></h3>
            <p>📍 <?php echo $b['local']; ?></p>
            <p>⭐ <?php echo $b['avaliacao']; ?> (<?php echo rand(50,150); ?> avaliações)</p>
            <div class="servicos">
                <?php foreach($b['servicos'] as $s): ?>
                    <div><?php echo $s; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<section id="servicos">
    <h2>Serviços</h2>
    <div class="servico-container">
        <?php if (!empty($servicos)): ?>
            <?php foreach($servicos as $s): ?>
                <div class="servico">
                    <strong><?php echo esc($s['nome_servico']); ?></strong>
                    <p>Duração: <?php echo esc($s['duracao_minutos']); ?> min</p>
                    <p>Preço: <span>R$ <?php echo number_format($s['preco'],2,',','.'); ?></span></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum serviço cadastrado.</p>
        <?php endif; ?>
    </div>
</section>

<section id="agendar" class="flex flex-col items-center p-4">
    <h2>Agendar Horário</h2>

    <?php if (is_logged()): ?>
    <form action="actions/agendar.php" method="post" class="form-agendamento w-full max-w-xl bg-white p-6 rounded-xl shadow-xl">
        
        <div class="form-card mb-6">
            <label class="block text-gray-700 font-bold mb-2">Barbeiro:</label>
            <select name="id_barbeiro" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <?php foreach($barbeiros as $b): ?>
                    <option value="<?php echo esc($b['id_barbeiro']); ?>"><?php echo esc($b['nome']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-card mb-6">
            <label class="block text-gray-700 font-bold mb-2">Serviço:</label>
            <select name="id_servico" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <?php foreach($servicos as $s): ?>
                    <option value="<?php echo esc($s['id_servico']); ?>"><?php echo esc($s['nome_servico']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-card mb-6">
            <label class="block text-gray-700 font-bold mb-3">1. Selecione o Dia</label>
            <div id="day-selection" class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            </div>
        </div>

        <div class="form-card mb-8">
            <label class="block text-gray-700 font-bold mb-3">2. Selecione o Horário</label>
            <div id="time-selection" class="grid grid-cols-4 sm:grid-cols-5 gap-2">
            </div>
        </div>

        <input type="hidden" name="data_hora" id="final_datetime_input" required>

        <button type="submit" id="submit_button" class="btn-agendar w-full py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:bg-indigo-700 transition duration-200" disabled>
            Selecione Dia e Horário
        </button>
    </form>

    <p class="mt-4"><a href="actions/logout.php">Sair da sessão</a></p>
    <?php else: ?>
        <p>Por favor <a href="login.php"><b>faça login</b></a> para agendar.</p>
    <?php endif; ?>
</section>

<footer class="footer">
    <p>&copy; 2025 Nevou Cortes. Todos os direitos reservados.</p>
</footer>


<script src="js/script.js"></script>
<script src="js/notificacao.js"></script> 
<script src="js/agendamentohorario.js"></script>
<script>
    window.addEventListener("load", () => {
        document.getElementById("loader").style.display = "none";
    });
</script>
</body>
</html>