<?php
// Garante que a sessão está iniciada para pegar o nome
if (!isset($_SESSION)) session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-ambulance"></i> Cabral Remoções
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="gerar_orcamento.php">Orçamento</a></li>
                <li class="nav-item"><a class="nav-link" href="gerar_contrato.php">Contrato</a></li>
                <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
                <li class="nav-item"><a class="nav-link" href="historico.php">Histórico</a></li>
            </ul>

            <ul class="navbar-nav align-items-center">
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_templates.php">Novo Modelo Orçamento</a></li>
                        <li><a class="dropdown-item" href="admin_contratos.php">Novo Modelo Contrato</a></li>
                        <li><a class="dropdown-item" href="gerenciar_modelos.php">Gerenciar Modelos</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li><a class="dropdown-item text-primary fw-bold" href="novo_usuario.php"><i class="bi bi-person-badge"></i> Gerenciar Usuários</a></li>

                    </ul>
                </li>

                <li class="nav-item dropdown d-flex align-items-center ms-2">
                    <a class="nav-link dropdown-toggle text-white border rounded px-3 py-1" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= $_SESSION['usuario_nome'] ?? 'Admin' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item" href="alterar_senha.php">
                                <i class="bi bi-key text-primary me-2"></i> Alterar Senha
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item text-danger fw-bold" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>