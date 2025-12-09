<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Resultados del Transfer</title>
</head>
<body>

<?php
require_once 'db.php';
session_start();

if (isset($_SESSION["idProductos"])) {
	echo '<pre>';
	print_r($_SESSION);
	echo '</pre>';
}

$result = [];
$transfers = explode(',', $_POST['transfer']);
$transfers = array_map('intval', $transfers);

if ($transfers !== '') {
	try {

		$conn = getConnection();

		foreach ($transfers as $transfer){

			$sql = "WITH t AS (
								SELECT cp.IdProducto,td.Cantidad
								FROM ACO_Transfer_Cabecera tc
								INNER JOIN ACO_Transfer_Detalle td ON td.IdTransfer = tc.IdTransfer
								INNER JOIN CabeceraProducto cp ON cp.IdProducto = td.IdProducto
								WHERE NumeroTransfer = :transfer AND fecha > '20250701'
							)
							SELECT t.idproducto, t.Cantidad, (SELECT SUM(Existencia) 
								FROM StockPorDeposito SPD 
								INNER JOIN Deposito D ON SPD.IdDeposito = D.IdDeposito
								WHERE (D.HabilitadoFacturacion = 1
									AND D.Interno=1) 
									--AND  D.IdFilial=2
									AND D.iddeposito NOT IN( 41,42 )
									AND spd.IdProducto = t.IdProducto) AS stock
							FROM t
							WHERE (SELECT SUM(Existencia)
								FROM StockPorDeposito SPD 
								INNER JOIN Deposito D ON SPD.IdDeposito = D.IdDeposito
								WHERE (D.HabilitadoFacturacion = 1
									AND D.Interno=1) 
									--AND  D.IdFilial=2
									AND D.iddeposito NOT IN( 41,42 )
									AND spd.IdProducto = t.IdProducto) < t.cantidad";

			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':transfer', $transfer);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Extraemos los idProducto
			$idProductos = array_column($result, 'idproducto');

			if (empty($idProductos)){

			} else {

				$placeholders = implode(',', array_fill(0, count($idProductos), '?'));

				$sql2 = "SELECT cantidad, cantidadFacturada, IdProducto, Descripcion
								FROM ACO_Transfer_Cabecera tc
								INNER JOIN ACO_Transfer_Detalle td on td.IdTransfer = tc.IdTransfer
								WHERE NumeroTransfer = ?
								AND fecha > '20250701'
								AND td.IdProducto IN ($placeholders)";

				$stmt2 = $conn->prepare($sql2);
				$stmt2->execute(array_merge([$transfer], $idProductos));
				$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
			}
			?>

			<h1>Resultados del Transfer <?= htmlspecialchars($transfer) ?></h1>
			<?php if (!empty($result)): ?>
				<h2>Productos con faltante de stock:</h2>
				<table cellpadding="5" cellspacing="0">
					<tr>
						<th>idProducto</th>
						<th>Cantidad</th>
						<th>Stock</th>
					</tr>
					<?php foreach ($result as $row): ?>
						<tr>
							<td><?= htmlspecialchars($row['idproducto']) ?></td>
							<td><?= htmlspecialchars($row['Cantidad']) ?></td>
							<td><?= htmlspecialchars($row['stock']) ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
				<br>
			<?php endif; ?>
			<?php if (!empty($result2)): ?>
				<table cellpadding="5" cellspacing="0">
					<tr>
						<th>Cantidad</th>
						<th>Cantidad Facturada</th>
						<th>IdProducto</th>
						<th>Descripción</th>
					</tr>
					<?php foreach ($result2 as $row2): ?>
						<tr>
							<td><?= htmlspecialchars($row2['cantidad']) ?></td>
							<td><?= htmlspecialchars($row2['cantidadFacturada']) ?></td>
							<td><?= htmlspecialchars($row2['IdProducto']) ?></td>
							<td><?= htmlspecialchars($row2['Descripcion']) ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
				<form action="3.procesa.php" method="post">
					<input type="hidden" name="transfer" value="<?= htmlspecialchars($transfer) ?>"><br><br>
					<input type="hidden" name="idProductos" value="<?= implode(',', $idProductos) ?>">
					<input type="submit" value="Procesar Transfer">
				</form>		
				<br>
			<?php elseif ($transfer !== ''): ?>
				<p>No se encontraron resultados para el transfer ingresado.</p>
				<form action="2.verifica.php" method="post">
					<input type="hidden" name="transfer" value="<?= htmlspecialchars($transfer) ?>" required><br><br>
					<input type="submit" value="Verificar Transfer">
				</form>
				<br>
			<?php endif; 
		}

	} catch (PDOException $e) {
		die("Error en la base de datos: " . $e->getMessage());
	} finally {
    $conn = null;
	}
}
?>
<br>
<a href="index.php"><button>Volver</button></a>

</body>
</html>
