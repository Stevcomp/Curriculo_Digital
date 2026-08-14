<?php
// Configurações do banco de dados do projeto Currículo Digital
$host    = 'localhost';
$port    = 3306;
$dbname  = 'curriculo_db';   
$usuario = 'root';
$senha   = '';              

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Garante que a tabela de certificados exista
    $pdo->exec("CREATE TABLE IF NOT EXISTS certificados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dados_pessoais_id INT NOT NULL,
        nome VARCHAR(150) NOT NULL,
        instituicao VARCHAR(150) NOT NULL,
        ano VARCHAR(50) NOT NULL,
        link VARCHAR(255) DEFAULT NULL,
        CONSTRAINT fk_certificados_dados_pessoais
            FOREIGN KEY (dados_pessoais_id)
            REFERENCES dados_pessoais(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Adiciona a coluna curriculo_doc em dados_pessoais se não existir
    $stmtCol = $pdo->query("SHOW COLUMNS FROM dados_pessoais LIKE 'curriculo_doc'");
    if (!$stmtCol->fetch()) {
        $pdo->exec("ALTER TABLE dados_pessoais ADD COLUMN curriculo_doc VARCHAR(255) DEFAULT NULL");
    }
} catch (PDOException $e) {
    die(json_encode(['erro' => 'Falha na conexão: ' . $e->getMessage()]));
}
?>