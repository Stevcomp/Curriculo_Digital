<?php
require_once 'conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $idPessoa = $_POST['dados_pessoais_id'] ?? null;
    $foto = $_FILES['foto'];

    // Valida se o arquivo foi enviado
    if ($foto['error'] === UPLOAD_ERR_OK && $idPessoa) {
        $extensao = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $extensoesPermitidas)) {
            // Cria a pasta 'uploads' se ela ainda não existir
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            // Gera um nome único para a imagem não sobrescrever outras fotos
            $novoNomeFoto = 'perfil_' . $idPessoa . '_' . time() . '.' . $extensao;
            $destino = 'uploads/' . $novoNomeFoto;

            // Move o arquivo temporário para a pasta uploads
            if (move_uploaded_file($foto['tmp_name'], $destino)) {
                
                // Atualiza a coluna 'foto_url' na tabela do banco de dados
                $stmt = $pdo->prepare("UPDATE dados_pessoais SET foto_url = :foto WHERE id = :id");
                $stmt->execute([
                    ':foto' => $novoNomeFoto,
                    ':id'   => $idPessoa
                ]);
            }
        }
    }

    header('Location: index.php');
    exit;
}