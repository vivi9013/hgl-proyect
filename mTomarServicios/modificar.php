<?php
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

$idServ = $_POST["idServ"];
$idMob = $_POST["idMob"];
$desc = $_POST["desc"];
$inv = $_POST["inv"];
$accion = $_POST["accion"];
$tipo = $_POST["tipo"];

$fecha=date("Y-m-d"); 
$hora=date ("H:i:s");

mysql_query("SET NAMES utf8");

$consulta1=mysql_query("SELECT
							id
						FROM
							tipo_servicio
						WHERE
							servicio LIKE '$tipo'",$conexion) or die (mysql_error());
 
//Descargamos el arreglo que arroja la consulta
$row1=mysql_fetch_row($consulta1);
$idTIPO=$row1[0];

 $actualizar = mysql_query("UPDATE servicios SET 
 									id_mobiliario = '$idMob', 
 									inventario='$inv' , 
 									descripcion_mobiliario='$desc',
 									accion_realizada='$accion',
 									tipo_servicio='$tipo',
 									fecha_termino='$fecha',
 									hora_termino='$hora',
 									terminado=1,
 									id_tipo_servicio='$idTIPO'
 						WHERE id = $idServ",$conexion)or die(mysql_error());

?>