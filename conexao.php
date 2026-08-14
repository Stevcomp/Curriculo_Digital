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

} catch (PDOException $e) {
    die(json_encode(['erro' => 'Falha na conexão: ' . $e->getMessage()]));
}
?>