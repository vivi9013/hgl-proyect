<?php 


//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

//Funcion que permite mostrar foto
include("../funciones/mostrarFoto.php");

include("combos.php");
?>
<script src="funciones.js"></script>

<div class="row">
<div class="col-xs-12">

  <div class="box box-<?php echo "$sColorCaja";  ?>">
    <div class="box-header">
      <h3 class="box-title">Servicios por Liberar</h3>
      <div class="box-tools pull-right">
        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
      </div>
    </div><!-- /.box-header -->
    <div class="box-body">
      <div class="table-responsive">
      <table id="example1" class="table table-condensed table-bordered table-striped ">
        <thead>
          <tr class="info">
            <th class="centrarx">#</th>
            <th class="centrarx">Folio</th>
            <th class="centrarx">Cliente</th>
            <th class="centrarx">Proceso</th>
            <th class="centrarx">Terminado</th>
            <th class="centrarx">Liberar</th>
          </tr>
        </thead>
        <tbody>
      <?php 
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
                          servicios.nombre_servidor,
                          servicios.sexo_solicitante,
                          servicios.nombre_solicitante,
                          (SELECT departamentos.extension FROM personas INNER JOIN trabajadores 
                          ON personas.id=trabajadores.id_persona 
                          INNER JOIN departamentos
                          ON trabajadores.id_departamento=departamentos.id
                          WHERE personas.id=servicios.id_personaServidor) AS extServidor,
                          servicios.accion_realizada,
                          servicios.tipo_servicio,
                          servicios.id_personaSolicitante
                        FROM
                          servicios
                        LEFT JOIN tipo_servicio ON servicios.id_tipo_servicio = tipo_servicio.id
                        INNER JOIN areas ON servicios.id_area = areas.id
                        WHERE
                          servicios.id_personaServidor =$sIdPersona
                        AND 
                          (liberado = 0)  
                        ORDER BY
                          servicios.id desc",$conexion) or die (mysql_error());
                     
          //Descargamos el arreglo que arroja la consulta
          $n=1;
          while ($row1=mysql_fetch_row($consulta1))
          {
               $fechaPet = strtotime($row1[4]);
               $fechaPet=  date("d-m-Y", $fechaPet); //06:23 pm   

                $horaPet = strtotime($row1[5]);
               $horaPet=  date("h:i a", $horaPet); //06:23 pm

               $fechaSer = strtotime($row1[6]);
               $fechaSer=  date("d-m-Y", $fechaSer); //06:23 pm   

                $horaSer = strtotime($row1[7]);
               $horaSer=  date("h:i a", $horaSer); //06:23 pm

               $fechaTer = strtotime($row1[8]);
               $fechaTer=  date("d-m-Y", $fechaTer); //06:23 pm   

                $horaTer = strtotime($row1[9]);
               $horaTer=  date("h:i a", $horaTer); //06:23 pm

              if ($row1[21]!=null) {

                $nombreServer=$row1[24];
                $userFoto=$row1[28];
                $userTipo=$row1[23];
              }
              else{
                $nombreServer="El servicio aún no ha sido elegido";
                $userFoto=0;
                $userTipo='?';
              }
          ?> 
               <tr>
                  
                  <td class="centrarx"><?php echo $n; ?></td>
                  <td class="centrarx">
                      <a  onclick="areaSolicitada('<?php echo "$row1[12]"; ?>','<?php echo "$row1[14]" ?>','<?php echo "$fechaPet" ?>','<?php echo "$horaPet" ?>','<?php echo "$row1[0]" ?>')" data-toggle="modal" class="btn btn-default">
                          <i class="<?php echo " $row1[20]"; ?>"></i> <?php echo "$row1[0] "; ?>
                      </a>
 
                  <!-- fotograria del tecnico de soporte -->
                  <td class="centrarx">
                  <?php 
                   ?>
                    <a onclick="foto('<?php echo Fotografia($userFoto,$userTipo); ?>','<?php echo "$row1[2]"; ?>','<?php echo "$row1[24]"; ?>')" data-toggle="modal" class="btn btn-default btn-xs">
                      <img src="<?php echo Fotografia($userFoto,$userTipo); ?>" class="img-circle foto " alt="User Image">                            
                    </a> 
                  </td>

                  <td class="centrarx">
                  <?php 
                    $liberar=($row1[15]=='1' && $row1[16]=='1')?' ':'disabled';
                    $boton=($row1[15]=='1' && $row1[16]=='1')?'btn btn-success':'btn btn-warning';
                    $ico=($row1[15]=='1' && $row1[16]=='1')?'fa fa-check-circle':'fa fa-times-circle';
                   ?>
                      <a onclick="solProceso('<?php echo "$fechaPet" ?>','<?php echo "$horaPet" ?>','<?php echo "$row1[0]" ?>','<?php echo "$row1[22]" ?>','<?php echo "$row1[25]" ?>','<?php echo "$fechaSer" ?>','<?php echo "$horaSer" ?>','<?php echo "$row1[13]" ?>')" class="<?php echo "$boton $liberar"; ?>">
                        <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                      </a> 
                  </td>

                  <td class="centrarx">
                  <?php 
                      $liberar=($row1[15]=='1' && $row1[16]=='1' && $row1[17])?' ':'disabled';
                      $boton=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'btn btn-success':'btn btn-warning';
                      $ico=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'fa fa-check-circle':'fa fa-times-circle';
                     ?>
                     <a onclick="solTermino('<?php echo "$fechaPet" ?>','<?php echo "$horaPet" ?>','<?php echo "$row1[0]" ?>','<?php echo "$row1[22]" ?>','<?php echo "$row1[25]" ?>','<?php echo "$fechaSer" ?>','<?php echo "$horaSer" ?>','<?php echo "$row1[13]" ?>','<?php echo "$fechaTer" ?>','<?php echo "$horaTer" ?>','<?php echo "$row1[26]" ?>','<?php echo "$row1[27]" ?>')" class="<?php echo "$boton $liberar"; ?>">
                          <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                        </a> 
                      </td>

                      <td class="centrarx">
                        <?php 
                      $liberar=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?' ':'disabled';
                      $boton=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'btn btn-primary':'btn btn-danger';
                      $ico=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'fa fa-check-circle':'fa fa-times-circle';
                        ?>
                      <a href="javascript:liberar(<?php echo $row1[0];?>)" class="<?php echo "$boton $liberar"; ?>">
                        <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                      </a> 

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
            <th class="centrarx">Cliente</th>
            <th class="centrarx">Proceso</th>
            <th class="centrarx">Terminado</th>
            <th class="centrarx">Liberar</th>
          </tr>
        </tfoot>
      </table>
      </div>
    </div><!-- /.box-body -->
  </div><!-- /.box -->
</div><!-- /.col -->
</div><!-- /.row -->       

   <!-- jQuery 2.1.4 -->

    <!-- Bootstrap 3.3.5 -->
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <!-- DataTables -->
    <script src="../plugins/datatables/jquery.dataTables.min.js"></script>

    <script src="../plugins/datatables/dataTables.bootstrap.min.js"></script>

    <!-- SlimScroll -->
    <script src="../plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="../plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/app.min.js"></script>
  <!-- inicio script -->

<script type="text/javascript">

$(document).ready(function() {

    $('#example1').DataTable( {
        "language": {
           // "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            "url": "../plugins/datatables/langauge/Spanish.json"
        },
        "order": [[ 0, "asc" ]],
         "paging":   true,
         "ordering": true,
         "info":     false,
         "searching": true,
         "stateSave": true
         // "dom": '<"top"i>rt<"bottom"flp><"clear">'
    } );
} );

</script>
