<?php
require_once 'db.php';

function busca($transfers){

	$conn = getConnection();

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

}

function comprueba($transfer, $idProductos){
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
}

function procesa($transfer, $idProductos){
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
}

function devuelve($transfer, $idProductos){
		$placeholders = implode(',', array_fill(0, count($idProductos), '?'));

		$conn = getConnection();

		$sql = "UPDATE td SET td.cantidadfacturada = 0
						FROM ACO_Transfer_Cabecera tc
						INNER JOIN ACO_Transfer_Detalle td
							ON td.IdTransfer = tc.IdTransfer
						WHERE NumeroTransfer = ?
							AND fecha > '20250701'
							AND td.IdProducto IN ($placeholders)";

		$stmt = $conn->prepare($sql);
		$stmt->execute(array_merge([$transfer], $idProductos));
}