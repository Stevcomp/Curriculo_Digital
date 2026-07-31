<?php
require_once 'conexao.php';

function create($pdo, $table, array $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
    $stmt->execute(array_values($data));
    return $pdo->lastInsertId();
}

function readAll($pdo, $table, $where = null, $params = []) {
    $sql = "SELECT * FROM $table";
    if ($where) $sql .= " WHERE $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function read($pdo, $table, $where = null, $params = []) {
    $sql = "SELECT * FROM $table";
    if ($where) $sql .= " WHERE $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function update($pdo, $table, array $data, $where, $params = []) {
    $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
    $stmt = $pdo->prepare("UPDATE $table SET $set WHERE $where");
    $stmt->execute(array_merge(array_values($data), $params));
    return $stmt->rowCount();
}

function delete($pdo, $table, $where, $params = []) {
    $stmt = $pdo->prepare("DELETE FROM $table WHERE $where");
    return $stmt->execute($params);
}
?>