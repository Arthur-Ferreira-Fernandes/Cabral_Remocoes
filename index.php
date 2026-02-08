<?php
require 'Scripts/protecao.php';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Principal - Cabral Remoções</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card-menu {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            height: 100%;
        }
        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        }
        .icon-large {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        a.text-decoration-none {
            color: inherit;
        }
    </style>
</head>
<body class="bg-light">

    <?php include 'navbar.php'; // Vamos criar este arquivo no Passo 3 para facilitar ?>

    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="fw-bold text-dark">Painel de Controle</h2>
                <p class="text-muted">Selecione uma opção abaixo para começar</p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            
            <div class="col-md-4 col-lg-3">
                <a href="gerar_orcamento.php" class="text-decoration-none">
                    <div class="card card-menu shadow border-0 text-center py-4">
                        <div class="card-body">
                            <div class="icon-large text-success">
                                <i class="bi bi-calculator"></i>
                            </div>
                            <h5 class="card-title fw-bold">Gerar Orçamento</h5>
                            <p class="card-text small text-muted">Criar orçamentos em PDF</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="gerar_contrato.php" class="text-decoration-none">
                    <div class="card card-menu shadow border-0 text-center py-4">
                        <div class="card-body">
                            <div class="icon-large text-primary">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <h5 class="card-title fw-bold">Gerar Contrato</h5>
                            <p class="card-text small text-muted">Preencher modelos de contrato Word</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
                <a href="historico.php" class="text-decoration-none">
                    <div class="card card-menu shadow border-0 text-center py-4">
                        <div class="card-body">
                            <div class="icon-large text-warning">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <h5 class="card-title fw-bold">Histórico</h5>
                            <p class="card-text small text-muted">Ver orçamentos e contratos gerados</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4 col-lg-3">
    <a href="clientes.php" class="text-decoration-none">
        <div class="card card-menu shadow border-0 text-center py-4">
            <div class="card-body">
                <div class="icon-large text-info">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h5 class="card-title fw-bold">Base de Clientes</h5>
                <p class="card-text small text-muted">Gerenciar contatos e histórico</p>
            </div>
        </div>
    </a>
</div>

        </div>

        <hr class="my-5">
        
        <div class="row mb-3">
            <div class="col-12">
                <h5 class="text-muted fw-bold"><i class="bi bi-gear"></i> Configurações & Modelos</h5>
            </div>
        </div>

        <div class="row g-3">
            
            <div class="col-md-4">
                <a href="admin_templates.php" class="text-decoration-none">
                    <div class="card card-menu shadow-sm border text-center py-3">
                        <div class="card-body">
                            <i class="bi bi-file-pdf fs-2 text-danger mb-2 d-block"></i>
                            <span class="fw-bold">Novo Modelo Orçamento</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="admin_contratos.php" class="text-decoration-none">
                    <div class="card card-menu shadow-sm border text-center py-3">
                        <div class="card-body">
                            <i class="bi bi-file-word fs-2 text-primary mb-2 d-block"></i>
                            <span class="fw-bold">Novo Modelo Contrato</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="gerenciar_modelos.php" class="text-decoration-none">
                    <div class="card card-menu shadow-sm border text-center py-3">
                        <div class="card-body">
                            <i class="bi bi-pencil-square fs-2 text-secondary mb-2 d-block"></i>
                            <span class="fw-bold">Editar/Excluir Modelos</span>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>