<?php
require 'Scripts/conecta_banco.php';
require 'Scripts/protecao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome_modelo'];
    $arquivo = $_FILES['arquivo_pdf'];

    // 1. Upload do Arquivo
    $pastaDestino = 'arquivos/templates/';
    if (!is_dir($pastaDestino)) mkdir($pastaDestino, 0777, true);
    
    $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    
    if ($ext == 'pdf') {
        $novoNome = $pastaDestino . 'template_' . uniqid() . '.pdf';
        move_uploaded_file($arquivo['tmp_name'], $novoNome);

        // 2. Salva o Modelo (Pai)
        $stmt = $pdo->prepare("INSERT INTO templates (nome_modelo, caminho_arquivo) VALUES (?, ?)");
        $stmt->execute([$nome, $novoNome]);
        $idTemplate = $pdo->lastInsertId();

        // 3. Salva os Campos do Cabeçalho (Filhos)
        // O Index.php vai ler isso aqui para saber o que perguntar e onde imprimir
        if (isset($_POST['campos']) && is_array($_POST['campos'])) {
            $sqlCoord = "INSERT INTO coordenadas_template (template_id, campo_chave, pos_x, pos_y, largura) VALUES (?, ?, ?, ?, ?)";
            $stmtCoord = $pdo->prepare($sqlCoord);

            foreach ($_POST['campos'] as $campo) {
                // campo_chave = O rótulo que aparecerá no formulário (ex: "Nome do Cliente")
                $stmtCoord->execute([
                    $idTemplate, 
                    $campo['nome'], 
                    $campo['x'], 
                    $campo['y'], 
                    $campo['w']
                ]);
            }
        }

        $mensagem = "<div class='alert alert-success'>Modelo criado e cabeçalho configurado!</div>";
    } else {
        $mensagem = "<div class='alert alert-danger'>Apenas arquivos PDF são permitidos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Modelo - Cabral Remoções</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">1. Configurar Novo Modelo</h4>
                    </div>
                    <div class="card-body">
                        <?= $mensagem ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nome do Modelo</label>
                                    <input type="text" name="nome_modelo" class="form-control" placeholder="Ex: Orçamento Padrão" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Arquivo PDF (Fundo)</label>
                                    <input type="file" name="arquivo_pdf" class="form-control" accept="application/pdf" required>
                                </div>
                            </div>

                            <hr>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-secondary mb-0">2. Definir Campos do Cabeçalho</h5>
                                <button type="button" class="btn btn-success btn-sm" onclick="adicionarCampo()">
                                    <i class="bi bi-plus-circle"></i> Adicionar Campo
                                </button>
                            </div>
                            
                            <p class="small text-muted">Adicione aqui tudo que fica no topo do PDF (Cliente, Data, Endereço, etc). A tabela de produtos é automática.</p>

                            <div id="lista-campos">
                                </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Salvar Modelo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let contador = 0;

        function adicionarCampo() {
            contador++;
            const div = document.createElement('div');
            div.className = 'card mb-2 bg-light border';
            div.id = 'linha-' + contador;
            
            div.innerHTML = `
                <div class="card-body p-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="small fw-bold">Nome do Campo (Rótulo)</label>
                            <input type="text" name="campos[${contador}][nome]" class="form-control form-control-sm" placeholder="Ex: Nome do Cliente" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Pos X (mm)</label>
                            <input type="number" step="0.1" name="campos[${contador}][x]" class="form-control form-control-sm" placeholder="Horiz." required>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Pos Y (mm)</label>
                            <input type="number" step="0.1" name="campos[${contador}][y]" class="form-control form-control-sm" placeholder="Vert." required>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Largura (mm)</label>
                            <input type="number" step="0.1" name="campos[${contador}][w]" class="form-control form-control-sm" value="0" placeholder="Opcional">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="document.getElementById('linha-${contador}').remove()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('lista-campos').appendChild(div);
        }

        // Adiciona um campo padrão ao carregar
        window.onload = function() { adicionarCampo(); };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>