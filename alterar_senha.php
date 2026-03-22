<?php
// alterar_senha.php
require 'Scripts/protecao.php';
require 'Scripts/conecta_banco.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = trim($_POST['senha_atual']);
    $nova_senha = trim($_POST['nova_senha']);
    $confirma_senha = trim($_POST['confirma_senha']);

    // Validações básicas
    if (empty($senha_atual) || empty($nova_senha) || empty($confirma_senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif ($nova_senha !== $confirma_senha) {
        $erro = "A nova senha e a confirmação não são iguais.";
    } elseif (strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        // Busca a senha atual do usuário logado no banco
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();

        // Verifica se a senha atual digitada está correta
        if ($usuario && password_verify($senha_atual, $usuario['senha'])) {
            
            // Criptografa a nova senha
            $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            
            // Atualiza no banco
            $update = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            if ($update->execute([$novo_hash, $_SESSION['usuario_id']])) {
                $sucesso = "Sua senha foi alterada com sucesso!";
            } else {
                $erro = "Erro ao atualizar a senha no banco de dados.";
            }
        } else {
            $erro = "A senha atual está incorreta.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-key-fill"></i> Alterar Minha Senha</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if($erro): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $erro ?></div>
                        <?php endif; ?>
                        
                        <?php if($sucesso): ?>
                            <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $sucesso ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Senha Atual</label>
                                <input type="password" name="senha_atual" class="form-control" required placeholder="Digite sua senha atual">
                            </div>
                            
                            <hr class="my-4">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control" required placeholder="Mínimo 6 caracteres">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-primary">Confirmar Nova Senha</label>
                                <input type="password" name="confirma_senha" class="form-control" required placeholder="Repita a nova senha">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Salvar Nova Senha</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>