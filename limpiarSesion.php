<?php
session_start();
$_SESSION["idProductos"] = [];
header("Location: index.php");
exit();