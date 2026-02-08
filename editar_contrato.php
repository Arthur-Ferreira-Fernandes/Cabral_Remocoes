<?php
// editar_contrato.php
require 'Scripts/conecta_banco.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: gerenciar_modelos.php");
    exit;
}

$msg = '';

// --- PROCESSAMENTO DO FORMULÁRIO (SALVAR) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeModelo = $_POST['nome_modelo'];
    
    // 1. Atualiza Nome
    $pdo->prepare("UPDATE contratos_templates SET nome_modelo = ? WHERE id = ?")->execute([$nomeModelo, $id]);

    // 2. Se enviou novo arquivo DOCX, substitui
    if (!empty($_FILES['arquivo_docx']['name'])) {
        $arquivo = $_FILES['arquivo_docx'];
        if (pathinfo($arquivo['name'], PATHINFO_EXTENSION) == 'docx') {
            
            // Busca caminho antigo para apagar
            $stmtOld = $pdo->prepare("SELECT caminho_arquivo FROM contratos_templates WHERE id = ?");
            $stmtOld->execute([$id]);
            $pathAntigo = $stmtOld->fetchColumn();
            if ($pathAntigo && file_exists($pathAntigo)) {
                unlink($pathAntigo);
            }

            // Salva novo
            $pasta = 'arquivos/contratos_base/';
            $novoNome = $pasta . uniqid('contrato_edit_') . '.docx';
            move_uploaded_file($arquivo['tmp_name'], $novoNome);

            // Atualiza no banco
            $pdo->prepare("UPDATE contratos_templates SET caminho_arquivo = ? WHERE id = ?")->execute([$novoNome, $id]);
        }
    }

    // 3. Atualiza os Campos (Apaga todos antigos e recria os novos)
    // Isso evita lógica complexa de comparação
    $pdo->prepare("DELETE FROM contratos_campos WHERE contrato_id = ?")->execute([$id]);

    if (isset($_POST['variaveis']) && isset($_POST['labels'])) {
        $variaveis = $_POST['variaveis'];
        $labels    = $_POST['labels'];
        
        $stmtInsert = $pdo->prepare("INSERT INTO contratos_campos (contrato_id, variavel, label) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < count($variaveis); $i++) {
            $var = trim($variaveis[$i]);
            $lbl = trim($labels[$i]);

            if (!empty($var) && !empty($lbl)) {
                $varLimpa = str_replace(['${', '}', ' '], '', $var);
                $stmtInsert->execute([$id, $varLimpa, $lbl]);
            }
        }
    }
    
    $msg = "Contrato atualizado com sucesso!";
}

// --- BUSCA DADOS ATUAIS ---
$stmt = $pdo->prepare("SELECT * FROM contratos_templates WHERE id = ?");
$stmt->execute([$id]);
$modelo = $stmt->fetch();

if (!$modelo) { die("Contrato não encontrado."); }

$stmtCampos = $pdo->prepare("SELECT * FROM contratos_campos WHERE contrato_id = ?");
$stmtCampos->execute([$id]);
$camposAtuais = $stmtCampos->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="card shadow mb-5">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Modelo de Contrato</h5>
            </div>
            <div class="card-body">
                
                <?php if($msg): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $msg ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="fw-bold">Nome do Modelo</label>
                            <input type="text" name="nome_modelo" class="form-control" value="<?= htmlspecialchars($modelo['nome_modelo']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Arquivo Word (Opcional)</label>
                            <input type="file" name="arquivo_docx" class="form-control" accept=".docx">
                            <div class="form-text small">Envie apenas se quiser substituir o arquivo atual.</div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-secondary mb-0">Variáveis do Contrato</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCampo()">
                            <i class="bi bi-plus-lg"></i> Adicionar Variável
                        </button>
                    </div>

                    <div id="lista-campos">
                        <?php foreach($camposAtuais as $c): ?>
                        <div class="row mb-2 align-items-end">
                            <div class="col-md-5">
                                <label class="small text-muted">Variável (Word)</label>
                                <input type="text" name="variaveis[]" class="form-control" value="<?= $c['variavel'] ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label class="small text-muted">Etiqueta (Tela)</label>
                                <input type="text" name="labels[]" class="form-control" value="<?= $c['label'] ?>" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger w-100" onclick="this.parentElement.parentElement.remove()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-grid mt-4 gap-2">
                        <button type="submit" class="btn btn-success btn-lg">Salvar Alterações</button>
                        <a href="gerenciar_modelos.php" class="btn btn-outline-secondary">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function addCampo() {
    const div = document.createElement('div');
    div.className = 'row mb-2 align-items-end';
    div.innerHTML = `
        <div class="col-md-5">
            <input type="text" name="variaveis[]" class="form-control" placeholder="Nova Variável" required>
        </div>
        <div class="col-md-5">
            <input type="text" name="labels[]" class="form-control" placeholder="Nova Etiqueta" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger w-100" onclick="this.parentElement.parentElement.remove()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    document.getElementById('lista-campos').appendChild(div);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>