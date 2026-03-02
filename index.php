<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style>
		body {
				font-family: Arial, sans-serif;
				max-width: 600px;
				margin: 30px auto;
				padding: 10px;
		}
		h2 { text-align: center; }
	</style>
	<title>ChapuTransfers</title>
</head>
<body>
<?php
	session_start();
/* 	if (!empty($_SESSION["idProductos"])) {
		echo '<pre>';
		print_r($_SESSION);
		echo '</pre>'; */


if (!empty($_SESSION['idProductos'])) {

    echo "<ul>";

    foreach ($_SESSION['idProductos'] as $transfer => $productos) {

        echo "<li>";
        echo "<strong>Transfer:</strong> " . htmlspecialchars($transfer);
				echo "<br>";
        echo "<ul>";

        foreach ($productos as $producto) {
            echo "<li>" . htmlspecialchars($producto) . "</li>";
        }

        echo "</ul>";
        echo "</li>";
				echo "<br>";
    }

    echo "</ul>";

?>

	<label>
			<input type="checkbox" id="confirmar">
			Confirmar limpieza de sesión
	</label>

	<br><br>

	<a href="limpiarSesion.php">
			<button id="btnLimpiar" disabled>Limpiar Sesión</button>
	</a>

	<script>
			const checkbox = document.getElementById('confirmar');
			const boton = document.getElementById('btnLimpiar');

			checkbox.addEventListener('change', function () {
					boton.disabled = !this.checked;
			});
	</script>

<?php
	}
?>

  <h1>ChapuTransfers</h1>
	<form action="1.busca.php" method="post">
		<label for="busca">Nro. Transfer:</label>
		<input type="text" name="transfer" id="busca" required>
		<br>
		<br>
		<br>
		<input type="submit" value="Buscar Transfer">
	</form>
</body>
</html>