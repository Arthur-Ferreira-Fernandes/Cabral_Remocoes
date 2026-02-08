<?php
// gerar_contrato.php
require 'Scripts/conecta_banco.php';
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

// --- CONFIGURAÇÃO DOMPDF ---
Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
Settings::setPdfRendererPath(__DIR__ . '/vendor/dompdf/dompdf');

$idSelecionado = $_GET['id_modelo'] ?? null;
$campos = [];
$modelo = null;

// Listas de variáveis AUTOMÁTICAS (que o sistema preenche sozinho)
$autoNome  = ['nome_cliente', 'cliente', 'nome', 'contratante', 'nome_contratante', 'paciente', 'nome_paciente'];
$autoEmail = ['email_cliente', 'email', 'e-mail', 'correio_eletronico'];
$autoTel   = ['telefone_cliente', 'telefone', 'tel', 'whatsapp', 'celular', 'cel', 'fone'];

// Busca Modelos
$modelos = $pdo->query("SELECT * FROM contratos_templates ORDER BY nome_modelo ASC")->fetchAll();

if ($idSelecionado) {
    $stmt = $pdo->prepare("SELECT * FROM contratos_templates WHERE id = ?");
    $stmt->execute([$idSelecionado]);
    $modelo = $stmt->fetch();

    $stmtCampos = $pdo->prepare("SELECT * FROM contratos_campos WHERE contrato_id = ?");
    $stmtCampos->execute([$idSelecionado]);
    $campos = $stmtCampos->fetchAll();
}

// --- PROCESSAMENTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $idSelecionado && $modelo) {
    
    // 1. DADOS DO CLIENTE
    $sysNome = trim($_POST['sistema_nome'] ?? 'Cliente');
    $sysEmail = trim($_POST['sistema_email'] ?? '');
    $sysTel = trim($_POST['sistema_telefone'] ?? '');

    // Salva/Atualiza Cliente
    if (!empty($sysNome)) {
        $stmtCheck = $pdo->prepare("SELECT id FROM clientes WHERE nome = ?");
        $stmtCheck->execute([$sysNome]);
        if (!$stmtCheck->fetch()) {
            $pdo->prepare("INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)")
                ->execute([$sysNome, $sysEmail, $sysTel]);
        }
    }

    // 2. PREENCHIMENTO DO WORD
    $pastaSaida = 'arquivos/gerados/';
    if (!is_dir($pastaSaida)) mkdir($pastaSaida, 0777, true);
    
    $nomeBase = 'Contrato_' . date('Ymd_His') . '_' . uniqid();
    $caminhoDocx = $pastaSaida . $nomeBase . '.docx';
    $caminhoPdf  = $pastaSaida . $nomeBase . '.pdf';

    $templateProcessor = new TemplateProcessor($modelo['caminho_arquivo']);
    
    // A. Substitui variáveis
    foreach ($campos as $campo) {
        $chave = $campo['variavel'];
        $valor = '';

        if (in_array($chave, $autoNome)) {
            $valor = $sysNome;
        } elseif (in_array($chave, $autoEmail)) {
            $valor = $sysEmail;
        } elseif (in_array($chave, $autoTel)) {
            $valor = $sysTel;
        } else {
            $valor = $_POST['cam_' . $chave] ?? '';
        }
        $templateProcessor->setValue($chave, $valor);
    }

    // B. TRAVA DE SEGURANÇA (Garante que nomes padrão sejam substituídos)
    $templateProcessor->setValue('cliente', $sysNome);
    $templateProcessor->setValue('nome_cliente', $sysNome);
    $templateProcessor->setValue('nome', $sysNome);
    $templateProcessor->setValue('contratante', $sysNome);
    
    $templateProcessor->setValue('email', $sysEmail);
    $templateProcessor->setValue('email_cliente', $sysEmail);
    
    $templateProcessor->setValue('telefone', $sysTel);
    $templateProcessor->setValue('tel', $sysTel);
    $templateProcessor->setValue('whatsapp', $sysTel);

    // Salva DOCX
    $templateProcessor->saveAs($caminhoDocx);

    // 3. CONVERSÃO PDF
    try {
        $phpWord = IOFactory::load($caminhoDocx); 
        $xmlWriter = IOFactory::createWriter($phpWord, 'PDF');
        $xmlWriter->save($caminhoPdf);
        $arquivoFinal = $caminhoPdf;
    } catch (Exception $e) {
        $arquivoFinal = $caminhoDocx;
    }

    // 4. HISTÓRICO
    $sqlInsert = "INSERT INTO historico_gerado (contrato_id, template_id, nome_cliente, caminho_pdf_final, created_at, valor_total) 
                  VALUES (?, NULL, ?, ?, NOW(), 0)";
    $pdo->prepare($sqlInsert)->execute([$idSelecionado, $sysNome, $arquivoFinal]);

    header("Location: historico.php?msg=sucesso");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerar Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-pen"></i> Gerador de Contratos</h5>
            </div>
            <div class="card-body">
                
                <form method="GET" class="mb-4">
                    <label class="fw-bold">Selecione o Modelo:</label>
                    <select name="id_modelo" class="form-select form-select-lg" onchange="this.form.submit()">
                        <option value="">-- Selecione --</option>
                        <?php foreach($modelos as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $idSelecionado == $m['id'] ? 'selected' : '' ?>>
                                <?= $m['nome_modelo'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if ($modelo): ?>
                    <hr>
                    <form method="POST">
                        
                        <div class="card mb-4 border-primary bg-light">
                            <div class="card-body">
                                <h6 class="text-primary fw-bold mb-3"><i class="bi bi-person-badge"></i> Dados do Cliente (Obrigatório)</h6>
                                <div class="row">
                                    <div class="col-md-5 mb-2">
                                        <label class="small fw-bold">Nome Completo *</label>
                                        <input type="text" name="sistema_nome" class="form-control" required placeholder="Nome do Cliente">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="small fw-bold">Email *</label>
                                        <input type="email" name="sistema_email" class="form-control" required placeholder="cliente@email.com">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small fw-bold">Telefone/Whats *</label>
                                        <input type="text" name="sistema_telefone" class="form-control" required placeholder="(00) 00000-0000">
                                    </div>
                                </div>
                                <div class="form-text text-danger small">
                                    * Todos os campos acima são obrigatórios para o cadastro.
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3"><i class="bi bi-file-text"></i> Detalhes do Contrato</h6>
                        
                        <?php if(empty($campos)): ?>
                            <div class="alert alert-warning">Sem variáveis configuradas neste modelo.</div>
                        <?php else: ?>
                            <div class="row">
                                <?php 
                                $camposVisiveis = 0;
                                foreach($campos as $c): 
                                    // Pula campos automáticos
                                    if (in_array($c['variavel'], $autoNome) || in_array($c['variavel'], $autoEmail) || in_array($c['variavel'], $autoTel)) {
                                        continue; 
                                    }
                                    $camposVisiveis++;
                                ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small"><?= $c['label'] ?> *</label>
                                    <input type="text" name="cam_<?= $c['variavel'] ?>" class="form-control" required>
                                    <div class="form-text" style="font-size: 0.75rem">Variável: <code>${<?= $c['variavel'] ?>}</code></div>
                                </div>
                                <?php endforeach; ?>

                                <?php if($camposVisiveis == 0): ?>
                                    <div class="col-12">
                                        <div class="alert alert-success border-0">
                                            <i class="bi bi-check-circle"></i> Tudo pronto! As variáveis serão preenchidas automaticamente com os dados acima.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg">Gerar Contrato</button>
                        </div>
                    </form>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>