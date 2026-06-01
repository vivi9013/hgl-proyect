<?php

$id_usuario= $_SESSION["s_clave"];


require_once('../../plugins/ezpdf/class.ezpdf.php');
$pdf =& new Cezpdf('a4');
$pdf->selectFont('../../plugins/ezpdf/fonts/Helvetica.afm');
$pdf->ezSetCmMargins(1,1,1.5,1.5);

//se manda llamar la conexion
include("../../conexion/conexion.php");

//verifico inicio de sesion
include("../../sesiones/verificar_sesion.php");
$conn=$conexion;
//variable que se encuentra declarada en el name del index de la categoría y en guardar declarada como POST
$p_tipoC=$_POST["tipo"];
//DECLARAR VARIABLE PARA QUE APAREZCA CUANDO ELIJA LA CATEGORIA
//$p_catego=$_POST["categoria"];  
// mysql_query("SET NAMES utf8"); QUITAR ESTA LINEA DONDE APAREZCA
$queEmp = "SELECT
              carga_archivos.nombre,
              catego_archivos.categoria,
              carga_archivos.descripcion_archivo,
              carga_archivos.fecha_registro,
              carga_archivos.hora_registro,
              carga_archivos.activo
            FROM
              carga_archivos
            INNER JOIN catego_archivos ON catego_archivos.id_catego_archivos = carga_archivos.id_catego
            WHERE
              id_catego = $p_tipoC
            AND
              carga_archivos.activo=1
            AND
              catego_archivos.activo=1
            ORDER BY
              id_archivo ASC";

$resEmp = mysql_query($queEmp, $conexion) or die(mysql_error());
$totEmp = mysql_num_rows($resEmp);
               
$consulta=mysql_query("SELECT
                        categoria
                      FROM
                        catego_archivos
                      WHERE
                        id_catego_archivos = $p_tipoC",$conexion) or die (mysql_error());

$row=mysql_fetch_row($consulta);  
$catego = strtoupper($row[0]);

$ixx = 0;
while($datatmp = mysql_fetch_assoc($resEmp)) {
        $ixx = $ixx+1;
        $data[] = array_merge($datatmp, array('num'=>$ixx));
}

//se deben llamar igual

$titles = array(
                                'num'=>'<b>No.</b>',
                                'nombre'=>'<b>Nombre</b>',
                               // 'categoria'=>'<b>Categoria</b>',
   								              'descripcion_archivo'=>'<b>Descripcion</b>'
                          
								
                        );
$options = array(
                                'shadeCol'=>array(0.5,0.5,0.5),
                                'xOrientation'=>'center',
                                'width'=>500
                        );

$txttit = "<b>                    LISTA COMPLETA DE ARCHIVOS DE $catego </b>\n";
//$txttit = "<b>                                        LISTA COMPLETA DE ARCHIVOS DE  $row[3] </b>\n";
$pdf->setColor(0.3,0.3,0.3);
$pdf->ezImage("../../images/encabezado.jpg", 0, 500, 'none', 'left');
$pdf->ezText($txttit, 12);
$pdf->ezTable($data, $titles, '', $options);
$pdf->ezText("\n");
$pdf->ezText("<b>Fecha:</b> ".date("d/m/Y"), 10);
$pdf->ezText("<b>Hora:</b> ".date("H:i:s")."\n\n", 10);
$pdf->ezStream();

?>