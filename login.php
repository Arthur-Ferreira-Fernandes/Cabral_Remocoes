<?php
// login.php
require 'Scripts/conecta_banco.php';

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Busca o usuário pelo email
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verifica se usuário existe E se a senha bate com a criptografia
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        
        // Inicia a sessão
        if (!isset($_SESSION)) session_start();
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        header("Location: index.php"); // Manda pro Painel
        exit;
    } else {
        $erro = "Email ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Cabral Remoções</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <div class="card card-login bg-white p-4">
        <div class="card-body">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-primary">Cabral Remoções</h3>
                <p class="text-muted">Acesso Restrito ao Sistema</p>
            </div>

            <?php if(isset($erro)): ?>
                <div class="alert alert-danger text-center"><?= $erro ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="admin@exemplo.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Senha</label>
                    <input type="password" name="senha" class="form-control form-control-lg" placeholder="********" required>
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">ENTRAR</button>
                </div>
            </form>
            
            <div class="text-center mt-3 text-muted small">
                &copy; <?= date('Y') ?> Sistema Interno
            </div>
        </div>
    </div>

</body>
</html>