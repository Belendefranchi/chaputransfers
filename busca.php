<?php
require_once 'db.php';

$result = [];
$transfer = $_POST['transfer'] ?? '';

if ($transfer !== '') {
	try {

		$conn = getConnection();

		$sql = "WITH t AS (
							SELECT cp.IdProducto, td.Cantidad
							FROM ACO_Transfer_Cabecera tc WITH (NOLOCK)
							INNER JOIN ACO_Transfer_Detalle td WITH (NOLOCK) ON td.IdTransfer = tc.IdTransfer
							INNER JOIN CabeceraProducto cp WITH (NOLOCK) ON cp.IdProducto = td.IdProducto
							WHERE NumeroTransfer = :transfer AND fecha > '20250701'
						)
						SELECT t.idproducto
						FROM t
						WHERE (
							SELECT SUM(Existencia)
							FROM StockPorDeposito SPD WITH (NOLOCK)
							INNER JOIN Deposito D WITH (NOLOCK) ON SPD.IdDeposito = D.IdDeposito
							WHERE D.HabilitadoFacturacion = 1
							AND D.Interno = 1
							AND D.IdFilial = 2
							AND D.iddeposito NOT IN (41, 42)
							AND spd.IdProducto = t.IdProducto
						) < t.cantidad";

		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':transfer', $transfer);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Extraemos los idProducto
    $idProductos = array_column($result, 'idproducto');

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
				<th>idProducto</th>
			</tr>
			<?php foreach ($result as $row): ?>
				<tr>
					<td><?= htmlspecialchars($row['idproducto']) ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<br>
		<form action="comprueba.php" method="post">
			<input type="hidden" name="transfer" value="<?= htmlspecialchars($transfer) ?>"><br><br>
			<input type="hidden" name="idProductos" value="<?= implode(',', $idProductos) ?>">
			<input type="submit" value="Comprobar Transfer">
		</form>
		
		<br>
		<a href="index.php"><button>Volver</button></a>

	<?php elseif ($transfer !== ''): ?>
		<p>No se encontraron resultados para el transfer ingresado.</p>
		<form action="verifica.php" method="post">
			<input type="hidden" name="transfer" value="<?= htmlspecialchars($transfer) ?>" required><br><br>
			<input type="submit" value="Verificar Transfer">
		</form>
		<br>
		<a href="index.php"><button>Volver</button></a>

	<?php endif; ?>

</body>
</html>
