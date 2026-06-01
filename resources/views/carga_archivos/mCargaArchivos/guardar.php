<?
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

include("../funciones/quitarAcentos.php");

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

mysql_query("SET NAMES utf8");
$insertar = mysql_query("INSERT INTO carga_archivos
(nombre,id_catego,descripcion_archivo,version_archivo,fecha_registro,hora_registro,activo,usuario)
VALUES ('$p_nombre','$p_tipoC','$p_desc','$p_version','$p_fecha','$p_hora','$activo','$usuario')",$conexion) or die(mysql_error());

echo"<script language=\"javascript\">window.location=\"index.php?var=exitog\"</script>";
?>
<!-- http://www.cuevana2.tv/18727/el-renacido/ 
y agregar pdfs con el nombre 