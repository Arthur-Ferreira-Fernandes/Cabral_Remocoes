<?php
// novo_usuario.php
require 'Scripts/protecao.php'; // Garante que SÓ QUEM ESTÁ LOGADO pode acessar
require 'Scripts/conecta_banco.php';

$msg = '';
$erro = '';

// --- PROCESSAMENTO (CADASTRAR NOVO ADMIN) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        // Verifica se o email já existe para não duplicar
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmtCheck->execute([$email]);
        
        if ($stmtCheck->fetch()) {
            $erro = "Já existe um usuário cadastrado com este e-mail.";
        } else {
            // Criptografa a senha nova
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Salva no banco
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            if ($stmt->execute([$nome, $email, $hash])) {
                $msg = "Novo administrador cadastrado com sucesso!";
            } else {
                $erro = "Erro ao salvar no banco de dados.";
            }
        }
    }
}

// --- BUSCA LISTA DE USUÁRIOS ATUAIS ---
$stmtLista = $pdo->query("SELECT id, nome, email, created_at FROM usuarios ORDER BY nome ASC");
$usuarios = $stmtLista->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Administradores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            
            <div class="col-md-5 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-person-plus-fill"></i> Novo Administrador</h5>
                    </div>
                    <div class="card-body">
                        
                        <?php if($erro): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $erro ?></div>
                        <?php endif; ?>
                        
                        <?php if($msg): ?>
                            <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $msg ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" required placeholder="Ex: João Silva">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">E-mail de Acesso</label>
                                <input type="email" name="email" class="form-control" required placeholder="email@empresa.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Senha Inicial</label>
                                <input type="password" name="senha" class="form-control" required placeholder="Mínimo 6 caracteres">
                                <div class="form-text small text-muted">A senha será criptografada no banco de dados.</div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Cadastrar Acesso</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-people-fill"></i> Usuários com Acesso</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Nome</th>
                                        <th>E-mail</th>
                                        <th>Data de Criação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($usuarios as $u): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold">
                                            <?= $u['nome'] ?>
                                            <?php if($u['id'] == $_SESSION['usuario_id']): ?>
                                                <span class="badge bg-success ms-1">Você</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted"><?= $u['email'] ?></td>
                                        <td class="small text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>