<?php
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';
$id = $_POST["id"];
//desactualizar
$usuario=$_SESSION["s_clave"];
$p_fecha=date("Y-m-d"); 
$p_hora=date ("H:i:s");

mysql_query("SET NAMES utf8");
$consulta = mysql_query("UPDATE servicios
								SET proceso = 1,
								 id_personaServidor = $sIdPersona,
								 nombre_servidor = '$sNombreCompleto',
								 sexo_servidor = '$sGenero',
								 fecha_tomado = '$p_fecha',
								 hora_tomado = '$p_hora',
								 id_uss= '$usuario'
								WHERE
									id = $id",$conexion)or die(mysql_error());
?>