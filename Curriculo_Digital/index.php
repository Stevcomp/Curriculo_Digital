<?php
require_once 'crud.php';

// Processamento do Form inicial (trablhado somente em POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'criar_pessoa') {

    $dados = [
        'nome' => trim($_POST['nome']),
        'cargo' => trim($_POST['cargo']),
        'resumo' => trim($_POST['resumo']),
        'cidade' => trim($_POST['cidade']),
    ];

    // Validação obrigatória do Nome e Cargo da pessoa
    if ($dados['nome'] === '' || $dados['cargo'] === '') {
        die('Favor inserir Nome e Cargo para prosseguir com o currículo.');
    }

    create($pdo, 'dados_pessoais', $dados);

    header('Location: index.php');
    exit;
}

// Busca os dados no banco (agora trablhado com GET)
$pessoa = read($pdo, 'dados_pessoais', 'id = ?', [1]);

// Só busca os relacionados se já existir uma pessoa cadastrada
$contatos = $pessoa ? readAll($pdo, 'contatos', 'dados_pessoais_id = ?', [$pessoa['id']]) : [];
$experiencias = $pessoa ? readAll($pdo, 'experiencias', 'dados_pessoais_id = ?', [$pessoa['id']]) : [];
$formacoes = $pessoa ? readAll($pdo, 'formacao', 'dados_pessoais_id = ?', [$pessoa['id']]) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title><?= $pessoa ? htmlspecialchars($pessoa['nome']) : 'Cadastro Inicial' ?></title>
    <link rel="stylesheet" href="style.css">
    <?php if (!$pessoa): ?>
        <link rel="stylesheet" href="style1.css">
    <?php endif; ?>
</head>

<body>

    <?php if (!$pessoa): ?>

        <!-- Form inicial -->
        <div class="admin-container">
            <section class="card">
                <h1>Transforme suas experiências em um currículo digital de respeito!</h1>
                <p>Preencha os dados abaixo para iniciar. Depois você poderá criar ou adicionar suas informações no <a
                        href="admin.php">painel administrativo</a>.</p>

                <form method="POST">
                    <input type="hidden" name="action" value="criar_pessoa">

                    <label>Nome</label>
                    <input type="text" name="nome" required>

                    <label>Cargo</label>
                    <input type="text" name="cargo" required>

                    <label>Resumo</label>
                    <textarea name="resumo" rows="5" maxlength="500"></textarea>

                    <label>Cidade</label>
                    <input type="text" name="cidade">

                    <button type="submit">Criar currículo</button>
                </form>
            </section>
        </div>

    <?php else: ?>

        <!-- Form do currículo em si -->
        <div class="container">

            <!-- CABEÇALHO -->
            <div class="header">
                <h1><?= htmlspecialchars($pessoa['nome']) ?></h1>
                <h2><?= htmlspecialchars($pessoa['cargo']) ?></h2>

                <?php if (!empty($pessoa['resumo'])): ?>
                    <p class="resumo"><?= htmlspecialchars($pessoa['resumo']) ?></p>
                <?php endif; ?>

                <div class="contatos">
                    <?php if (count($contatos) > 0): ?>
                        <?php foreach ($contatos as $contato): ?>
                            <span>
                                <?= htmlspecialchars(ucfirst($contato['tipo'])) ?>:
                                <?= htmlspecialchars($contato['valor']) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span>Nenhum contato cadastrado</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- EXPERIÊNCIAS -->
            <div class="section">
                <h3>Experiência Profissional</h3>

                <?php if (count($experiencias) > 0): ?>
                    <?php foreach ($experiencias as $exp): ?>
                        <div class="item">
                            <div class="titulo">
                                <?= htmlspecialchars($exp['funcao']) ?> — <?= htmlspecialchars($exp['empresa']) ?>
                            </div>
                            <div class="periodo"><?= htmlspecialchars($exp['periodo']) ?></div>
                            <div class="descricao"><?= htmlspecialchars($exp['descricao']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="vazio">Nenhuma experiência cadastrada.</p>
                <?php endif; ?>
            </div>

            <!-- FORMAÇÃO -->
            <div class="section">
                <h3>Formação Acadêmica/Profissional</h3>

                <?php if (count($formacoes) > 0): ?>
                    <?php foreach ($formacoes as $form): ?>
                        <div class="item">
                            <div class="titulo"><?= htmlspecialchars($form['curso']) ?></div>
                            <div class="periodo">
                                <?= htmlspecialchars($form['instituicao']) ?> · <?= htmlspecialchars($form['periodo']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="vazio">Nenhuma formação cadastrada.</p>
                <?php endif; ?>
            </div>

            <p style="text-align:center; padding: 15px; font-size: 13px;">
                <a href="admin.php">✏️ Editar currículo</a>
            </p>

        </div>

    <?php endif; ?>

</body>

</html>