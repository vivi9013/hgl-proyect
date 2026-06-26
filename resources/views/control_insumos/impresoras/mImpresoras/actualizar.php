<?
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//Extraer el periodo activo

$p_id_impresora=$_POST["id_impresora"];
$p_inventario=$_POST["inventario"]; 
$p_tipo=$_POST["tipo"]; 
$p_serie=$_POST["serie"]; 
$p_modelo=$_POST["modelo"];
$p_marca=$_POST["marca"]; 
$p_descripcion=$_POST["descripcion"];
$p_tecnologia=$_POST["tecnologia"];
$p_consumible=$_POST["consumible"];
$p_red=$_POST["red"];
$p_ip=$_POST["ip"];
$p_comodato=$_POST["comodato"];
$activo=1;
//se extrae de una funcion date 
$p_fecha=date("Y-m-d"); 
$p_hora=date ("H:i:s");
//constante
//variables de sesion
$usuario=$_SESSION["s_clave"];

mysql_query("SET NAMES utf8");
$actualizar = mysql_query("UPDATE impresoras
								SET inventario = '$p_inventario',
								 tipo = '$p_tipo',
								 serie = '$p_serie',
								 modelo = '$p_modelo',
								 marca = '$p_marca',
								 descripcion = '$p_descripcion',
								 tecnologia = '$p_tecnologia',
								 consumible = '$p_consumible',
								 red = '$p_red',
								 ip = '$p_ip',
								 comodato = '$p_comodato',
								 fecha = '$p_fecha',
								 hora = '$p_hora',
								 usuario = '$usuario',
								 activo = '$activo'
								WHERE
									id_impresora=$p_id_impresora",$conexion) or die(mysql_error());


echo"<script language=\"javascript\">window.location=\"lista.php?var=exito\"</script>";
?>

