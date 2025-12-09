<?php
require_once 'db.php';
session_start();

$transfer = $_POST['transfer'] ?? '';
$idProductos = explode(',', $_POST['idProductos']);
$idProductos = array_map('intval', $idProductos);

$_SESSION['idProductos'][$transfer] = $idProductos;

if (isset($_SESSION["idProductos"])) {
	echo '<pre>';
	print_r($_SESSION);
	echo '</pre>';
}

if ($transfer !== '') {
	try {

		$placeholders = implode(',', array_fill(0, count($idProductos), '?'));

		$conn = getConnection();

		$sql = "UPDATE td SET td.cantidadfacturada = td.cantidad
						FROM ACO_Transfer_Cabecera tc
						INNER JOIN ACO_Transfer_Detalle td
							ON td.IdTransfer = tc.IdTransfer
						WHERE NumeroTransfer = ?
							AND fecha > '20250701'
							AND td.IdProducto IN ($placeholders)";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array_merge([$transfer], $idProductos));

		$sql2 = "SELECT cantidad, cantidadFacturada, IdProducto, Descripcion
						FROM ACO_Transfer_Cabecera tc
						INNER JOIN ACO_Transfer_Detalle td
							ON td.IdTransfer = tc.IdTransfer
						WHERE NumeroTransfer = ? 
							AND fecha > '20250701'
							AND td.IdProducto IN ($placeholders)";

		$stmt2 = $conn->prepare($sql2);
		$stmt2->execute(array_merge([$transfer], $idProductos));
		$result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

	} catch (PDOException $e) {
		die("Error en la base de datos: " . $e->getMessage());
	} finally {
		$conn = null;
	}
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Resultados del Transfer</title>
</head>
<body>

	<h1>Resultados del Transfer <?= htmlspecialchars($transfer) ?></h1>

	<?php if (!empty($result2)): ?>
		<h2>Productos procesados:</h2>
		<table cellpadding="5" cellspacing="0">
			<tr>
				<th>IdProducto</th>
				<th>Descripcion</th>
				<th>Cantidad</th>
				<th>CantidadFacturada</th>
			</tr>
			<?php foreach ($result2 as $row): ?>
				<tr>
					<td><?= htmlspecialchars($row['IdProducto']) ?></td>
					<td><?= htmlspecialchars($row['Descripcion']) ?></td>
					<td><?= htmlspecialchars($row['cantidad']) ?></td>
					<td><?= htmlspecialchars($row['cantidadFacturada']) ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<form action="4.devuelve.php" method="post">
			<input type="hidden" name="transfer" value="<?= htmlspecialchars($transfer) ?>"><br><br>
			<input type="hidden" name="idProductos" value="<?= implode(',', $idProductos) ?>">
			<input type="submit" value="Volver Transfer">
		</form>	
		<br>
		<a href="index.php"><button>Volver</button></a>
	<?php endif; ?>
</body>
</html>