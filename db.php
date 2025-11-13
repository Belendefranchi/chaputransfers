<?php
function getConnection() {
  $serverName = '192.168.10.12';
  $database = 'ACOFAR';
  $username = 'first';
  $password = 'pramparo';

  
  try {
      $dsn = "sqlsrv:Server=$serverName,7015;Database=$database";  // TCP/IP con puerto explícito
      $conn = new PDO($dsn, $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $conn;
    } catch (PDOException $e) {
      $lastError = $e->getMessage();
    }
  

  // Si ninguna conexión funcionó, mostramos error
  die("Error de conexión: $lastError");
}