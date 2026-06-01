<?php 

//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

//cargo variables de sesion
include("../sesiones/variables_sesion.php");

include"../funciones/quitarAcentos.php";

$pCarpeta= sanear_string($_POST['catego']);
//$pCarpeta= sanear_string($pCarpeta);

//Crea una carpeta si esta no existe
if (!file_exists("hojasArchivos/$pCarpeta/")) {
	mkdir("hojasArchivos/$pCarpeta/", 0777);
}
$p_archivo=$_POST['archivo'];
$p_archivo=sanear_string($p_archivo);
$pArchivo= $p_archivo;

$target_path = "hojasArchivos/$pCarpeta/"; 

$target_path = $target_path . $pArchivo.'.pdf'; 

//echo $_FILES['archivo-a-subir']['type'] ;


	  	if(move_uploaded_file($_FILES['archivo-a-subir']['tmp_name'], $target_path)) 
		{ 
			$result="exito";
		} 
		else
		{
			$result="error";
		}

	header("Location: lista.php?var=$result");

 ?>