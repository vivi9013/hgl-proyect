<?php
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

$fechaSol  = $_POST["fechaSol"];
$horaSol   = $_POST["horaSol"];
$fechaServ = $_POST["fechaServ"];
$horaServ  = $_POST["horaServ"];
$folio     = $_POST["folio"];
//desactualizar
$usuario=$_SESSION["s_clave"];
$p_fecha=date("Y-m-d"); 
$p_hora=date ("H:i:s");

mysql_query("SET NAMES utf8");
$consulta = mysql_query("UPDATE servicios
								SET fecha_peticion = '$fechaSol',
								 hora_peticion = '$horaSol' ,
								 fecha_tomado = '$fechaServ',
								 hora_tomado = '$horaServ',
								 id_uss= '$usuario',
								 fecha_modificado = '$p_fecha',
								 hora_modificado = '$p_hora',
								 modificado=1,
								 modificadox=(SELECT
													CONCAT(nombre, ' ', ap_paterno, ' ',ap_materno) as Nombre
												FROM
													personas
												WHERE
													id = $usuario),
								 motivo_modificado='Cambio de fecha u hora en peticion o tomado'
								WHERE
									id = $folio",$conexion)or die(mysql_error());
?>