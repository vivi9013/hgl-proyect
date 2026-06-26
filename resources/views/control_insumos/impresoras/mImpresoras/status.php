<?
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

//variables post
$gStatus=$_GET["val"]; 
$gId=$_GET["id"]; 

//se extrae de una funcion date 
$fecha=date("Y-m-d"); 
$hora=date ("H:i:s");

/*variable de session*/
$usuario=$_SESSION["s_clave"];

//con el if cambiamos el status
$activo=($gStatus==1)?0:1;
	
$actualizar = mysql_query("UPDATE impresoras
							SET activo = '$activo',
							 fecha = '$fecha',
							 hora = '$hora',
							 usuario = '$usuario'
							WHERE
							 id_impresora = $gId",$conexion) or die (mysql_error());

echo"<script language=\"javascript\">window.location=\"lista.php?var=exito\"</script>";
?>

