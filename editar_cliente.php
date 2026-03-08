<?php
// editar_cliente.php
require 'Scripts/protecao.php';
require 'Scripts/conecta_banco.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: clientes.php");
    exit;
}

$msg = '';
$erro = '';

// --- PROCESSAMENTO (SALVAR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome     = trim($_POST['nome']);
    $email    = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);

    if (empty($nome)) {
        $erro = "O nome é obrigatório.";
    } else {
        // Verifica se já existe OUTRO cliente com esse nome (exceto ele mesmo)
        $stmtCheck = $pdo->prepare("SELECT id FROM clientes WHERE nome = ? AND id != ?");
        $stmtCheck->execute([$nome, $id]);

        if ($stmtCheck->fetch()) {
            $erro = "Já existe outro cliente cadastrado com este nome.";
        } else {
            // Atualiza
            $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ? WHERE id = ?");
            if ($stmt->execute([$nome, $email, $telefone, $id])) {
                header("Location: clientes.php?msg=sucesso_edicao");
                exit;
            } else {
                $erro = "Erro ao salvar no banco de dados.";
            }
        }
    }
}

// --- BUSCA DADOS ATUAIS ---
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header("Location: clientes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Cliente</h5>
                        <a href="clientes.php" class="btn btn-sm btn-light text-primary fw-bold">Voltar</a>
                    </div>
                    <div class="card-body">
                        
                        <?php if($erro): ?>
                            <div class="alert alert-danger"><?= $erro ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nome Completo *</label>
                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($cliente['email']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Telefone / WhatsApp</label>
                                <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($cliente['telefone']) ?>" placeholder="(00) 00000-0000">
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg">Salvar Alterações</button>
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