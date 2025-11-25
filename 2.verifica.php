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

	$result = [];
	//$transfer = $_POST['transfer'] ?? '';
	$transfers = explode(',', $_POST['transfer']);

	try {
		$conn = getConnection();

		foreach ($transfers as $tranasfer) {


			$stmt = $conn->prepare("SELECT
													CantidadFacturada,cantidad,
													(SELECT [dbo].[Aco_StockporFilial] (cp.CodigoAlternativo,2)) AS Stock,td.IdProducto
													FROM ACO_Transfer_Cabecera tc
													INNER JOIN ACO_Transfer_Detalle td
														ON td.IdTransfer = tc.IdTransfer
													INNER JOIN CabeceraProducto cp
														ON cp.IdProducto = td.IdProducto
													WHERE NumeroTransfer = :transfer
													AND fecha > '20250701'");

			$stmt->bindParam(':transfer', $transfer);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			?>
			<h1>Resultados del Transfer <?= htmlspecialchars($transfer) ?></h1>

			<?php if (!empty($result)): ?>
				<h2>Productos con stock:</h2>
				<table cellpadding="5" cellspacing="0">
					<tr>
						<th>CantidadFacturada</th>
						<th>cantidad</th>
						<th>Stock</th>
						<th>idProducto</th>
					</tr>
					<?php foreach ($result as $row): ?>
						<tr>
							<td><?= htmlspecialchars($row['CantidadFacturada']) ?></td>
							<td><?= htmlspecialchars($row['cantidad']) ?></td>
							<td><?= htmlspecialchars($row['Stock']) ?></td>
							<td><?= htmlspecialchars($row['IdProducto']) ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
				<br>
				<a href="index.php"><button>Volver</button></a>

			<?php elseif ($transfer !== ''): ?>
				<p>No se encontró transfer.</p>
				<br>
				<br>
				<a href="index.php"><button>Volver</button></a>
			<?php endif;
		}

	} catch (PDOException $e) {
		// Manejo de errores
		echo "Error: " . $e->getMessage();
		return false;
	} finally {
			$conn = null;
	}

	?>

	<script>
		const origen = document.getElementById('busca');
		const destino = document.getElementById('verifica');
		origen.addEventListener('input', () => destino.value = origen.value);
	</script>
</body>
</html>