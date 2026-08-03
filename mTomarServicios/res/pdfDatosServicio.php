<?php 

$Id=$_GET['id'];

include'../funciones/funcionEspacios.php';
include'../funciones/funcionSalto.php';
include'../funciones/mesanioEspanol.php';
include'../funciones/calcularEdad.php';
include'../conexion/conexion.php'; 
                
mysql_query("SET NAMES utf8");
$consulta=mysql_query("SELECT
                        id,
                        nombre_solicitante,
                        departamento,
                        ext_telefonica,
                        fecha_peticion,
                        hora_peticion,
                        fecha_tomado,
                        hora_tomado,
                        descripcion_servicio,
                        descripcion_mobiliario,
                        inventario,
                        accion_realizada,
                        nombre_servidor,
                        tipo_servicio,
                        (SELECT area FROM areas WHERE areas.id=servicios.id_area) AS AREA
                    FROM
                        servicios
                    WHERE
                        id =$Id",$conexion) or die (mysql_error());
   
//Descargamos el arreglo que arroja la consulta
$n=1;
$row=mysql_fetch_row($consulta);


$fechaS=date("d-m-Y",strtotime($row[4])); 
$fechaA=date("d-m-Y",strtotime($row[6])); 

$fechai =date("d-m-Y");

 ?>

<style type="text/css">
<!--
table
{
    width:  100%;
 
}

hr{
  border: solid 1px #34495e;
}

table.borde
{
    width:  90%;
    border: solid 1px #D8D8D8;
    margin:auto;
}
th
{
    text-align: center;
    border: solid 0px #113300;
    background: #EEFFEE;
}
th.borde
{
    text-align: center;
    border: solid 1px #D8D8D8;
    background: #EEFFEE;
}


td.borde
{
    text-align: left;
    border: solid 1px #D8D8D8;
}
td.col1
{
    border: solid 0px red;
    text-align: right;
}

td.titular
{
    text-align: center;
    border: solid 1px #dcdde1;
    font-weight: normal;
    font-size: 12px;
    padding: 6px;

}

td.titularx
{
    text-align: center;
    border: solid 1px #ffffff;
    font-weight: normal;
    font-size: 12px;
    padding: 10px;

}

td.titular3
{
    text-align: left;
    border: solid 1px #dcdde1;
    font-weight: normal;
    font-size: 12px;
    padding: 9px;

}

.altura{
    height:200px;
}

td.titular2
{
    text-align: center;
    border: solid 1px #dcdde1;
    font-weight: normal;
    font-size: 12px;
    background: #dcdde1;
    padding: 0px;

}

label.enfa{
    text-decoration: underline;
}

label.folio{
font-weight: bold;
font-size:16px;
}

td.subtitular
{
    text-align: center;
    border: solid 1px rgba(220, 221, 225,.2);
    background: #ffffff;
    color:#34495e;
    letter-spacing: 3px;
    padding: 2px;

}
.izq{
    text-align: left;
}

td.fecha
{
    text-align: right;
    border: solid 0px #34495e;
    background: #ffffff;
    color:#34495e;
    letter-spacing: 3px;
    padding: 18px;

}
/*hojas de estilo propia*/
img{
    width: 100%;
}

/*letras*/
.chico{font-size: 11px;}  .mediano{font-size: 15px;}  .grande{font-size:18px;} .medianox{font-size: 13px;}
.subrayado{text-decoration: underline;} .firma {font-size: 13px;font-style: italic;}

.ancho{width:20px; };

.bajo{
    display: block;
    margin: 15px 0px 0px 0px;
    background: #ccc;
}
/**/
-->


</style>


<table border="0">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <col style="width: 10%">
    <!-- defino el ancho de la tabla -->
    <tr border="0">
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

<tr>

    <td rowspan="2" colspan="2" class="titular">
        <img src="../images/hgl.jpg" >
    </td>

    <td  colspan="6" class="titular">
        <label class="grande">Hoja de servicio | </label>
        <label class="grande">Área - <?php echo "$row[14]"; ?></label>
    </td>

    <td rowspan="2" colspan="2" class="titular">
        <img src="../images/SSNLOPD.jpg" alt="">
    </td>
</tr>

<tr>

    <td  colspan="1" class="titular2">
       <label class="folio"><?php echo "$row[0]"; ?></label>
    </td>
    <td  colspan="5" class="titular">
        <label class="mediano"><strong>Departamento.</strong> - <?php echo "$row[2]"; ?></label> 
        <!-- <label class="mediano"><strong><?php //echo "$row[3]"; ?></strong></label> -->
    </td>

</tr>
 
<tr>
    <td  colspan="1" class="titular2">
        <label class="mediano"><strong></strong></label>
    </td>
    <td  colspan="2" class="titular">
        <label class="mediano"><strong>Fecha</strong></label>
    </td>
    <td  colspan="1" class="titular">
        <label class="mediano"><strong>Hora</strong></label>
    </td>    
    <td  colspan="6" class="titular3" rowspan="3">
        <label class="mediano izq"><strong>Descripción del equipo : </strong> <?php echo "$row[9]"; ?></label>
    </td>  
</tr>
 
 <tr>
    <td  colspan="1" class="titular">
        <label class="mediano"><strong>Solicitud</strong></label>
    </td>
    <td  colspan="2" class="titular">
        <label class="mediano"><?php echo "$fechaS"; ?></label>
    </td>
    <td  colspan="1" class="titular">
        <label class="mediano"><?php echo "$row[5]"; ?></label>
    </td>  
 
</tr>
 <tr>
    <td  colspan="1" class="titular">
        <label class="mediano"><strong>Servicio</strong></label>
    </td>
    <td  colspan="2" class="titular">
        <label class="mediano"><?php echo "$fechaA"; ?></label>
    </td>
    <td  colspan="1" class="titular">
        <label class="mediano"><?php echo "$row[7]"; ?></label>
    </td>     
</tr>

 <tr>
    <td  colspan="10" class="titular">
        <label class="mediano"><strong>Tipo de Servicio :</strong></label><label class="medianox"><?php echo "$row[13]"; ?></label>
    </td>    
</tr>

 <tr class="altura">
    <td class="titular" rowspan="2">
    
        <img src="../images/imagen.jpg" >

    </td>
    <td  colspan="9"  class="titular3">
        <label class="mediano izq"><strong>Servicio solicitado -</strong></label><label class="medianox"> <?php echo "$row[8]"; ?></label>
    </td>  
</tr>

 <tr class="altura">

</tr>

 <tr class="altura">
    <td class="titular" rowspan="2">

        <img src="../images/imagen.jpg" >

    </td>
    <td  colspan="9"  class="titular3">
        <label class="mediano izq"><strong>Acciones Realizadas -</strong></label><label class="medianox"> <?php echo "$row[11]"; ?></label>
    </td>  
</tr>



<tr class="altura">

</tr>

<tr>
    <td class="titularx" colspan="5">
    
        <strong><label class="mediano">Usuario</label></strong><br>
    </td>
    <br>
    <td class="titularx" colspan="5">
        <strong><label class="mediano">Soporte</label></strong><br>
    </td>
</tr>

<tr>
    <td class="titularx" colspan="5">
        _____________________________________
    </td>
    <td class="titularx" colspan="5">
        _____________________________________
    </td>
</tr>
<tr>
    <td class="titularx" colspan="5">
        <strong><label class="mediano"><?php echo "$row[1]"; ?></label></strong>
    </td>
    <td class="titularx" colspan="5">
        <strong><label class="mediano"><?php echo "$row[12]"; ?></label></strong>
    </td>
</tr>
    <tr >

    </tr> 

    <tr>
        <td  colspan="10" align="center">

        </td>
    </tr>

</table>
