<?
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

//variables post
$pDesc=$_POST["descripcion"]; 
$pIdUsuario=$_POST["id_usuario"]; 
$pIdPersona=$_POST["id_persona"]; 
$pIdDepartamento=$_POST["id_departamento"]; 
$pDepartamento=$_POST["departamento"]; 
$pIdArea=$_POST["id_area"];
//se extrae de una funcion date 
$fecha=date("Y-m-d"); 
$hora=date ("H:i:s");
$activo=1;
/*variable de session*/
$usuario=$_SESSION["s_clave"];
mysql_query("SET NAMES utf8");	
$insertar= mysql_query("INSERT INTO servicios
							(id_usc,id_personaSolicitante,fecha_peticion,hora_peticion,id_departamento,departamento,descripcion_servicio,id_area,pendiente)
						VALUES
							($pIdUsuario,$pIdPersona,'$fecha','$hora',$pIdDepartamento,'$pDepartamento','$pDesc','$pIdArea',1)
						",$conexion) or die (mysql_error());

echo"<script language=\"javascript\">window.location=\"index.php?var=exitog\"</script>";
?>

