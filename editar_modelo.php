<?php
require 'Scripts/conecta_banco.php';
require 'Scripts/protecao.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: gerenciar_modelos.php"); exit; }

$msg = '';

// --- Lógica de Exclusão de CAMPO Específico ---
if (isset($_GET['delete_campo'])) {
    $idCampo = $_GET['delete_campo'];
    $pdo->prepare("DELETE FROM coordenadas_template WHERE id = ?")->execute([$idCampo]);
    header("Location: editar_modelo.php?id=$id&msg=campo_deleted");
    exit;
}

// --- Lógica de Atualização (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Atualiza Nome do Modelo
    $nomeModelo = $_POST['nome_modelo'];
    $pdo->prepare("UPDATE templates SET nome_modelo = ? WHERE id = ?")->execute([$nomeModelo, $id]);

    // 2. Se enviou novo PDF, substitui
    if (!empty($_FILES['arquivo_pdf']['name'])) {
        $arquivo = $_FILES['arquivo_pdf'];
        if (pathinfo($arquivo['name'], PATHINFO_EXTENSION) == 'pdf') {
            // Busca caminho antigo para apagar
            $stmtOld = $pdo->prepare("SELECT caminho_arquivo FROM templates WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldPath = $stmtOld->fetchColumn();
            if(file_exists($oldPath)) unlink($oldPath);

            // Salva novo
            $novoCaminho = 'arquivos/templates/template_' . uniqid() . '.pdf';
            move_uploaded_file($arquivo['tmp_name'], $novoCaminho);
            
            $pdo->prepare("UPDATE templates SET caminho_arquivo = ? WHERE id = ?")->execute([$novoCaminho, $id]);
        }
    }

    // 3. Atualiza ou Cria Campos (Header)
    if (isset($_POST['campos'])) {
        foreach ($_POST['campos'] as $key => $dados) {
            $campoId = $dados['id_db'] ?? null;
            
            if ($campoId) {
                // UPDATE existente
                $sql = "UPDATE coordenadas_template SET campo_chave=?, pos_x=?, pos_y=?, largura=? WHERE id=?";
                $pdo->prepare($sql)->execute([$dados['nome'], $dados['x'], $dados['y'], $dados['w'], $campoId]);
            } else {
                // INSERT novo (o usuário clicou em adicionar campo na edição)
                $sql = "INSERT INTO coordenadas_template (template_id, campo_chave, pos_x, pos_y, largura) VALUES (?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$id, $dados['nome'], $dados['x'], $dados['y'], $dados['w']]);
            }
        }
    }

    $msg = "<div class='alert alert-success'>Alterações salvas com sucesso!</div>";
}

// --- Carrega Dados Atuais ---
$modelo = $pdo->prepare("SELECT * FROM templates WHERE id = ?");
$modelo->execute([$id]);
$dadosModelo = $modelo->fetch();

$campos = $pdo->prepare("SELECT * FROM coordenadas_template WHERE template_id = ?");
$campos->execute([$id]);
$listaCampos = $campos->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Modelo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Editar: <?= $dadosModelo['nome_modelo'] ?></h4>
                    </div>
                    <div class="card-body">
                        <?= $msg ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nome do Modelo</label>
                                    <input type="text" name="nome_modelo" class="form-control" value="<?= $dadosModelo['nome_modelo'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Trocar Arquivo PDF (Opcional)</label>
                                    <input type="file" name="arquivo_pdf" class="form-control" accept="application/pdf">
                                    <small class="text-muted">Atual: <a href="<?= $dadosModelo['caminho_arquivo'] ?>" target="_blank">Visualizar</a></small>
                                </div>
                            </div>

                            <hr>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-secondary mb-0">Campos do Cabeçalho</h5>
                                <button type="button" class="btn btn-success btn-sm" onclick="adicionarCampoVisual()">
                                    <i class="bi bi-plus-circle"></i> Adicionar Novo Campo
                                </button>
                            </div>
                            
                            <div id="lista-campos-editor">
                                <?php 
                                    $i = 0;
                                    foreach($listaCampos as $c): 
                                    $i++;
                                ?>
                                <div class="card mb-2 bg-light border">
                                    <div class="card-body p-2">
                                        <div class="row g-2 align-items-end">
                                            <input type="hidden" name="campos[<?= $i ?>][id_db]" value="<?= $c['id'] ?>">
                                            
                                            <div class="col-md-4">
                                                <label class="small fw-bold">Nome do Campo</label>
                                                <input type="text" name="campos[<?= $i ?>][nome]" class="form-control form-control-sm" value="<?= $c['campo_chave'] ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="small">Pos X (mm)</label>
                                                <input type="number" step="0.1" name="campos[<?= $i ?>][x]" class="form-control form-control-sm" value="<?= $c['pos_x'] ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="small">Pos Y (mm)</label>
                                                <input type="number" step="0.1" name="campos[<?= $i ?>][y]" class="form-control form-control-sm" value="<?= $c['pos_y'] ?>" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="small">Largura</label>
                                                <input type="number" step="0.1" name="campos[<?= $i ?>][w]" class="form-control form-control-sm" value="<?= $c['largura'] ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <a href="editar_modelo.php?id=<?= $id ?>&delete_campo=<?= $c['id'] ?>" 
                                                   class="btn btn-danger btn-sm w-100"
                                                   onclick="return confirm('Excluir este campo permanentemente?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-4 d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Começa com um número alto para não conflitar com os índices do PHP
        let novoIndex = 999; 

        function adicionarCampoVisual() {
            novoIndex++;
            const div = document.createElement('div');
            div.className = 'card mb-2 bg-white border border-success'; // Borda verde para destacar que é novo
            
            div.innerHTML = `
                <div class="card-body p-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="small fw-bold text-success">Novo Campo</label>
                            <input type="text" name="campos[${novoIndex}][nome]" class="form-control form-control-sm" placeholder="Ex: CPF" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Pos X</label>
                            <input type="number" step="0.1" name="campos[${novoIndex}][x]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Pos Y</label>
                            <input type="number" step="0.1" name="campos[${novoIndex}][y]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Largura</label>
                            <input type="number" step="0.1" name="campos[${novoIndex}][w]" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.card').remove()">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('lista-campos-editor').appendChild(div);
        }
    </script>
</body>
</html>