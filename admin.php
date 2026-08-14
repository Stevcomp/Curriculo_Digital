<?php
require_once 'crud.php';

// Função auxiliar para processar uploads do explorador de arquivos
function processarUploadArquivo($fileKey, $pasta = 'uploads/certificados') {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES[$fileKey];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];

        if (in_array($extensao, $permitidas)) {
            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }
            $novoNome = 'anexo_' . time() . '_' . rand(1000, 9999) . '.' . $extensao;
            $destino = $pasta . '/' . $novoNome;
            if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
                return $destino;
            }
        }
    }
    return null;
}

// Processamento dos formulários (somente POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {

        // Salvar os dados pessoais + Anexo do Currículo em PDF/Foto
        case 'salvar_pessoa':
            $dados = [
                'nome' => trim($_POST['nome']),
                'cargo' => trim($_POST['cargo']),
                'resumo' => trim($_POST['resumo']),
                'cidade' => trim($_POST['cidade']),
            ];

            if ($dados['nome'] === '' || $dados['cargo'] === '') {
                die('Nome e cargo são obrigatórios.');
            }

            // Verifica se o usuário enviou um arquivo de currículo (PDF/Doc/Foto) do explorador
            $anexoCv = processarUploadArquivo('arquivo_cv', 'uploads/documentos');
            if ($anexoCv) {
                $dados['curriculo_doc'] = $anexoCv;
            }

            $pessoaExistente = read($pdo, 'dados_pessoais', 'id = ?', [1]);
            if ($pessoaExistente) {
                update($pdo, 'dados_pessoais', $dados, 'id = ?', [1]);
            } else {
                create($pdo, 'dados_pessoais', $dados);
            }
            break;

        // --- CONTATOS ---
        case 'adicionar_contato':
            create($pdo, 'contatos', [
                'dados_pessoais_id' => 1,
                'tipo' => $_POST['tipo'],
                'valor' => trim($_POST['valor']),
            ]);
            break;

        case 'editar_contato':
            $id = (int)($_POST['id'] ?? 0);
            update($pdo, 'contatos', [
                'tipo' => $_POST['tipo'],
                'valor' => trim($_POST['valor']),
            ], 'id = ?', [$id]);
            break;

        // --- EXPERIÊNCIAS ---
        case 'adicionar_experiencia':
            create($pdo, 'experiencias', [
                'dados_pessoais_id' => 1,
                'empresa' => trim($_POST['empresa']),
                'funcao' => trim($_POST['funcao']),
                'periodo' => trim($_POST['periodo']),
                'descricao' => trim($_POST['descricao']),
            ]);
            break;

        case 'editar_experiencia':
            $id = (int)($_POST['id'] ?? 0);
            update($pdo, 'experiencias', [
                'empresa' => trim($_POST['empresa']),
                'funcao' => trim($_POST['funcao']),
                'periodo' => trim($_POST['periodo']),
                'descricao' => trim($_POST['descricao']),
            ], 'id = ?', [$id]);
            break;

        // --- FORMAÇÃO ---
        case 'adicionar_formacao':
            create($pdo, 'formacao', [
                'dados_pessoais_id' => 1,
                'instituicao' => trim($_POST['instituicao']),
                'curso' => trim($_POST['curso']),
                'periodo' => trim($_POST['periodo']),
            ]);
            break;

        case 'editar_formacao':
            $id = (int)($_POST['id'] ?? 0);
            update($pdo, 'formacao', [
                'instituicao' => trim($_POST['instituicao']),
                'curso' => trim($_POST['curso']),
                'periodo' => trim($_POST['periodo']),
            ], 'id = ?', [$id]);
            break;

        // --- CERTIFICADOS (Com upload de arquivo PDF/Foto) ---
        case 'adicionar_certificado':
            $link = trim($_POST['link'] ?? '');
            $arquivoUpload = processarUploadArquivo('arquivo_certificado', 'uploads/certificados');
            if ($arquivoUpload) {
                $link = $arquivoUpload;
            }

            create($pdo, 'certificados', [
                'dados_pessoais_id' => 1,
                'nome' => trim($_POST['nome']),
                'instituicao' => trim($_POST['instituicao']),
                'ano' => trim($_POST['ano']),
                'link' => $link,
            ]);
            break;

        case 'editar_certificado':
            $id = (int)($_POST['id'] ?? 0);
            $link = trim($_POST['link'] ?? '');
            $arquivoUpload = processarUploadArquivo('arquivo_certificado', 'uploads/certificados');
            if ($arquivoUpload) {
                $link = $arquivoUpload;
            }

            $dadosCert = [
                'nome' => trim($_POST['nome']),
                'instituicao' => trim($_POST['instituicao']),
                'ano' => trim($_POST['ano']),
            ];
            if (!empty($link)) {
                $dadosCert['link'] = $link;
            }

            update($pdo, 'certificados', $dadosCert, 'id = ?', [$id]);
            break;

        // --- EXCLUIR ---
        case 'excluir':
            $tabelasPermitidas = ['contatos', 'experiencias', 'formacao', 'certificados'];
            $tabela = $_POST['tabela'] ?? '';
            $id = (int)($_POST['id'] ?? 0);

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
$certificados = $pessoa ? readAll($pdo, 'certificados', 'dados_pessoais_id = ?', [1]) : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo — Currículo Digital SENAI</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style1.css">
</head>

<body>

    <div class="admin-container">

        <header class="admin-header">
            <div class="header-title">
                <h1><i class="fas fa-sliders"></i> Painel Administrativo</h1>
                <p>Gerencie todas as informações e arquivos do seu currículo digital</p>
            </div>
            <div class="header-actions">
                <a href="index.php" class="btn-secondary"><i class="fas fa-eye"></i> Ver Currículo Publicado</a>
                <button id="theme-toggle" class="btn-icon" title="Alternar Tema"><i class="fas fa-moon"></i></button>
            </div>
        </header>

        <!-- DADOS PESSOAIS -->
        <section class="card">
            <h2><i class="fas fa-user-gear"></i> Dados Pessoais e Anexo</h2>
            <form method="POST" enctype="multipart/form-data" class="form-grid">
                <input type="hidden" name="action" value="salvar_pessoa">

                <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($pessoa['nome'] ?? '') ?>" required placeholder="Ex: João da Silva">
                </div>

                <div class="form-group">
                    <label>Cargo / Título Profissional *</label>
                    <input type="text" name="cargo" value="<?= htmlspecialchars($pessoa['cargo'] ?? '') ?>" required placeholder="Ex: Desenvolvedor Full Stack">
                </div>

                <div class="form-group full-width">
                    <label>Resumo Profissional</label>
                    <textarea name="resumo" rows="4" placeholder="Descreva brevemente seus objetivos, conquistas e habilidades..."><?= htmlspecialchars($pessoa['resumo'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Cidade / Estado</label>
                    <input type="text" name="cidade" value="<?= htmlspecialchars($pessoa['cidade'] ?? '') ?>" placeholder="Ex: São Paulo, SP">
                </div>

                <!-- Campo para Selecionar Documento PDF/Foto do Currículo no Explorador -->
                <div class="form-group">
                    <label><i class="fas fa-file-pdf text-red"></i> Anexo do Currículo (PDF, Doc ou Foto)</label>
                    <input type="file" name="arquivo_cv" accept=".pdf, .png, .jpg, .jpeg, .webp, .doc, .docx">
                    <?php if (!empty($pessoa['curriculo_doc'])): ?>
                        <span class="file-attached"><i class="fas fa-paperclip"></i> Anexo Atual: <a href="<?= htmlspecialchars($pessoa['curriculo_doc']) ?>" target="_blank">Ver Arquivo Cadastrado</a></span>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-floppy-disk"></i> Salvar Dados Pessoais</button>
                </div>
            </form>
        </section>

        <?php if ($pessoa): ?>

            <!-- CONTATOS -->
            <section class="card">
                <h2><i class="fas fa-address-book"></i> Contatos</h2>

                <ul class="lista">
                    <?php if (count($contatos) > 0): ?>
                        <?php foreach ($contatos as $c): ?>
                            <li>
                                <div class="item-info">
                                    <span class="badge-tipo"><?= htmlspecialchars(strtoupper($c['tipo'])) ?></span>
                                    <span class="item-valor"><?= htmlspecialchars($c['valor']) ?></span>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-editar" onclick="abrirModalContato(<?= htmlspecialchars(json_encode($c)) ?>)">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                    <form method="POST" class="form-excluir" onsubmit="return confirm('Tem certeza que deseja excluir este contato?');">
                                        <input type="hidden" name="action" value="excluir">
                                        <input type="hidden" name="tabela" value="contatos">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-excluir"><i class="fas fa-trash"></i> Excluir</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="vazio">Nenhum contato adicionado ainda.</p>
                    <?php endif; ?>
                </ul>

                <form method="POST" class="form-inline">
                    <input type="hidden" name="action" value="adicionar_contato">
                    <select name="tipo" required>
                        <option value="email">E-mail</option>
                        <option value="telefone">Telefone / WhatsApp</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="github">GitHub</option>
                        <option value="site">Site / Portfólio</option>
                        <option value="outro">Outro</option>
                    </select>
                    <input type="text" name="valor" placeholder="Valor (ex: joao@email.com ou (11) 99999-9999)" required>
                    <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Adicionar Contato</button>
                </form>
            </section>

            <!-- EXPERIÊNCIAS -->
            <section class="card">
                <h2><i class="fas fa-briefcase"></i> Experiências Profissionais</h2>

                <ul class="lista">
                    <?php if (count($experiencias) > 0): ?>
                        <?php foreach ($experiencias as $e): ?>
                            <li>
                                <div class="item-info">
                                    <strong><?= htmlspecialchars($e['funcao']) ?></strong> na <span class="empresa-nome"><?= htmlspecialchars($e['empresa']) ?></span>
                                    <span class="periodo-badge"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($e['periodo']) ?></span>
                                    <?php if (!empty($e['descricao'])): ?>
                                        <p class="desc-preview"><?= htmlspecialchars($e['descricao']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-editar" onclick="abrirModalExperiencia(<?= htmlspecialchars(json_encode($e)) ?>)">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                    <form method="POST" class="form-excluir" onsubmit="return confirm('Tem certeza que deseja excluir esta experiência?');">
                                        <input type="hidden" name="action" value="excluir">
                                        <input type="hidden" name="tabela" value="experiencias">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <button type="submit" class="btn-excluir"><i class="fas fa-trash"></i> Excluir</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="vazio">Nenhuma experiência cadastrada ainda.</p>
                    <?php endif; ?>
                </ul>

                <form method="POST" class="form-stack">
                    <h3><i class="fas fa-plus-circle"></i> Adicionar Nova Experiência</h3>
                    <div class="form-row">
                        <input type="text" name="empresa" placeholder="Empresa (ex: SENAI SP)" required>
                        <input type="text" name="funcao" placeholder="Função / Cargo (ex: Desenvolvedor Júnior)" required>
                    </div>
                    <input type="text" name="periodo" placeholder="Período (ex: Jan/2024 - Atual)" required>
                    <textarea name="descricao" placeholder="Descrição das responsabilidades e realizações..." rows="3" maxlength="500"></textarea>
                    <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Adicionar Experiência</button>
                </form>
            </section>

            <!-- FORMAÇÃO -->
            <section class="card">
                <h2><i class="fas fa-graduation-cap"></i> Formação Acadêmica / Cursos</h2>

                <ul class="lista">
                    <?php if (count($formacoes) > 0): ?>
                        <?php foreach ($formacoes as $f): ?>
                            <li>
                                <div class="item-info">
                                    <strong><?= htmlspecialchars($f['curso']) ?></strong> — <?= htmlspecialchars($f['instituicao']) ?>
                                    <span class="periodo-badge"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($f['periodo']) ?></span>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-editar" onclick="abrirModalFormacao(<?= htmlspecialchars(json_encode($f)) ?>)">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                    <form method="POST" class="form-excluir" onsubmit="return confirm('Tem certeza que deseja excluir esta formação?');">
                                        <input type="hidden" name="action" value="excluir">
                                        <input type="hidden" name="tabela" value="formacao">
                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                        <button type="submit" class="btn-excluir"><i class="fas fa-trash"></i> Excluir</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="vazio">Nenhuma formação cadastrada ainda.</p>
                    <?php endif; ?>
                </ul>

                <form method="POST" class="form-stack">
                    <h3><i class="fas fa-plus-circle"></i> Adicionar Nova Formação</h3>
                    <div class="form-row">
                        <input type="text" name="instituicao" placeholder="Instituição (ex: Escola SENAI)" required>
                        <input type="text" name="curso" placeholder="Curso / Habilitação (ex: Técnico em Desenvolvimento de Sistemas)" required>
                    </div>
                    <input type="text" name="periodo" placeholder="Período (ex: 2024 - 2026)" required>
                    <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Adicionar Formação</button>
                </form>
            </section>

            <!-- CERTIFICADOS (Com seletor do Explorador de Arquivos) -->
            <section class="card">
                <h2><i class="fas fa-award"></i> Certificados e Cursos Complementares</h2>

                <ul class="lista">
                    <?php if (count($certificados) > 0): ?>
                        <?php foreach ($certificados as $cert): ?>
                            <li>
                                <div class="item-info">
                                    <strong><i class="fas fa-certificate text-gold"></i> <?= htmlspecialchars($cert['nome']) ?></strong> — <?= htmlspecialchars($cert['instituicao']) ?>
                                    <span class="periodo-badge"><i class="far fa-calendar-check"></i> <?= htmlspecialchars($cert['ano']) ?></span>
                                    <?php if (!empty($cert['link'])): ?>
                                        <a href="<?= htmlspecialchars($cert['link']) ?>" target="_blank" class="cert-link">
                                            <i class="<?= strpos($cert['link'], 'uploads/') === 0 ? 'fas fa-file-lines' : 'fas fa-external-link-alt' ?>"></i>
                                            <?= strpos($cert['link'], 'uploads/') === 0 ? 'Ver Documento Anexado' : 'Ver Credencial Web' ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="item-actions">
                                    <button class="btn-editar" onclick="abrirModalCertificado(<?= htmlspecialchars(json_encode($cert)) ?>)">
                                        <i class="fas fa-pen"></i> Editar
                                    </button>
                                    <form method="POST" class="form-excluir" onsubmit="return confirm('Tem certeza que deseja excluir este certificado?');">
                                        <input type="hidden" name="action" value="excluir">
                                        <input type="hidden" name="tabela" value="certificados">
                                        <input type="hidden" name="id" value="<?= $cert['id'] ?>">
                                        <button type="submit" class="btn-excluir"><i class="fas fa-trash"></i> Excluir</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="vazio">Nenhum certificado cadastrado ainda.</p>
                    <?php endif; ?>
                </ul>

                <form method="POST" enctype="multipart/form-data" class="form-stack">
                    <input type="hidden" name="action" value="adicionar_certificado">
                    <h3><i class="fas fa-plus-circle"></i> Adicionar Novo Certificado</h3>
                    
                    <div class="form-row">
                        <input type="text" name="nome" placeholder="Nome do Certificado (ex: Certificação Scrum Fundamentals)" required>
                        <input type="text" name="instituicao" placeholder="Instituição / Emissor (ex: Udemy, SENAI, Cisco)" required>
                    </div>
                    
                    <div class="form-row">
                        <input type="text" name="ano" placeholder="Ano de Conclusão (ex: 2025)" required>
                        <input type="url" name="link" placeholder="Link da Web (opcional)">
                    </div>

                    <!-- Seletor do Explorador de Arquivos para PDF, Foto ou Documento -->
                    <div class="file-input-box">
                        <label><i class="fas fa-folder-open"></i> Selecionar Arquivo do Computador (PDF, Foto, DOCX)</label>
                        <input type="file" name="arquivo_certificado" accept=".pdf, .png, .jpg, .jpeg, .webp, .doc, .docx">
                    </div>

                    <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Adicionar Certificado</button>
                </form>
            </section>

        <?php else: ?>
            <p class="vazio">Salve os dados pessoais primeiro para liberar as demais seções.</p>
        <?php endif; ?>

    </div>

    <!-- MODAL GENÉRICO DE EDIÇÃO -->
    <div id="modal-edicao" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="fecharModal()">&times;</span>
            <h2 id="modal-titulo"><i class="fas fa-edit"></i> Editar Informação</h2>
            <form method="POST" enctype="multipart/form-data" id="modal-form">
                <input type="hidden" name="action" id="modal-action">
                <input type="hidden" name="id" id="modal-id">

                <div id="modal-campos"></div>

                <div class="form-actions" style="margin-top:20px;">
                    <button type="button" class="btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Suporte ao tema Dark/Light Mode no Admin
        const themeToggleBtn = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        atualizarIconeTema(currentTheme);

        themeToggleBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            let newTheme = theme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            atualizarIconeTema(newTheme);
        });

        function atualizarIconeTema(theme) {
            themeToggleBtn.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        }

        // Modais de Edição Dinâmicos
        const modal = document.getElementById('modal-edicao');
        const modalTitulo = document.getElementById('modal-titulo');
        const modalAction = document.getElementById('modal-action');
        const modalId = document.getElementById('modal-id');
        const modalCampos = document.getElementById('modal-campos');

        function fecharModal() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                fecharModal();
            }
        }

        function abrirModalContato(contato) {
            modalTitulo.innerHTML = '<i class="fas fa-edit"></i> Editar Contato';
            modalAction.value = 'editar_contato';
            modalId.value = contato.id;

            modalCampos.innerHTML = `
                <div class="form-group">
                    <label>Tipo de Contato</label>
                    <select name="tipo" required>
                        <option value="email" ${contato.tipo === 'email' ? 'selected' : ''}>E-mail</option>
                        <option value="telefone" ${contato.tipo === 'telefone' ? 'selected' : ''}>Telefone / WhatsApp</option>
                        <option value="linkedin" ${contato.tipo === 'linkedin' ? 'selected' : ''}>LinkedIn</option>
                        <option value="github" ${contato.tipo === 'github' ? 'selected' : ''}>GitHub</option>
                        <option value="site" ${contato.tipo === 'site' ? 'selected' : ''}>Site / Portfólio</option>
                        <option value="outro" ${contato.tipo === 'outro' ? 'selected' : ''}>Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Valor / Link</label>
                    <input type="text" name="valor" value="${escapeHtml(contato.valor)}" required>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function abrirModalExperiencia(exp) {
            modalTitulo.innerHTML = '<i class="fas fa-edit"></i> Editar Experiência';
            modalAction.value = 'editar_experiencia';
            modalId.value = exp.id;

            modalCampos.innerHTML = `
                <div class="form-group">
                    <label>Empresa</label>
                    <input type="text" name="empresa" value="${escapeHtml(exp.empresa)}" required>
                </div>
                <div class="form-group">
                    <label>Função / Cargo</label>
                    <input type="text" name="funcao" value="${escapeHtml(exp.funcao)}" required>
                </div>
                <div class="form-group">
                    <label>Período</label>
                    <input type="text" name="periodo" value="${escapeHtml(exp.periodo)}" required>
                </div>
                <div class="form-group full-width">
                    <label>Descrição</label>
                    <textarea name="descricao" rows="4">${escapeHtml(exp.descricao || '')}</textarea>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function abrirModalFormacao(form) {
            modalTitulo.innerHTML = '<i class="fas fa-edit"></i> Editar Formação';
            modalAction.value = 'editar_formacao';
            modalId.value = form.id;

            modalCampos.innerHTML = `
                <div class="form-group">
                    <label>Instituição</label>
                    <input type="text" name="instituicao" value="${escapeHtml(form.instituicao)}" required>
                </div>
                <div class="form-group">
                    <label>Curso / Habilitação</label>
                    <input type="text" name="curso" value="${escapeHtml(form.curso)}" required>
                </div>
                <div class="form-group">
                    <label>Período</label>
                    <input type="text" name="periodo" value="${escapeHtml(form.periodo)}" required>
                </div>
            `;
            modal.style.display = 'flex';
        }

        function abrirModalCertificado(cert) {
            modalTitulo.innerHTML = '<i class="fas fa-edit"></i> Editar Certificado';
            modalAction.value = 'editar_certificado';
            modalId.value = cert.id;

            modalCampos.innerHTML = `
                <div class="form-group">
                    <label>Nome do Certificado</label>
                    <input type="text" name="nome" value="${escapeHtml(cert.nome)}" required>
                </div>
                <div class="form-group">
                    <label>Instituição / Emissor</label>
                    <input type="text" name="instituicao" value="${escapeHtml(cert.instituicao)}" required>
                </div>
                <div class="form-group">
                    <label>Ano</label>
                    <input type="text" name="ano" value="${escapeHtml(cert.ano)}" required>
                </div>
                <div class="form-group">
                    <label>Link da Web (opcional)</label>
                    <input type="url" name="link" value="${escapeHtml(cert.link || '')}">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-folder-open"></i> Substituir Arquivo / PDF (do explorador)</label>
                    <input type="file" name="arquivo_certificado" accept=".pdf, .png, .jpg, .jpeg, .webp, .doc, .docx">
                </div>
            `;
            modal.style.display = 'flex';
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>

</html>
