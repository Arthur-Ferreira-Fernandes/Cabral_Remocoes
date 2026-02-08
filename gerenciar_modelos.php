<?php
// gerenciar_modelos.php
require 'Scripts/conecta_banco.php';
require 'Scripts/protecao.php';

// --- Lógica de Exclusão (PDF - ORÇAMENTOS) ---
if (isset($_GET['excluir_pdf_id'])) {
    $id = $_GET['excluir_pdf_id'];
    
    // Busca arquivo
    $stmt = $pdo->prepare("SELECT caminho_arquivo FROM templates WHERE id = ?");
    $stmt->execute([$id]);
    $modelo = $stmt->fetch();

    if ($modelo && file_exists($modelo['caminho_arquivo'])) {
        unlink($modelo['caminho_arquivo']);
    }

    // Limpa banco
    $pdo->prepare("DELETE FROM historico_gerado WHERE template_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM coordenadas_template WHERE template_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM templates WHERE id = ?")->execute([$id]);

    header("Location: gerenciar_modelos.php?msg=deleted");
    exit;
}

// --- Lógica de Exclusão (WORD - CONTRATOS) ---
if (isset($_GET['excluir_contrato_id'])) {
    $id = $_GET['excluir_contrato_id'];
    
    // Busca arquivo
    $stmt = $pdo->prepare("SELECT caminho_arquivo FROM contratos_templates WHERE id = ?");
    $stmt->execute([$id]);
    $modelo = $stmt->fetch();

    if ($modelo && file_exists($modelo['caminho_arquivo'])) {
        unlink($modelo['caminho_arquivo']);
    }

    // Limpa banco
    $pdo->prepare("DELETE FROM historico_gerado WHERE contrato_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM contratos_campos WHERE contrato_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM contratos_templates WHERE id = ?")->execute([$id]);

    header("Location: gerenciar_modelos.php?msg=deleted");
    exit;
}

// --- BUSCAS ---
$modelosPDF = $pdo->query("SELECT * FROM templates ORDER BY id DESC")->fetchAll();
$modelosContrato = $pdo->query("SELECT * FROM contratos_templates ORDER BY id DESC")->fetchAll();
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

    <?php include 'navbar.php'; ?>

    <div class="container">
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Modelo excluído com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-word"></i> Modelos de Contrato (Word)</h5>
                <a href="admin_contratos.php" class="btn btn-sm btn-light text-primary fw-bold">+ Novo Contrato</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nome do Modelo</th>
                                <th>Arquivo Base</th>
                                <th class="text-end pe-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($modelosContrato) > 0): ?>
                                <?php foreach($modelosContrato as $mc): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= $mc['nome_modelo'] ?></td>
                                    <td>
                                        <a href="<?= $mc['caminho_arquivo'] ?>" class="text-decoration-none small text-muted">
                                            <i class="bi bi-download"></i> Baixar DOCX
                                        </a>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="editar_contrato.php?id=<?= $mc['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="gerenciar_modelos.php?excluir_contrato_id=<?= $mc['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Tem certeza? Isso apagará o histórico deste contrato.')" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-3 text-muted">Nenhum modelo de contrato cadastrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow mb-5">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-pdf"></i> Modelos de Orçamento (PDF)</h5>
                <a href="admin_templates.php" class="btn btn-sm btn-light text-danger fw-bold">+ Novo Orçamento</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nome do Modelo</th>
                                <th>Arquivo Base</th>
                                <th class="text-end pe-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($modelosPDF) > 0): ?>
                                <?php foreach($modelosPDF as $mp): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= $mp['nome_modelo'] ?></td>
                                    <td>
                                        <a href="<?= $mp['caminho_arquivo'] ?>" target="_blank" class="text-decoration-none small text-muted">
                                            <i class="bi bi-eye"></i> Ver PDF
                                        </a>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="editar_modelo.php?id=<?= $mp['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="gerenciar_modelos.php?excluir_pdf_id=<?= $mp['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Tem certeza? Isso apagará o histórico deste orçamento.')" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-3 text-muted">Nenhum modelo de orçamento cadastrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>