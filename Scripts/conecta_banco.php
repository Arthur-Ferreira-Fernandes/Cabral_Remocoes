<?php
// db.php
$host = 'localhost';
$db   = 'cabral_remocoes';
$user = 'root'; // Seu usuário
$pass = '';     // Sua senha

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>