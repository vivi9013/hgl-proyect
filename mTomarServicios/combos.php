<?
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

$sPersona=$_SESSION["s_id_persona"];
//primer combo CARRERAS
mysql_query("SET NAMES utf8");
$combo1 = mysql_query("SELECT id, concat(abreviatura,' | ',area) as nomb from areas where activo=1 ORDER BY area ASC ",$conexion);
$num1=mysql_num_rows($combo1);

// SEGUNDO COMBO plan de estudios

$combo2 = mysql_query("SELECT
							id,
							servicio
						FROM
							tipo_servicio",$conexion);
$num2=mysql_num_rows($combo2);
/////////////////////

?>