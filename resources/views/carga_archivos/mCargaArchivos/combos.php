<?
include'../conexion/conexion.php';

//primer combo CARGA ARCHIVOS
mysql_query("SET NAMES utf8");
$combo1 = mysql_query("SELECT
							id_catego_archivos,	
							categoria	
						FROM
							catego_archivos
						WHERE
							catego_archivos.activo = 1	
						ORDER BY
							categoria ASC",$conexion);
$num1=mysql_num_rows($combo1);
?>