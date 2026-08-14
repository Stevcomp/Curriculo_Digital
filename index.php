<?php
require_once 'crud.php';

// Processamento do Form inicial (trabalhado somente em POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'criar_pessoa') {

    $dados = [
        'nome' => trim($_POST['nome']),
        'cargo' => trim($_POST['cargo']),
        'resumo' => trim($_POST['resumo']),
        'cidade' => trim($_POST['cidade']),
    ];

    if ($dados['nome'] === '' || $dados['cargo'] === '') {
        die('Favor inserir Nome e Cargo para prosseguir com o currículo.');
    }

    create($pdo, 'dados_pessoais', $dados);

    header('Location: index.php');
    exit;
}

// Busca os dados no banco
$pessoa = read($pdo, 'dados_pessoais', 'id = ?', [1]);

// Só busca os relacionados se já existir uma pessoa cadastrada
$contatos = $pessoa ? readAll($pdo, 'contatos', 'dados_pessoais_id = ?', [$pessoa['id']]) : [];
$experiencias = $pessoa ? readAll($pdo, 'experiencias', 'dados_pessoais_id = ?', [$pessoa['id']]) : [];
$formacoes = $pessoa ? readAll($pdo, 'formacao', 'dados_pessoais_id = ?', [$pessoa['id']]) : [];
$certificados = $pessoa ? readAll($pdo, 'certificados', 'dados_pessoais_id = ?', [$pessoa['id']]) : [];

// Mapeamento de ícones para tipos de contato
function getIconeContato($tipo) {
    switch (strtolower($tipo)) {
        case 'email':
            return 'fas fa-envelope';
        case 'telefone':
            return 'fas fa-phone';
        case 'linkedin':
            return 'fab fa-linkedin';
        case 'github':
            return 'fab fa-github';
        case 'site':
            return 'fas fa-globe';
        default:
            return 'fas fa-link';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currículo — <?= $pessoa ? htmlspecialchars($pessoa['nome']) : 'Cadastro Inicial' ?></title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
    <?php if (!$pessoa): ?>
        <link rel="stylesheet" href="style1.css">
    <?php endif; ?>
</head>

<body>

    <?php if (!$pessoa): ?>

        <!-- Form inicial quando ainda não há dados -->
        <div class="admin-container">
            <section class="card">
                <h1>Transforme suas experiências em um currículo digital de respeito!</h1>
                <p>Preencha os dados abaixo para iniciar. Depois você poderá gerenciar todas as suas informações no <a href="admin.php">painel administrativo</a>.</p>

                <form method="POST">
                    <input type="hidden" name="action" value="criar_pessoa">

                    <label>Nome Completo</label>
                    <input type="text" name="nome" placeholder="Ex: Maria Oliveira" required>

                    <label>Cargo / Título Profissional</label>
                    <input type="text" name="cargo" placeholder="Ex: Desenvolvedora Full Stack" required>

                    <label>Resumo Profissional</label>
                    <textarea name="resumo" rows="4" placeholder="Fale um pouco sobre você..." maxlength="500"></textarea>

                    <label>Cidade / Estado</label>
                    <input type="text" name="cidade" placeholder="Ex: São Paulo, SP">

                    <button type="submit" class="btn-primary">Criar currículo</button>
                </form>
            </section>
        </div>

    <?php else: ?>

        <!-- Barra de Ações Superior (Exportar PDF, Anexo de Arquivo, Tema e Admin) -->
        <div class="top-bar">
            <div class="top-bar-inner">
                <div class="brand-tag">
                    <i class="fas fa-graduation-cap"></i> SENAI Digital CV
                </div>
                <div class="top-bar-actions">
                    <?php if (!empty($pessoa['curriculo_doc'])): ?>
                        <a href="<?= htmlspecialchars($pessoa['curriculo_doc']) ?>" target="_blank" class="btn-top btn-doc" title="Baixar Anexo do Currículo (PDF/Foto)">
                            <i class="fas fa-paperclip"></i> Baixar Anexo
                        </a>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn-top btn-pdf" title="Exportar ou Imprimir em PDF">
                        <i class="fas fa-file-pdf"></i> Imprimir / Salvar PDF
                    </button>
                    <button id="theme-toggle" class="btn-top btn-theme" title="Alternar Tema Claro / Escuro">
                        <i class="fas fa-moon"></i> <span>Tema</span>
                    </button>
                    <a href="admin.php" class="btn-top btn-admin">
                        <i class="fas fa-pen-to-square"></i> Editar Currículo
                    </a>
                </div>
            </div>
        </div>

        <!-- Container Principal do Currículo -->
        <div class="container">

            <!-- CABEÇALHO -->
            <header class="header">

                <!-- Dados Pessoais (Lado Esquerdo) -->
                <div class="header-info">
                    <h1 class="header-nome"><?= htmlspecialchars($pessoa['nome']) ?></h1>
                    <h2 class="header-cargo"><?= htmlspecialchars($pessoa['cargo']) ?></h2>

                    <?php if (!empty($pessoa['cidade'])): ?>
                        <div class="header-cidade">
                            <i class="fas fa-location-dot"></i> <?= htmlspecialchars($pessoa['cidade']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pessoa['resumo'])): ?>
                        <p class="resumo"><?= htmlspecialchars($pessoa['resumo']) ?></p>
                    <?php endif; ?>

                    <!-- Badges de Contato Inteligentes -->
                    <div class="contatos">
                        <?php if (count($contatos) > 0): ?>
                            <?php foreach ($contatos as $contato): ?>
                                <?php
                                    $icone = getIconeContato($contato['tipo']);
                                    $valor = htmlspecialchars($contato['valor']);
                                    $href = '#';

                                    if ($contato['tipo'] === 'email') $href = 'mailto:' . $valor;
                                    elseif ($contato['tipo'] === 'telefone') $href = 'tel:' . preg_replace('/[^0-9+]/', '', $valor);
                                    elseif (in_array($contato['tipo'], ['linkedin', 'github', 'site'])) {
                                        $href = (strpos($valor, 'http') === 0) ? $valor : 'https://' . $valor;
                                    }
                                ?>
                                <a href="<?= $href ?>" target="_blank" class="contato-badge">
                                    <i class="<?= $icone ?>"></i>
                                    <span><?= $valor ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="contato-vazio"><i class="fas fa-info-circle"></i> Nenhum contato cadastrado</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Foto de Perfil + Botão de Upload (Lado Direito) -->
                <div class="perfil-avatar-container">
                    <div class="perfil-avatar">
                        <?php
                        if (!empty($pessoa['foto_url']) && file_exists('uploads/' . $pessoa['foto_url'])) {
                            $fotoExibicao = 'uploads/' . $pessoa['foto_url'];
                            echo "<img src='{$fotoExibicao}' alt='Foto de Perfil de " . htmlspecialchars($pessoa['nome']) . "'>";
                        } else {
                            echo "<i class='fas fa-user-tie'></i>";
                        }
                        ?>
                    </div>

                    <!-- Formulário de foto -->
                    <form action="processa_foto.php" method="POST" enctype="multipart/form-data" id="form-foto">
                        <input type="hidden" name="dados_pessoais_id" value="<?= $pessoa['id'] ?>">

                        <label for="input-foto" class="btn-upload-foto" title="Alterar Foto de Perfil">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" name="foto" id="input-foto" accept="image/*" onchange="this.form.submit()">
                    </form>
                </div>
            </header>

            <main class="content-body">

                <!-- EXPERIÊNCIAS -->
                <section class="section">
                    <h3 class="section-title"><i class="fas fa-briefcase"></i> Experiência Profissional</h3>

                    <?php if (count($experiencias) > 0): ?>
                        <div class="timeline">
                            <?php foreach ($experiencias as $exp): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="item">
                                        <div class="item-header">
                                            <span class="titulo"><?= htmlspecialchars($exp['funcao']) ?></span>
                                            <span class="empresa">— <?= htmlspecialchars($exp['empresa']) ?></span>
                                        </div>
                                        <div class="periodo"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($exp['periodo']) ?></div>
                                        <?php if (!empty($exp['descricao'])): ?>
                                            <div class="descricao"><?= nl2br(htmlspecialchars($exp['descricao'])) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="vazio">Nenhuma experiência cadastrada.</p>
                    <?php endif; ?>
                </section>

                <!-- FORMAÇÃO ACADÊMICA -->
                <section class="section">
                    <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Formação Acadêmica</h3>

                    <?php if (count($formacoes) > 0): ?>
                        <div class="timeline">
                            <?php foreach ($formacoes as $form): ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="item">
                                        <div class="item-header">
                                            <span class="titulo"><?= htmlspecialchars($form['curso']) ?></span>
                                        </div>
                                        <div class="periodo">
                                            <i class="fas fa-school"></i> <?= htmlspecialchars($form['instituicao']) ?> &bull; <i class="far fa-calendar-alt"></i> <?= htmlspecialchars($form['periodo']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="vazio">Nenhuma formação cadastrada.</p>
                    <?php endif; ?>
                </section>

                <!-- CERTIFICADOS E CURSOS COMPLEMENTARES -->
                <section class="section">
                    <h3 class="section-title"><i class="fas fa-award"></i> Certificados e Cursos Complementares</h3>

                    <?php if (count($certificados) > 0): ?>
                        <div class="certificados-grid">
                            <?php foreach ($certificados as $cert): ?>
                                <div class="certificado-card">
                                    <div class="cert-icon">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <div class="cert-details">
                                        <h4 class="cert-nome"><?= htmlspecialchars($cert['nome']) ?></h4>
                                        <p class="cert-instituicao"><?= htmlspecialchars($cert['instituicao']) ?> (<?= htmlspecialchars($cert['ano']) ?>)</p>
                                        <?php if (!empty($cert['link'])): ?>
                                            <?php $isLocalFile = (strpos($cert['link'], 'uploads/') === 0); ?>
                                            <a href="<?= htmlspecialchars($cert['link']) ?>" target="_blank" class="cert-link-btn">
                                                <i class="<?= $isLocalFile ? 'fas fa-file-pdf text-red' : 'fas fa-external-link-alt' ?>"></i>
                                                <?= $isLocalFile ? 'Abrir Anexo / Documento' : 'Ver Credencial Web' ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="vazio">Nenhum certificado cadastrado.</p>
                    <?php endif; ?>
                </section>

            </main>

            <!-- Rodapé discreto -->
            <footer class="footer">
                <p>Currículo Digital gerado via <a href="admin.php">SENAI Digital CV</a> &bull; <?= date('Y') ?></p>
            </footer>

        </div>

    <?php endif; ?>

    <script>
        // Suporte ao tema Dark/Light Mode
        const themeToggleBtn = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        atualizarIconeTema(currentTheme);

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                let theme = document.documentElement.getAttribute('data-theme');
                let newTheme = theme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                atualizarIconeTema(newTheme);
            });
        }

        function atualizarIconeTema(theme) {
            if (!themeToggleBtn) return;
            const span = themeToggleBtn.querySelector('span');
            const icon = themeToggleBtn.querySelector('i');
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
                if (span) span.innerText = ' Modo Claro';
            } else {
                icon.className = 'fas fa-moon';
                if (span) span.innerText = ' Modo Escuro';
            }
        }
    </script>

</body>

</html>