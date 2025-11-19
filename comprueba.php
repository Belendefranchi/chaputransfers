<?php
require_once 'db.php';

$transfer = $_POST['transfer'] ?? '';
$idProductos = explode(',', $_POST['idProductos']);
$idProductos = array_map('intval', $idProductos);


if ($transfer !== '') {
	try {

		$placeholders = implode(',', array_fill(0, count($idProductos), '?'));

		$conn = getConnection();

		$sql = "SELECT cantidad, cantidadFacturada, IdProducto, Descripcion
						FROM ACO_Transfer_Cabecera tc
						INNER JOIN ACO_Transfer_Detalle td on td.IdTransfer = tc.IdTransfer
						WHERE NumeroTransfer = ?
						AND fecha > '20250701'
						AND td.IdProducto IN ($placeholders)";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array_merge([$transfer], $idProductos));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

	<?php if (!empty($result)): ?>
    <h2>Productos con faltante de stock:</h2>
		<table cellpadding="5" cellspacing="0">
			<tr>
				<th>Cantidad</th>
				<th>Cantidad Facturada</th>
				<th>IdProducto</th>
				<th>Descripción</th>
			</tr>
			<?php foreach ($result as $row): ?>
				<tr>
					<td><?= htmlspecialchars($row['cantidad']) ?></td>
					<td><?= htmlspecialchars($row['cantidadFacturada']) ?></td>
					<td><?= htmlspecialchars($row['IdProducto']) ?></td>
					<td><?= htmlspecialchars($row['Descripcion']) ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
  <?php endif; ?>

  <br>
  <a href="index.php"><button>Volver</button></a>

</body>
</html>
