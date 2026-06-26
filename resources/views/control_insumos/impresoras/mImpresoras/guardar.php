<?php
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion//
include("../sesiones/verificar_sesion.php");

//variables post
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
$p_nombre_equipo=$_POST["nombre_equipo"];
$activo=1;
//se extrae de una funcion date 
$p_fecha=date("Y-m-d"); 
$p_hora=date ("H:i:s");
//constante
//variables de sesion
$usuario=$_SESSION["s_clave"];

mysql_query("SET NAMES utf8");
$insertar = mysql_query("INSERT INTO impresoras 
(inventario,tipo,serie,modelo,marca,descripcion,tecnologia,consumible,red,ip,comodato,fecha,hora,usuario,activo)
VALUES ('$p_inventario', '$p_tipo', '$p_serie', '$p_modelo', '$p_marca', '$p_descripcion', '$p_tecnologia','$p_consumible', '$p_red', '$p_ip', '$p_comodato', '$p_fecha',' $p_hora', '$usuario', '$activo')",$conexion) or die(mysql_error());

echo"<script language=\"javascript\">window.location=\"index.php?var=exitog\"</script>";
?>

