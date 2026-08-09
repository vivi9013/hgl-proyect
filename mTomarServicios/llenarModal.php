<?php

include'../conexion/conexion.php';

$folio = $_POST["folio"];

	$consulta1=mysql_query("SELECT
								fecha_peticion,
								hora_peticion,
								fecha_tomado,
								hora_tomado
							FROM
								servicios
							WHERE
								ID = $folio",$conexion) or die (mysql_error());

	//Descargamos el arreglo que arroja la consulta
	$row1=mysql_fetch_row($consulta1);

	$resultado 	=$row1[0]."|".$row1[1]."|".$row1[2]."|".$row1[3];
	$arr		=array("resultado"=>$resultado);

	echo json_encode($arr);
?>