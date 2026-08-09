<?php
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';
$id = $_POST["id"];
//desactualizar

$fecha=date("Y-m-d"); 
$hora=date ("H:i:s");

$consulta = mysql_query("UPDATE servicios SET 
							liberado = 1 ,
							estatus_final='Liberado' ,
							fecha_finaliza='$fecha' ,
							hora_finaliza='$hora' ,
							liberadox='Soporte'
							WHERE id = $id",$conexion)or die(mysql_error());

?>