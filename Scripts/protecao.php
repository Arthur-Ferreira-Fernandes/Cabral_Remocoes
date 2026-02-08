<?php
// Scripts/protecao.php

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    // Se não tiver sessão, mata o script e manda pro login
    header("Location: login.php");
    exit;
}
?>