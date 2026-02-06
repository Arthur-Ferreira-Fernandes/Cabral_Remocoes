<?php
require 'Scripts/conecta_banco.php';
require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

$msg = '';
$arquivoGerado = '';
$camposCabecalho = []; 

// 1. Busca modelos
$modelos = $pdo->query("SELECT * FROM templates ORDER BY nome_modelo ASC")->fetchAll();
$idTemplateSelecionado = $_REQUEST['template_id'] ?? null;

// 2. Busca campos dinâmicos do modelo selecionado
if ($idTemplateSelecionado) {
    $stmt = $pdo->prepare("SELECT * FROM coordenadas_template WHERE template_id = ?");
    $stmt->execute([$idTemplateSelecionado]);
    $camposCabecalho = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 3. GERAÇÃO DO PDF
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gerar_pdf'])) {

    $total_geral = 0.00;
    
    $stmtTpl = $pdo->prepare("SELECT * FROM templates WHERE id = ?");
    $stmtTpl->execute([$idTemplateSelecionado]);
    $templateInfo = $stmtTpl->fetch();

    $pdf = new Fpdi();
    $pdf->setSourceFile($templateInfo['caminho_arquivo']);
    $tplIdx = $pdf->importPage(1);
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx);
    
    // --- (NOVO) NOME NO TOPO ---
    if (!empty($_POST['cliente_identificacao'])) {
        $pdf->SetFont('Arial', 'B', 14); // Negrito, Tamanho 14
        
        // Define apenas a altura Y (15mm do topo)
        $pdf->SetY(100); 
        
        // Cell(Largura, Altura, Texto, Borda, QuebraLinha, Alinhamento)
        // Largura 0 = Ocupa a linha inteira (permitindo centralizar na página)
        // 'C' = Centralizado
        $pdf->Cell(0, 0, utf8_decode($_POST['cliente_identificacao']), 0, 0, 'C');
    }
    // ---------------------------

    $pdf->SetFont('Arial', '', 12); // Restaura fonte normal

    // (A) PREENCHIMENTO DO CABEÇALHO (Dinâmico)
    foreach ($camposCabecalho as $campo) {
        $inputName = 'campo_dinamico_' . $campo['id'];
        
        if (strtolower($campo['campo_chave']) == 'tabela_inicio') continue;

        if (isset($_POST[$inputName])) {
            $valor = utf8_decode($_POST[$inputName]);
            $pdf->SetXY($campo['pos_x'], $campo['pos_y']);
            
            if ($campo['largura'] > 0) {
                $pdf->MultiCell($campo['largura'], 6, $valor);
            } else {
                $pdf->Write(0, $valor);
            }
        }
    }

    // (B) TABELA DE PRODUTOS
    $produtos = $_POST['itens'] ?? [];
    
    if (!empty($produtos)) {
        
        // Lógica do Y Dinâmico (Procura campo 'tabela_inicio')
        $y_atual = 115; // Padrão
        foreach ($camposCabecalho as $campo) {
            if (strtolower($campo['campo_chave']) == 'tabela_inicio') {
                $y_atual = $campo['pos_y'];
                break;
            }
        }

        $pdf->SetFont('Arial', 'B', 10);

        // Cabeçalho da Tabela
        $pdf->SetXY(10, $y_atual);  $pdf->Cell(15, 5, 'ITEM', 0, 0);
        $pdf->SetXY(25, $y_atual);  $pdf->Cell(90, 5, utf8_decode('DESCRIÇÃO'), 0, 0);
        $pdf->SetXY(115, $y_atual); $pdf->Cell(20, 5, 'QTD', 0, 0, 'C');
        $pdf->SetXY(138, $y_atual); $pdf->Cell(20, 5, 'UNID', 0, 0, 'C');
        $pdf->SetXY(158, $y_atual); $pdf->Cell(25, 5, utf8_decode('PREÇO'), 0, 0, 'R');
        $pdf->SetXY(182, $y_atual); $pdf->Cell(25, 5, 'TOTAL', 0, 0, 'R');

        $y_atual += 8;
        $pdf->SetFont('Arial', '', 10);
        
        $contador = 1;

        foreach ($produtos as $prod) {
            if ($y_atual > 260) { 
                $pdf->AddPage(); 
                $pdf->useTemplate($tplIdx); 
                // Repete nome no topo da pag 2
                if (!empty($_POST['cliente_identificacao'])) {
                    $pdf->SetFont('Arial', 'B', 14);
                    $pdf->SetY(15); 
                    $pdf->Cell(0, 0, utf8_decode($_POST['cliente_identificacao']), 0, 0, 'C');
                    $pdf->SetFont('Arial', '', 10);
                }
                $y_atual = 40; 
            }

            // --- CÁLCULO FINANCEIRO CORRIGIDO ---
            // 1. QTD: Tenta pegar numero float, se falhar pega 1
            $qtd_limpa = str_replace(',', '.', $prod['unidade']);
            $qtd_num = floatval(preg_replace('/[^0-9.]/', '', $qtd_limpa));
            if($qtd_num <= 0) $qtd_num = 1; // Fallback se escrever "Serviço único"

            // 2. PREÇO: Remove ponto de milhar, troca vírgula por ponto
            // Ex: "1.250,50" -> "1250,50" -> "1250.50"
            $preco_limpo = str_replace('.', '', $prod['preco']); // Tira ponto
            $preco_limpo = str_replace(',', '.', $preco_limpo); // Troca vírgula
            $preco = floatval($preco_limpo);

            $subtotal = $qtd_num * $preco;
            $total_geral += $subtotal; // Soma ao total global

            // --- ESCREVE NO PDF ---
            $pdf->SetXY(10, $y_atual); $pdf->Cell(15, 6, str_pad($contador, 2, '0', STR_PAD_LEFT), 0, 0);
            
            $pdf->SetXY(25, $y_atual);
            $y_antes = $pdf->GetY();
            $pdf->MultiCell(85, 5, utf8_decode($prod['nome']), 0, 'L');
            $altura = max($pdf->GetY() - $y_antes, 6);

            $pdf->SetXY(115, $y_atual); $pdf->Cell(20, 6, utf8_decode($prod['qtd']), 0, 0, 'C');
            $pdf->SetXY(138, $y_atual); $pdf->Cell(20, 6, utf8_decode($prod['unidade']), 0, 0, 'C');
            $pdf->SetXY(158, $y_atual); $pdf->Cell(25, 6, number_format($preco, 2, ',', '.'), 0, 0, 'R');
            $pdf->SetXY(182, $y_atual); $pdf->Cell(25, 6, number_format($subtotal, 2, ',', '.'), 0, 0, 'R');

            $y_atual += ($altura + 2);
            $contador++;
        }
        
        // Total
        $y_atual += 5;
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY(100, $y_atual);
        $pdf->MultiCell(107, 8, utf8_decode("VALOR TOTAL: R$ " . number_format($total_geral, 2, ',', '.')), 0, 'R');
    }

    // 4. Salvar Arquivo
    $pastaGerados = 'arquivos/gerados/';
    if (!is_dir($pastaGerados)) mkdir($pastaGerados, 0777, true);
    $nomeArquivo = $pastaGerados . 'doc_' . time() . '.pdf';
    $pdf->Output('F', $nomeArquivo);

    // 5. Salvar no Histórico (USANDO O NOVO CAMPO DO TOPO)
    $nomeClienteHistorico = $_POST['cliente_identificacao'] ?? 'Sem Identificação';
    
    $sqlInsert = "INSERT INTO historico_gerado (template_id, caminho_pdf_final, nome_cliente, valor_total) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sqlInsert)->execute([
        $idTemplateSelecionado, 
        $nomeArquivo, 
        $nomeClienteHistorico, 
        $total_geral
    ]);

    $arquivoGerado = $nomeArquivo;
    $msg = "Documento gerado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerador de PDF - Cabral Remoções</title>
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
                    <a class="nav-link active" href="index.php">Gerar PDF</a>
                    <a class="nav-link" href="admin_templates.php">Criar Novo Modelo</a>
                    <a class="nav-link" href="gerenciar_modelos.php">Gerenciar / Editar</a>
                    <a class="nav-link" href="historico.php">Histórico</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if($arquivoGerado): ?>
            <div class="alert alert-success d-flex justify-content-between align-items-center">
                <span><?= $msg ?></span>
                <a href="<?= $arquivoGerado ?>" target="_blank" class="btn btn-success"><i class="bi bi-download"></i> Baixar PDF</a>
            </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-white">
                <h4 class="mb-0">Preencher Documento</h4>
            </div>
            <div class="card-body">
                
                <form method="GET" action="index.php" class="mb-4">
                    <label class="form-label fw-bold">1. Selecione o Modelo</label>
                    <select name="template_id" class="form-select form-select-lg" onchange="this.form.submit()">
                        <option value="">Selecione...</option>
                        <?php foreach($modelos as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($idTemplateSelecionado == $m['id']) ? 'selected' : '' ?>>
                                <?= $m['nome_modelo'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if($idTemplateSelecionado): ?>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="template_id" value="<?= $idTemplateSelecionado ?>">
                        <input type="hidden" name="gerar_pdf" value="1">

                        <div class="p-3 bg-light border rounded mb-4">
                            <label class="form-label fw-bold text-primary mb-1">
                                <i class="bi bi-person-badge"></i> Nome do Cliente / Identificação do Arquivo
                            </label>
                            <input type="text" 
                                   name="cliente_identificacao" 
                                   id="inputClientePrincipal"
                                   class="form-control form-control-lg" 
                                   placeholder="Ex: João Silva - Orçamento Ambulância" 
                                   required 
                                   oninput="copiarParaCamposPDF(this.value)">
                            <div class="form-text">Este nome será salvo no histórico.</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-secondary mb-0">Itens do Serviço</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProduto()">
                                <i class="bi bi-plus-lg"></i> Adicionar Item
                            </button>
                        </div>

                        <table class="table table-bordered table-striped" id="tabela-itens">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Descrição</th>
                                    <th width="15%">Qtd/Período</th>
                                    <th width="15%">Unidade</th>
                                    <th width="20%">Preço (R$)</th>
                                    <th width="10%"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        <div class="d-grid gap-2 mt-5">
                            <button type="submit" class="btn btn-success btn-lg">Gerar Documento</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Função inteligente: Copia o nome do cliente principal para campos do PDF que pareçam ser de Nome
        function copiarParaCamposPDF(valor) {
            const campos = document.querySelectorAll('.campo-dinamico');
            campos.forEach(input => {
                const label = input.getAttribute('data-label');
                // Se o campo do PDF tiver "nome" ou "cliente" no rótulo, copiamos automaticamente
                if (label.includes('nome') || label.includes('cliente')) {
                    input.value = valor;
                }
            });
        }

        let itemIndex = 0;
        function addProduto() {
            const tbody = document.querySelector('#tabela-itens tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="itens[${itemIndex}][nome]" class="form-control" placeholder = "Ex: Ambulância Básica"></td>
                <td><input type="text" name="itens[${itemIndex}][qtd]" class="form-control text-center" placeholder="Ex: 30 Dias" ></td>
                <td><input type="text" name="itens[${itemIndex}][unidade]" class="form-control text-center" placeholder="4 Saidas"></td>
                <td><input type="text" name="itens[${itemIndex}][preco]" class="form-control text-end" onkeyup="mascaraMoeda(this)" placeholder = "R$250,00"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
            itemIndex++;
        }
        function mascaraMoeda(i) {
            let v = i.value.replace(/\D/g,'');
            v = (v/100).toFixed(2) + '';
            v = v.replace(".", ",");
            v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            i.value = v;
        }
        window.onload = function() { addProduto(); }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>