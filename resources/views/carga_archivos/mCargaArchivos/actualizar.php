<?
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//variables post

$p_nombre=$_POST["nombre"]; 
$p_tipoC=$_POST["tipo"]; 
$p_desc=$_POST["desc"]; 
$p_version=$_POST["version"];

//se extrae de una funcion date 
$p_fecha=date("Y-m-d"); 
$p_hora=date ("h:i:s");
//constante
$activo=1;
//variables de sesion
$usuario=$_SESSION["s_clave"];
$IdArchivo=$_POST["clave"]; 
mysql_query("SET NAMES utf8");
$actualizar = mysql_query("UPDATE carga_archivos
							SET 
								 nombre = '$p_nombre',
								 id_catego='$p_tipoC',
								 descripcion_archivo = '$p_desc',
								 version_archivo = '$p_version',
								 fecha_registro = '$p_fecha',
								 hora_registro = '$p_hora',
								 activo = '$activo',
								 usuario = '$usuario'
							WHERE
								 id_archivo = $IdArchivo",$conexion) or die(mysql_error());

echo"<script language=\"javascript\">window.location=\"lista.php?var=exito\"</script>";
?>
