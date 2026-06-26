<?php

$id_usuario= $_SESSION["s_clave"];


require_once('../../plugins/ezpdf/class.ezpdf.php');
$pdf =& new Cezpdf('a4','landscape');
$pdf->selectFont('../../plugins/ezpdf/fonts/Helvetica.afm');
$pdf->ezSetCmMargins(1,1,1.5,1.5);

//se manda llamar la conexion
include("../../conexion/conexion.php");

//verifico inicio de sesion
include("../../sesiones/verificar_sesion.php");
$conn=$conexion;


$queEmp = "SELECT
                inventario,
                serie,
                modelo,
                marca,
                descripcion,
                tecnologia,
                consumible,
                red,
                ip
            FROM impresoras
            ORDER BY id_impresora DESC";

$resEmp = mysql_query($queEmp, $conexion) or die(mysql_error());
$totEmp = mysql_num_rows($resEmp);

$ixx = 0;
while($datatmp = mysql_fetch_assoc($resEmp)) {
        $ixx = $ixx+1;
        $data[] = array_merge($datatmp, array('num'=>$ixx));
}

$titles = array(
                                
                                'inventario'=>'<b>Inventario</b>',
								'serie'=>'<b>No Serie</b>',
								'modelo'=>'<b>Modelo</b>',
								'descripcion'=>'<b>Descripcion</b>',
                                'marca'=>'<b>Marca</b>',
                                'tecnologia'=>'<b>Tecnologia</b>',
                                'consumible'=>'<b>Consumible</b>',
                                'Red'=>'<b>Red</b>',
                                'ip'=>'<b>Ip</b>',

    
								
								
                        );
$options = array(
                                'shadeCol'=>array(0.5,0.5,0.5),
                                'xOrientation'=>'center',
                                'width'=>750
                        );

$txttit = "<b>LISTA COMPLETA DE COMPUTADORAS</b>\n";
$pdf->setColor(0.3,0.3,0.3);
$pdf->ezImage("../../images/encabezado.jpg", 0, 500, 'none', 'center');
$pdf->ezText($txttit, 15,array("justification"=>"center"));
$pdf->ezTable($data, $titles, '', $options);
$pdf->ezText("\n");
$pdf->ezText("<b>Fecha:</b> ".date("d/m/Y"), 10);
$pdf->ezText("<b>Hora:</b> ".date("H:i:s")."\n\n", 10);
$pdf->ezStream();

?>