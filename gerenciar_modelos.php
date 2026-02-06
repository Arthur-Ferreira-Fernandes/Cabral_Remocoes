<?php
require 'Scripts/conecta_banco.php';

// --- Lógica de Exclusão ---
if (isset($_GET['excluir_id'])) {
    $id = $_GET['excluir_id'];
    
    // 1. Busca o arquivo para deletar da pasta física
    $stmt = $pdo->prepare("SELECT caminho_arquivo FROM templates WHERE id = ?");
    $stmt->execute([$id]);
    $modelo = $stmt->fetch();

    if ($modelo && file_exists($modelo['caminho_arquivo'])) {
        unlink($modelo['caminho_arquivo']); // Deleta o arquivo PDF
    }

    // 2. CORREÇÃO DO ERRO: Deletar registros dependentes primeiro
    // Apaga o histórico gerado com este modelo
    $pdo->prepare("DELETE FROM historico_gerado WHERE template_id = ?")->execute([$id]);
    
    // Apaga as coordenadas dos campos (caso o banco não tenha CASCADE configurado)
    $pdo->prepare("DELETE FROM coordenadas_template WHERE template_id = ?")->execute([$id]);

    // 3. Agora sim, deleta o Modelo
    $stmtDel = $pdo->prepare("DELETE FROM templates WHERE id = ?");
    $stmtDel->execute([$id]);

    header("Location: gerenciar_modelos.php?msg=deleted");
    exit;
}

// Busca todos os modelos (resto do código continua igual...)
$modelos = $pdo->query("SELECT * FROM templates ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Modelos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Cabral Remoções</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav">
                <a class="nav-link" href="index.php">Gerar PDF</a>
                <a class="nav-link" href="admin_templates.php">Criar Novo Modelo</a>
                <a class="nav-link" href="gerenciar_modelos.php">Gerenciar / Editar</a>
                <a class="nav-link" href="historico.php">Histórico</a>
            </div>
        </div>
    </div>
</nav>

    <div class="container">
        <h3 class="mb-4">Modelos Cadastrados</h3>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Modelo excluído com sucesso!</div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome do Modelo</th>
                            <th>Arquivo Base</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($modelos as $m): ?>
                        <tr>
                            <td><?= $m['id'] ?></td>
                            <td><?= $m['nome_modelo'] ?></td>
                            <td><a href="<?= $m['caminho_arquivo'] ?>" target="_blank" class="text-decoration-none">Ver PDF</a></td>
                            <td class="text-end">
                                <a href="editar_modelo.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <a href="gerenciar_modelos.php?excluir_id=<?= $m['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Tem certeza? Isso apagará o histórico vinculado a este modelo.')">
                                    <i class="bi bi-trash"></i> Excluir
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>