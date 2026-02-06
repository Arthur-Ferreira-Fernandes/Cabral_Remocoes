<?php
require 'Scripts/conecta_banco.php';
require 'vendor/autoload.php'; // Carrega o PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// --- CONFIGURAÇÃO DOS FILTROS ---
$busca     = $_GET['busca'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim    = $_GET['data_fim'] ?? '';

// Montagem da Query
$sql = "SELECT h.*, t.nome_modelo 
        FROM historico_gerado h 
        LEFT JOIN templates t ON h.template_id = t.id 
        WHERE 1=1";

$params = [];

if (!empty($busca)) {
    $sql .= " AND (h.nome_cliente LIKE ? OR t.nome_modelo LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if (!empty($dataInicio)) {
    $sql .= " AND DATE(h.created_at) >= ?";
    $params[] = $dataInicio;
}

if (!empty($dataFim)) {
    $sql .= " AND DATE(h.created_at) <= ?";
    $params[] = $dataFim;
}

$sql .= " ORDER BY h.created_at DESC";

// --- EXPORTAÇÃO PARA EXCEL (.XLSX) ---
if (isset($_GET['exportar'])) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cria o Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Cabeçalhos
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Data');
    $sheet->setCellValue('C1', 'Cliente / Identificação');
    $sheet->setCellValue('D1', 'Modelo');
    $sheet->setCellValue('E1', 'Valor Total (R$)');
    $sheet->setCellValue('F1', 'Link do Arquivo');

    // Estilo do Cabeçalho (Negrito)
    $sheet->getStyle('A1:F1')->getFont()->setBold(true);

    // Preenche os dados
    $linha = 2;
    foreach ($resultados as $row) {
        $sheet->setCellValue('A' . $linha, $row['id']);
        $sheet->setCellValue('B' . $linha, date('d/m/Y', strtotime($row['created_at'])));
        $sheet->setCellValue('C' . $linha, $row['nome_cliente']);
        $sheet->setCellValue('D' . $linha, $row['nome_modelo']);
        $sheet->setCellValue('E' . $linha, $row['valor_total']);
        $sheet->setCellValue('F' . $linha, $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $row['caminho_pdf_final']);
        
        // Formata a coluna E como dinheiro
        $sheet->getStyle('E' . $linha)->getNumberFormat()->setFormatCode('#,##0.00');
        
        $linha++;
    }

    // Ajusta largura das colunas automaticamente
    foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Força o Download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="historico_orcamentos.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Busca para exibição na tela
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico - Cabral Remoções</title>
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
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Histórico de Documentos</h3>
            <div>
                <a href="?exportar=1&busca=<?= $busca ?>&data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Baixar Excel (.xlsx)
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Buscar</label>
                        <input type="text" name="busca" class="form-control" value="<?= htmlspecialchars($busca) ?>" placeholder="Cliente ou Modelo...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $dataInicio ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $dataFim ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Filtrar</button>
                        <?php if(!empty($busca) || !empty($dataInicio) || !empty($dataFim)): ?>
                            <a href="historico.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Cliente / Identificação</th>
                                <th>Valor Total</th> <th>Modelo</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($historico) > 0): ?>
                                <?php foreach($historico as $h): ?>
                                <tr>
                                    <td>#<?= $h['id'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($h['created_at'])) ?></td>
                                    <td class="fw-bold"><?= $h['nome_cliente'] ?></td>
                                    
                                    <td class="text-success fw-bold">
                                        R$ <?= number_format($h['valor_total'], 2, ',', '.') ?>
                                    </td>

                                    <td><span class="badge bg-secondary"><?= $h['nome_modelo'] ?? 'Excluído' ?></span></td>
                                    <td class="text-end">
                                        <?php if(file_exists($h['caminho_pdf_final'])): ?>
                                            <a href="<?= $h['caminho_pdf_final'] ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                        <?php endif; ?>
                                        <a href="delete_historico.php?id=<?= $h['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apagar?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Nenhum registro encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>