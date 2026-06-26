<?
include'../conexion/conexion.php';

//primer combo CARRERAS
mysql_query("SET NAMES utf8");
$combo1 = mysql_query("SELECT
							id,
							nombre AS depto
						FROM
							departamentos
						WHERE
							activo = 1 
						ORDER BY nombre",$conexion);
$num1=mysql_num_rows($combo1);
//SEGUNDO COMBO plan de estudios
$combo2 = mysql_query("SELECT
								personas.id,
								CONCAT(
									personas.ap_paterno,
									' ',
									personas.ap_materno,
									' ',
									personas.nombre
								) AS persona
							FROM
								personas
							INNER JOIN trabajadores ON personas.id = trabajadores.id_persona
							WHERE
								personas.activo = 1
							AND trabajadores.activo = 1
							ORDER BY
								personas.ap_paterno,
								personas.ap_materno",$conexion);
$num2=mysql_num_rows($combo2);

$combo3 = mysql_query("SELECT
								mobiliario.inventario
							FROM
								mobiliario
							INNER JOIN tipo_mobiliario ON mobiliario.id_tipo_mobiliario = tipo_mobiliario.id
							LEFT JOIN impresoras ON mobiliario.inventario = impresoras.inventario
							WHERE
								ISNULL(
									impresoras.id_impresora
								) AND  (
									tipo_mobiliario.tipo LIKE '%impre%'
									AND mobiliario.activo = 1
								)
							",$conexion);
$num3=mysql_num_rows($combo3);
///////////////////////

?>