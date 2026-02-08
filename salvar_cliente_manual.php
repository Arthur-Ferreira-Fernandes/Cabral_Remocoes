<?php
// salvar_cliente_manual.php
require 'Scripts/conecta_banco.php';
require 'Scripts/protecao.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if (empty($nome)) {
        header("Location: clientes.php?msg=erro_nome");
        exit;
    }

    try {
        $stmtCheck = $pdo->prepare("SELECT id FROM clientes WHERE nome = ?");
        $stmtCheck->execute([$nome]);
        
        if ($stmtCheck->fetch()) {
            header("Location: clientes.php?msg=erro_existe");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$nome, $email, $telefone])) {
            // SUCESSO! 
            // Aqui está o segredo: Passamos o telefone e nome na URL para abrir o modal automático
            $urlRedirect = "clientes.php?msg=sucesso_cadastro&new_nome=" . urlencode($nome) . "&new_tel=" . urlencode($telefone);
            header("Location: " . $urlRedirect);
        } else {
            header("Location: clientes.php?msg=erro_banco");
        }

    } catch (PDOException $e) {
        header("Location: clientes.php?msg=erro_tecnico");
    }

} else {
    header("Location: clientes.php");
    exit;
}
?>