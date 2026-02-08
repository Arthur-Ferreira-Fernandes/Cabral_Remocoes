<?php

require 'Scripts/conecta_banco.php';
require 'Scripts/protecao.php';


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Busca caminho para apagar o arquivo físico também (opcional, se quiser manter o arquivo tire isso)
    $stmt = $pdo->prepare("SELECT caminho_pdf_final FROM historico_gerado WHERE id = ?");
    $stmt->execute([$id]);
    $arquivo = $stmt->fetchColumn();

    if ($arquivo && file_exists($arquivo)) {
        unlink($arquivo);
    }

    // Apaga do banco
    $pdo->prepare("DELETE FROM historico_gerado WHERE id = ?")->execute([$id]);
}

header("Location: historico.php");
exit;