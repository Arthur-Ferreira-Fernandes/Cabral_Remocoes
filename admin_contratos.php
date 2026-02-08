<?php
// admin_contratos.php
require 'Scripts/conecta_banco.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome_modelo'];
    $arquivo = $_FILES['arquivo_docx'];
    
    // Verifica se é DOCX
    $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    
    if ($ext == 'docx') {
        $pasta = 'arquivos/contratos_base/';
        if (!is_dir($pasta)) mkdir($pasta, 0777, true);
        
        $novoNome = $pasta . uniqid('contrato_') . '.docx';
        
        if (move_uploaded_file($arquivo['tmp_name'], $novoNome)) {
            
            // 1. Salva o Modelo
            $stmt = $pdo->prepare("INSERT INTO contratos_templates (nome_modelo, caminho_arquivo) VALUES (?, ?)");
            $stmt->execute([$nome, $novoNome]);
            $idContrato = $pdo->lastInsertId();

            // 2. Salva os Campos
            if (isset($_POST['variaveis']) && isset($_POST['labels'])) {
                $variaveis = $_POST['variaveis']; 
                $labels    = $_POST['labels'];
                
                $stmtCampo = $pdo->prepare("INSERT INTO contratos_campos (contrato_id, variavel, label) VALUES (?, ?, ?)");
                
                for ($i = 0; $i < count($variaveis); $i++) {
                    $var = trim($variaveis[$i]);
                    $lbl = trim($labels[$i]);

                    if (!empty($var) && !empty($lbl)) {
                        // Limpa formatação extra
                        $varLimpa = str_replace(['${', '}', ' '], '', $var);
                        $stmtCampo->execute([$idContrato, $varLimpa, $lbl]);
                    }
                }
            }
            
            $msg = "Modelo cadastrado com sucesso! ID: " . $idContrato;
        } else {
            $msg = "Erro ao salvar o arquivo.";
        }
    } else {
        $msg = "Envie apenas arquivos .docx";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Modelo de Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="card shadow mb-5">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-word"></i> Novo Modelo de Contrato</h5>
            </div>
            <div class="card-body">
                <?php if($msg): ?>
                    <div class="alert alert-info"><?= $msg ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="fw-bold">Nome do Modelo</label>
                            <input type="text" name="nome_modelo" class="form-control" placeholder="Ex: Contrato de Prestação de Serviços" required>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Arquivo Word (.docx)</label>
                            <input type="file" name="arquivo_docx" class="form-control" accept=".docx" required>
                        </div>
                    </div>

                    <hr>
                    <h5 class="text-secondary mb-3">Variáveis do Contrato</h5>
                    <p class="small text-muted">Defina quais campos do Word devem ser preenchidos.</p>
                    
                    <div class="card bg-light border-primary mb-3">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <label class="small fw-bold text-primary mb-1">Variável no Word</label>
                                    <input type="text" name="variaveis[]" class="form-control fw-bold" value="nome_cliente" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="small fw-bold text-primary mb-1">Etiqueta na Tela</label>
                                    <input type="text" name="labels[]" class="form-control fw-bold" value="Nome do Cliente" required>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="badge bg-primary mt-3">Padrão</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="lista-campos"></div>
                    
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-4" onclick="addCampo()">
                        <i class="bi bi-plus-circle"></i> Adicionar Outra Variável
                    </button>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Salvar Modelo</button>
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
            <input type="text" name="variaveis[]" class="form-control" placeholder="Variável (ex: valor_total)" required>
        </div>
        <div class="col-md-5">
            <input type="text" name="labels[]" class="form-control" placeholder="Etiqueta (ex: Valor R$)" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger w-100" onclick="this.parentElement.parentElement.remove()"><i class="bi bi-trash"></i></button>
        </div>
    `;
    document.getElementById('lista-campos').appendChild(div);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>