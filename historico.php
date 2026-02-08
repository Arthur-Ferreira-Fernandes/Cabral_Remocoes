<?php
// historico.php
require 'Scripts/conecta_banco.php';
require 'Scripts/protecao.php';

// --- CONFIGURAÇÃO DOS FILTROS ---
$busca     = $_GET['busca'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim    = $_GET['data_fim'] ?? '';

// --- QUERY ATUALIZADA ---
// Busca tanto na tabela de templates (PDF) quanto na de contratos (Word)
$sql = "SELECT h.*, 
               t.nome_modelo AS nome_pdf, 
               c.nome_modelo AS nome_contrato
        FROM historico_gerado h 
        LEFT JOIN templates t ON h.template_id = t.id 
        LEFT JOIN contratos_templates c ON h.contrato_id = c.id
        WHERE 1=1";

$params = [];

if (!empty($busca)) {
    // Filtra pelo nome do cliente OU nome do modelo (seja PDF ou Contrato)
    $sql .= " AND (h.nome_cliente LIKE ? OR t.nome_modelo LIKE ? OR c.nome_modelo LIKE ?)";
    $params[] = "%$busca%";
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

// Executa a busca
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historico = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Arquivos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Histórico de Arquivos Gerados</h5>
            </div>
            <div class="card-body">
                
                <form method="GET" class="row g-2 mb-4 border p-3 rounded bg-white shadow-sm">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Buscar (Cliente ou Modelo)</label>
                        <input type="text" name="busca" class="form-control form-control-sm" value="<?= htmlspecialchars($busca) ?>" placeholder="Digite para buscar...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?= $dataInicio ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control form-control-sm" value="<?= $dataFim ?>">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Filtrar Resultados">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        
                        <?php if(!empty($busca) || !empty($dataInicio) || !empty($dataFim)): ?>
                            <a href="historico.php" class="btn btn-outline-secondary btn-sm" title="Limpar Filtros">
                                <i class="bi bi-x-lg"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Modelo Utilizado</th>
                                <th>Valor / Tipo</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($historico) > 0): ?>
                                <?php foreach($historico as $h): ?>
                                    
                                    <?php 
                                        // Lógica para decidir o nome e se é contrato
                                        $isContrato = !empty($h['contrato_id']);
                                        $nomeModelo = $isContrato ? $h['nome_contrato'] : $h['nome_pdf'];
                                        
                                        if (empty($nomeModelo)) $nomeModelo = 'Modelo Excluído';
                                    ?>

                                <tr>
                                    <td><?= $h['id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                                    <td class="fw-bold"><?= $h['nome_cliente'] ?></td>
                                    
                                    <td>
                                        <span class="badge <?= $isContrato ? 'bg-info text-dark' : 'bg-secondary' ?>">
                                            <?= $nomeModelo ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if($isContrato): ?>
                                            <small class="text-muted">Documento</small>
                                        <?php else: ?>
                                            <span class="text-success fw-bold">
                                                R$ <?= number_format($h['valor_total'], 2, ',', '.') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">
                                        <?php if(file_exists($h['caminho_pdf_final'])): ?>
                                            <a href="<?= $h['caminho_pdf_final'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= $h['caminho_pdf_final'] ?>" download class="btn btn-sm btn-outline-secondary" title="Baixar">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Arquivo Perdido</span>
                                        <?php endif; ?>

                                        <a href="delete_historico.php?id=<?= $h['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja apagar este registro?')" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                        Nenhum registro encontrado com estes filtros.
                                    </td>
                                </tr>
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