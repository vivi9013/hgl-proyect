<?php 


//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

//Funcion que permite mostrar foto
include("../funciones/mostrarFoto.php");

include'../funciones/diasTranscurridos.php';

include("combos.php");

$userOrigin=$_SESSION["s_clave"];
$idPersona=$_SESSION["s_id_persona"];

?>
<script src="funciones.js"></script>

<div class="row">
<div class="col-xs-12">

  <div class="box box-<?php echo "$sColorCaja";  ?>">
    <div class="box-header">
      <h3 class="box-title">Lista de Servicios solicitados</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
      </div>
    </div><!-- /.box-header -->
    <div class="box-body">
      <!-- <div class="table-responsive"> -->
      <table id="example1" class="table table-condensed table-bordered table-striped ">
        <thead>
          <tr class="info">
            <th class="centrarx">#</th>
            <th class="centrarx">Folio</th>
            <th class="centrarx">Atiende</th>
            <th class="centrarx">Proceso</th>
            <th class="centrarx">Terminado</th>
            <th class="centrarx">Acción</th>
          </tr>
        </thead>
        <tbody>
      <?php 
mysql_query("SET NAMES utf8");
$consulta1=mysql_query("SELECT
                          servicios.id,
                          servicios.id_area,
                          servicios.departamento,
                          servicios.id_personaSolicitante,
                          servicios.fecha_peticion,
                          servicios.hora_peticion,
                          servicios.fecha_tomado,
                          servicios.hora_tomado,
                          servicios.fecha_termino,
                          servicios.hora_termino,
                          tipo_servicio.servicio,
                          areas.activo,
                          areas.area,
                          servicios.clasificacion_servicio,
                          servicios.descripcion_servicio,
                          servicios.pendiente,
                          servicios.proceso,
                          servicios.terminado,
                          servicios.liberado,
                          servicios.id_usc,
                          areas.icono,
                          servicios.id_personaServidor,
                          servicios.nombre_Servidor,
                          servicios.sexo_solicitante,
                          servicios.nombre_solicitante,
                          servicios.ext_telefonica,
                          servicios.sede,
                          servicios.abre_sede,
                          servicios.id_sede,
                          servicios.accion_realizada,
                          servicios.tipo_servicio,
                          servicios.id_uss
                        FROM
                          soporte_area
                        INNER JOIN servicios ON soporte_area.id_area = servicios.id_area
                        LEFT JOIN tipo_servicio ON servicios.id_tipo_servicio = tipo_servicio.id
                        INNER JOIN areas ON servicios.id_area = areas.id
                        WHERE
                          soporte_area.id_persona = $idPersona
                        AND (liberado = 0)
                        ORDER BY
                          servicios.id DESC",$conexion) or die (mysql_error());
     
//Descargamos el arreglo que arroja la consulta
$n=1;
while ($row1=mysql_fetch_row($consulta1))
{
      $verificarUsuario=($userOrigin==$row1[31])?"igual":"diferente";

      $fecha=date("Y-m-d");
      // --------------------------------------------------
      $fechaPet = strtotime($row1[4]);
      $fechaPet=  date("d-m-Y", $fechaPet); //06:23 pm   

      $horaPet = strtotime($row1[5]);
      $horaPet=  date("h:i a", $horaPet); //06:23 pm
      // --------------------------------------------------
      // --------------------------------------------------
      $fechaTom = strtotime($row1[6]);
      $fechaTom=  date("d-m-Y", $fechaTom); //06:23 pm   

      $horaTom = strtotime($row1[7]);
      $horaTom=  date("h:i a", $horaTom); //06:23 pm
      // --------------------------------------------------    
      // --------------------------------------------------
      $fechaTer = strtotime($row1[8]);
      $fechaTer=  date("d-m-Y", $fechaTer); //06:23 pm   

      $horaTer = strtotime($row1[9]);
      $horaTer=  date("h:i a", $horaTer); //06:23 pm
      // --------------------------------------------------   
      $dias=dias_transcurridos($row1[4],$fecha);

    if ($row1[21]!=null) {

      $nombreServer=$row1[22];
      $userFoto=$row1[21];
      $userTipo=$row1[23];
    }
    else{
      $nombreServer="El servicio aún no ha sido elegido";
      $userFoto=0;
      $userTipo='?';
    }
?> 
     <tr>
        
        <!-- NUMERO CONSECUTIVO -->
        <td class="centrarx">
          <?php echo $n; ?>
        </td>
        <!-- NUMERO CONSECUTIVO -->
        
        <!-- FOLIO -->
        <td class="centrarx">
            <a onclick="ventanaFolio('<?php echo "$row1[14]"; ?>','<?php echo "$row1[0]"; ?>','<?php echo "$row1[12]"; ?>','<?php echo "$fechaPet"; ?>','<?php echo "$horaPet"; ?>','<?php echo "$row1[26]"; ?>')"  data-toggle="modal" class="btn btn-<?php echo "$sColorCaja";  ?>">
                <i class="<?php echo " $row1[20]"; ?>"></i> <?php echo "$row1[0] | $row1[27] | $dias "; ?>
            </a>
        </td>
        <!-- FOLIO -->
        
        <!-- FOTOGRAFIA -->
        <td class="centrarx">
        <?php 
         ?>

            <a onclick="foto('<?php echo Fotografia($userFoto,$userTipo); ?>','<?php echo "$row1[12]"; ?>','<?php echo "$row1[22]"; ?>')" data-toggle="modal" class="btn btn-default btn-xs">
              <img src="<?php echo Fotografia($userFoto,$userTipo); ?>" class="img-circle foto " alt="User Image">                            
            </a> 
        </td>
        <!-- FOTOGRAFIA -->

        <!-- PROCESO -->
        <td class="centrarx">
        <?php 
          $liberar=($row1[15]=='1' && $row1[16]=='1')?' ':'disabled';
          $boton=($row1[15]=='1' && $row1[16]=='1')?'btn btn-success':'btn btn-warning';
          $ico=($row1[15]=='1' && $row1[16]=='1')?'fa fa-check-circle':'fa fa-times-circle';
         ?>
            <a onclick="ventanaProceso('<?php echo "$row1[24]"; ?>','<?php echo "$row1[0]"; ?>','<?php echo "$row1[25]"; ?>','<?php echo "$fechaPet"; ?>','<?php echo "$horaPet"; ?>','<?php echo "$row1[26]"; ?>','<?php echo "$fechaTom"; ?>','<?php echo "$horaTom"; ?>','<?php echo "$row1[13]"; ?>')"  data-toggle="modal" class="<?php echo "$boton $liberar"; ?>">
              <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
            </a> 
        </td>
        <!-- PROCESO -->

        <!-- TERMINADO -->
        <td class="centrarx">
        <?php 
            $liberar=($row1[15]=='1' && $row1[16]=='1' && $row1[17])?' ':'disabled';
            $boton=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'btn btn-success':'btn btn-warning';
            $ico=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'fa fa-check-circle':'fa fa-times-circle';
           ?>
            <a onclick="ventanaTerminado('<?php echo "$row1[24]"; ?>','<?php echo "$row1[0]"; ?>','<?php echo "$row1[25]"; ?>','<?php echo "$fechaPet"; ?>','<?php echo "$horaPet"; ?>','<?php echo "$row1[26]"; ?>','<?php echo "$fechaTom"; ?>','<?php echo "$horaTom"; ?>','<?php echo "$row1[13]"; ?>','<?php echo "$fechaTer"; ?>','<?php echo "$horaTer"; ?>','<?php echo "$row1[29]"; ?>','<?php echo "$row1[30]"; ?>')"  data-toggle="modal" class="<?php echo "$boton $liberar"; ?>">
              <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
            </a> 
        </td>
        <!-- TERMINADO -->

        <!-- ACCION -->
        <td class="centrarx">
          <?php 

              if ($row1[21]==null and $row1[13]==null ) {
                  $liberar=' ';
                  $boton='btn btn-primary';
                  $ico='fa fa-check-circle';
                  ?>
                  <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                  <a href="javascript:tomarServicio(<?php echo $row1[0];?>)" class="<?php echo "$boton $liberar"; ?>">
                    <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                  </a> 

                  <?php 
              }


                if ($row1[21]<>null and $row1[13]==null ){
                          $liberar=' ';
                          $boton='btn btn-warning';
                          $ico='fa fa-check-square';
                          //saber si es la misma persona que eligio el servicio y darle continuidad
                          if ($verificarUsuario=="igual") {
                          ?>

                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <div class="btn-group">
                            <button type="button" class="btn btn-warning"><i class="fa fa-chevron-right" aria-hidden="true"></i> Seleccione </button>
                           
                            <button type="button" class="btn btn-warning dropdown-toggle"
                                    data-toggle="dropdown" >
                              <span class="caret"></span>
                              <span class="sr-only">Desplegar menú</span>
                            </button>
                           
                            <ul class="dropdown-menu" role="menu">
                              <li><a href="#" onclick="enviarValorBotones('Con inventario','<?php echo "$row1[0]"; ?>');"><i class="fa fa-list-ol" aria-hidden="true"></i> Mobiliario con Inventario</a></li>
                              <li class="divider"></li>
                              <li><a href="#" onclick="enviarValorBotones('Sin inventario','<?php echo "$row1[0]"; ?>');"><i class="fa fa-list-ul" aria-hidden="true"></i> Mobiliario sin Inventario</a></li>
                              <li class="divider"></li>
                              <li><a href="#" onclick="enviarValorBotones('Especial','<?php echo "$row1[0]"; ?>');"><i class="fa fa-tasks" aria-hidden="true"></i> No interviene Mobiliario</a></li>
                            </ul>
                          </div>      
                                            
                          <?php 
                          }
                          else{
                          ?>  
                            <a href="#" onclick="msjElegido('<?php echo "$row1[22]"; ?>')" class="btn btn-default"><i class="fa fa-lock  fa-lg"></i><span class="sr-only"></span></a>
                          <?php 
                          } 
                }
              
              if ($row1[13]<>null and $row1[17]==0 ){
                //saber si es la misma persona que eligio el servicio y darle continuidad
                if ($verificarUsuario=="igual") {
                  switch ($row1[13]) {
                    case 'Con inventario':
                          $liberar=' ';
                          $boton='btn btn-default';
                          $ico='fa fa-list-ol';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:inventarios('<?php echo "$row1[0]"; ?>','<?php echo "$row1[1]"; ?>','<?php echo "$row1[14]"; ?>');" class="<?php echo "$boton $liberar"; ?>"><i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i></a>

                          <?php     
                      break;
                    case 'Sin inventario':
                          $liberar=' ';
                          $boton='btn btn-default';
                          $ico='fa fa-list-ul';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:modalSI(<?php echo $row1[0];?>,'<?php echo "$row1[14]"; ?>','<?php echo "$row1[1]"; ?>')"  class="<?php echo "$boton $liberar"; ?>" onContextMenu="cancelarSel();">
                            <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          </a> 


                          <?php     
                      break;
                    case 'Especial':
                          $liberar=' ';
                          $boton='btn btn-default';
                          $ico='fa fa-tasks';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:modalSM(<?php echo $row1[0];?>,'<?php echo "$row1[14]"; ?>','<?php echo "$row1[1]"; ?>')"  class="<?php echo "$boton $liberar"; ?>">
                            <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          </a> 

                          <?php     
                      break;
                  }
                 }
                else{
                  ?>
                  <a href="#" onclick="msjElegido('<?php echo "$row1[22]"; ?>')" class="btn btn-default"><i class="fa fa-lock  fa-lg"></i><span class="sr-only"></span></a>                 
                  <?php
                }
                }

                if ($row1[15]==1 and $row1[16]==1   and $row1[17]==1  ){
                //saber si es la misma persona que eligio el servicio y darle continuidad
                if ($verificarUsuario=="igual") {
                          $liberar=' ';
                          $boton='btn btn-warning';
                          $ico='fa fa-bell';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:enviarDatos2(<?php echo $row1[0];?>)"  class="<?php echo "$boton $liberar"; ?>">
                            <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          </a> 

                          <?php  
                }
                else{
                  ?>
                  <a href="#" onclick="msjElegido('<?php echo "$row1[22]"; ?>')" class="btn btn-default"><i class="fa fa-lock  fa-lg"></i><span class="sr-only"></span></a>                 
                  <?php
                }   
                }
          ?>
        </td>
      </tr>

<?php 
++$n;
}
          ?>
        </tbody>
        <tfoot>
          <tr>
            <th class="centrarx">#</th>
            <th class="centrarx">Folio</th>
            <th class="centrarx">Atiende</th>
            <th class="centrarx">Proceso</th>
            <th class="centrarx">Terminado</th>
            <th class="centrarx">Acción</th>
          </tr>
        </tfoot>
      </table>
      <!-- </div> -->
    </div><!-- /.box-body -->
  </div><!-- /.box -->
</div><!-- /.col -->
</div><!-- /.row -->       

<script type="text/javascript">

$(document).ready(function() {

    $('#example1').DataTable( {
        "language": {
           // "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            "url": "../plugins/datatables/langauge/Spanish.json"
        },
        "order": [[ 0, "asc" ]],
         "paging":   false,
         "ordering": true,
         "info":     false,
         "searching": true,
         "stateSave": true
         // "dom": '<"top"i>rt<"bottom"flp><"clear">'
    } );
} );

</script>
