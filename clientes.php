<?php
// clientes.php
require 'Scripts/conecta_banco.php';
require 'Scripts/protecao.php';
include 'navbar.php';

// --- CONFIGURAÇÃO DO SEU GRUPO ---
// COLOQUE AQUI O LINK DO SEU GRUPO DO WHATSAPP
$linkDoGrupo = "https://chat.whatsapp.com/BkE3rCoDe7R7xqtMQ5KvNW"; 

// Lógica de Exclusão
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $pdo->prepare("DELETE FROM clientes WHERE id = ?")->execute([$id]);
    header("Location: clientes.php?msg=deleted");
    exit;
}

// Busca
$busca = $_GET['busca'] ?? '';
$sql = "SELECT * FROM clientes WHERE 1=1";
$params = [];
if ($busca) {
    $sql .= " AND (nome LIKE ? OR email LIKE ? OR telefone LIKE ?)";
    $params = ["%$busca%", "%$busca%", "%$busca%"];
}
$sql .= " ORDER BY nome ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Base de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php if(isset($_GET['msg'])): ?>
    <div class="container mt-3">
        <?php if($_GET['msg'] == 'sucesso_cadastro'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> Cliente cadastrado com sucesso!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'erro_existe'): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> Já existe um cliente com este nome.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-trash"></i> Cliente removido.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-people-fill text-primary"></i> Base de Clientes</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoCliente">
            <i class="bi bi-plus-lg"></i> Novo Cliente
        </button>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="busca" class="form-control" placeholder="Buscar cliente..." value="<?= htmlspecialchars($busca) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Contato</th>
                            <th>Cadastro</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($clientes as $c): ?>
                        <tr>
                            <td class="fw-bold"><?= $c['nome'] ?></td>
                            <td>
                                <?php if($c['email']): ?><div class="small"><i class="bi bi-envelope"></i> <?= $c['email'] ?></div><?php endif; ?>
                                <?php if($c['telefone']): ?><div class="small text-muted"><i class="bi bi-whatsapp"></i> <?= $c['telefone'] ?></div><?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                            <td class="text-end">
                                
                                <?php 
                                    $telLimpo = preg_replace('/[^0-9]/', '', $c['telefone']);
                                    if(!empty($telLimpo)):
                                        $msgZap = "Olá " . $c['nome'] . "! Entre no nosso grupo de clientes: " . $linkDoGrupo;
                                        $linkApiZap = "https://wa.me/55" . $telLimpo . "?text=" . urlencode($msgZap);
                                ?>
                                    <a href="<?= $linkApiZap ?>" target="_blank" class="btn btn-sm btn-success" title="Enviar Convite">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="historico.php?busca=<?= urlencode($c['nome']) ?>" class="btn btn-sm btn-outline-info" title="Histórico">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <a href="clientes.php?delete_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir?')" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($clientes) == 0): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum cliente encontrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovoCliente" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="salvar_cliente_manual.php">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Cadastrar Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nome Completo *</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Telefone / WhatsApp</label>
                        <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar Cliente</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalZapAuto" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-whatsapp"></i> Cliente Cadastrado!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <h4 id="zapNomeCliente" class="fw-bold mb-3"></h4>
                <p class="text-muted">Deseja enviar o convite do grupo agora?</p>
                
                <a id="btnZapLink" href="#" target="_blank" class="btn btn-success btn-lg w-100 shadow">
                    <i class="bi bi-send-fill"></i> ENVIAR CONVITE AGORA
                </a>
                
                <button type="button" class="btn btn-link text-muted mt-2" data-bs-dismiss="modal">Agora não</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Verifica se a URL tem os parâmetros de novo cliente (new_tel e new_nome)
    const urlParams = new URLSearchParams(window.location.search);
    const newTel = urlParams.get('new_tel');
    const newNome = urlParams.get('new_nome');

    if (newTel && newNome) {
        // Limpa telefone
        let telLimpo = newTel.replace(/\D/g,'');
        
        if (telLimpo.length > 8) {
            // Cria o link
            let linkGrupo = "<?= $linkDoGrupo ?>";
            let msg = "Olá " + newNome + "! Segue o convite para nosso grupo: " + linkGrupo;
            let linkFinal = "https://wa.me/55" + telLimpo + "?text=" + encodeURIComponent(msg);

            // Preenche o modal
            document.getElementById('zapNomeCliente').textContent = newNome;
            document.getElementById('btnZapLink').href = linkFinal;

            // Abre o modal
            var myModal = new bootstrap.Modal(document.getElementById('modalZapAuto'));
            myModal.show();
        }
    }
});
</script>
</body>
</html>