<?php
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

$idServ = $_POST["idServ"];

$fecha=date("Y-m-d"); 
$hora=date ("H:i:s");

mysql_query("SET NAMES utf8");


 $actualizar = mysql_query("UPDATE servicios SET 
 									clasificacion_servicio = '', 
 									proceso='0' 

 						WHERE id = $idServ",$conexion)or die(mysql_error());

?>