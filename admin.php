<?php
require_once 'crud.php';

// Processamento do Form inicial (trablhado somente em POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    switch ($action) {

        // Salvar os dados pessoais (atualiza)
        case 'salvar_pessoa':
            $dados = [
                'nome' => trim($_POST['nome']),
                'cargo' => trim($_POST['cargo']),
                'resumo' => trim($_POST['resumo']),
                'cidade' => trim($_POST['cidade']),
            ];

            // Validação obrigatória do nome e cargo da pessoa
            if ($dados['nome'] === '' || $dados['cargo'] === '') {
                die('Nome e cargo são obrigatórios.');
            }

            $pessoaExistente = read($pdo, 'dados_pessoais', 'id = ?', [1]);

            if ($pessoaExistente) {
                // Se a pessao já existe os dados são atualizados
                update($pdo, 'dados_pessoais', $dados, 'id = ?', [1]);
            } else {
                // Se a pessao não existe os dados são criados
                create($pdo, 'dados_pessoais', $dados);
            }
            break;

        // Adicionar contato
        case 'adicionar_contato':
            create($pdo, 'contatos', [
                'dados_pessoais_id' => 1,
                'tipo' => $_POST['tipo'],
                'valor' => trim($_POST['valor']),
            ]);
            break;

        // Adicionar experiência
        case 'adicionar_experiencia':
            create($pdo, 'experiencias', [
                'dados_pessoais_id' => 1,
                'empresa' => trim($_POST['empresa']),
                'funcao' => trim($_POST['funcao']),
                'periodo' => trim($_POST['periodo']),
                'descricao' => trim($_POST['descricao']),
            ]);
            break;

        // Adicionar formação 
        case 'adicionar_formacao':
            create($pdo, 'formacao', [
                'dados_pessoais_id' => 1,
                'instituicao' => trim($_POST['instituicao']),
                'curso' => trim($_POST['curso']),
                'periodo' => trim($_POST['periodo']),
            ]);
            break;

        // Excluir registro
        case 'excluir':
            $tabelasPermitidas = ['contatos', 'experiencias', 'formacao'];
            $tabela = $_POST['tabela'] ?? '';
            $id = $_POST['id'] ?? 0;

            if (in_array($tabela, $tabelasPermitidas, true)) {
                delete($pdo, $tabela, 'id = ?', [$id]);
            }
            break;
    }

    header('Location: admin.php');
    exit; 
}

// Busca os dados cadastrados 
$pessoa = read($pdo, 'dados_pessoais', 'id = ?', [1]);
$contatos = $pessoa ? readAll($pdo, 'contatos', 'dados_pessoais_id = ?', [1]) : [];
$experiencias = $pessoa ? readAll($pdo, 'experiencias', 'dados_pessoais_id = ?', [1]) : [];
$formacoes = $pessoa ? readAll($pdo, 'formacao', 'dados_pessoais_id = ?', [1]) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Administração do Currículo</title>
    <link rel="stylesheet" href="style1.css">
</head>

<body>

    <div class="admin-container">

        <h1>Painel Administrativo do Currículo</h1>
        <p><a href="index.php">← Ver currículo publicado</a></p>

        <!-- DADOS PESSOAIS -->
        <section class="card">
            <h2>Dados Pessoais</h2>
            <form method="POST">
                <input type="hidden" name="action" value="salvar_pessoa">

                <label>Nome</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($pessoa['nome'] ?? '') ?>" required>

                <label>Cargo</label>
                <input type="text" name="cargo" value="<?= htmlspecialchars($pessoa['cargo'] ?? '') ?>" required>

                <label>Resumo</label>
                <textarea name="resumo" rows="5"><?= htmlspecialchars($pessoa['resumo'] ?? '') ?></textarea>

                <label>Cidade</label>
                <input type="text" name="cidade" value="<?= htmlspecialchars($pessoa['cidade'] ?? '') ?>">

                <button type="submit">Salvar</button>
            </form>
        </section>

        <?php if ($pessoa): ?>

            <!--CONTATOS -->
            <section class="card">
                <h2>Contatos</h2>

                <ul class="lista">
                    <?php foreach ($contatos as $c): ?>
                        <li>
                            <strong><?= htmlspecialchars($c['tipo']) ?>:</strong>
                            <?= htmlspecialchars($c['valor']) ?>
                            <form method="POST" class="form-excluir">
                                <input type="hidden" name="action" value="excluir">
                                <input type="hidden" name="tabela" value="contatos">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-excluir">Excluir</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="POST" class="form-inline">
                    <input type="hidden" name="action" value="adicionar_contato">
                    <select name="tipo" required>
                        <option value="email">E-mail</option>
                        <option value="telefone">Telefone</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="github">GitHub</option>
                        <option value="site">Site</option>
                        <option value="outro">Outro</option>
                    </select>
                    <input type="text" name="valor" placeholder="Valor (ex: nome@gmail.com)" required>
                    <button type="submit">Adicionar</button>
                </form>
            </section>

            <!-- EXPERIÊNCIAS -->
            <section class="card">
                <h2>Experiências</h2>

                <ul class="lista">
                    <?php foreach ($experiencias as $e): ?>
                        <li>
                            <strong><?= htmlspecialchars($e['funcao']) ?></strong> —
                            <?= htmlspecialchars($e['empresa']) ?>
                            (<?= htmlspecialchars($e['periodo']) ?>)
                            <form method="POST" class="form-excluir">
                                <input type="hidden" name="action" value="excluir">
                                <input type="hidden" name="tabela" value="experiencias">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn-excluir">Excluir</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="POST" class="form-stack">
                    <input type="hidden" name="action" value="adicionar_experiencia">
                    <input type="text" name="empresa" placeholder="Empresa" required>
                    <input type="text" name="funcao" placeholder="Função" required>
                    <input type="text" name="periodo" placeholder="Período (ex: Jan/2024 - Atual)" required>
                    <textarea name="descricao" placeholder="Descrição" rows="5" maxlength="500"></textarea>
                    <button type="submit">Adicionar</button>
                </form>
            </section>

            <!-- FORMAÇÃO -->
            <section class="card">
                <h2>Formação</h2>

                <ul class="lista">
                    <?php foreach ($formacoes as $f): ?>
                        <li>
                            <strong><?= htmlspecialchars($f['curso']) ?></strong> —
                            <?= htmlspecialchars($f['instituicao']) ?>
                            (<?= htmlspecialchars($f['periodo']) ?>)
                            <form method="POST" class="form-excluir">
                                <input type="hidden" name="action" value="excluir">
                                <input type="hidden" name="tabela" value="formacao">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                <button type="submit" class="btn-excluir">Excluir</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="POST" class="form-inline">
                    <input type="hidden" name="action" value="adicionar_formacao">
                    <input type="text" name="instituicao" placeholder="Instituição" required>
                    <input type="text" name="curso" placeholder="Curso" required>
                    <input type="text" name="periodo" placeholder="Período (ex: 2024 - 2026)" required>
                    <button type="submit">Adicionar</button>
                </form>
            </section>

        <?php else: ?>
            <p class="vazio">Salve os dados pessoais primeiro para liberar as demais seções.</p>
        <?php endif; ?>

    </div>

</body>

</html>
