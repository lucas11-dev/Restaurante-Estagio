<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['tipo'] == 'cliente') {
        header("Location: cliente/index.php");
    } elseif ($_SESSION['tipo'] == 'garcon') {
        header("Location: garcon/index.php");
    } elseif ($_SESSION['tipo'] == 'admin' || $_SESSION['tipo'] == 'gerente') {
        header("Location: admin/index.php");
    }
} else {
    header("Location: login.php");
}
exit();
?>