<?php 
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

include'../funciones/diasTranscurridos.php';

//Cambio de piel del sistema
$skin="skin-".$sSkin;
$opa="C";

 ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>HGLinares</title>

    <link rel="icon" href="../images/favicon.ico" type="image/x-icon" />
    
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
        <link rel="stylesheet" href="../dist/css/estilos.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="../dist/css/skins/<?php echo $skin; ?>.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="../plugins/iCheck/flat/blue.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="../plugins/morris/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="../plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="../plugins/datepicker/datepicker3.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="../plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <!-- Daterange picker -->
    <!-- Select2 -->
    <link rel="stylesheet" href="../plugins/select2/select2.css">
    <link rel="stylesheet" href="../plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="../plugins/datatables/dataTables.bootstrap.css">
    <!-- alertifi -->
    <link rel="stylesheet" href="../plugins/alertifyjs/css/alertify.css">
    <link rel="stylesheet" href="../plugins/alertifyjs/css/themes/default.css">
    <!-- ACCIONES MODALES -->
  
    <!-- Scroll Menu -->
    <link href="../plugins/sweetalert2-master/dist/sweetalert2.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- jQuery 2.1.4 -->
    <script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
    
    <script src="../plugins/alertaModal/js/functions.js"></script>
    <!-- Sweet Alert Script -->
    <script src="../plugins/sweetalert2-master/dist/sweetalert2.js"></script>
    <!-- ACCIONES MODALES -->
    <script src="funciones.js"></script>
  </head>
  <body class="hold-transition <?php echo $skin; ?> sidebar-mini">
    <div class="wrapper">

      <header class="main-header">
      <?php include"verificacion.php";?>
        <?php  include("../plantilla/headers.php");?>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
          <?php  include("../plantilla/users.php");?>
          <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
      <li class="header">Opciónes del Módulo</li>
      <?php 
       include("menu.php");
       include("../plantilla/vertical.php");
       ?>
      </ul>
        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Módulos
            <small>Lista de Módulos</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
            <li><a href="index.php"><i class="fa fa-home"></i>Catalogo</a></li>
            <li class="active">Liberados | Cancelados</li>
          </ol>
        </section>

        <!-- Main content -->
       <section class="content">

           <div class="row"> <!-- Alta de Actividades -->

          </div><!-- /.row --><!-- Alta de Actividades -->

          <div class="row">
            <div class="col-xs-12">

              <div class="box box-<?php echo "$sColorCaja";  ?>">
                <div class="box-header">
                  <h3 class="box-title">Servicios Finalizados (Liberados | Cancelados)</h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>

              </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                <form name="form2" method="POST" action="agregar_quitar.php">
                  <div class="table-responsive">
                  <table id="example1" class="table table-condensed table-bordered table-hover ">
                    <thead>
                      <tr class="info">
                        <th class="centrarx">#</th>
                        <th class="centrarx">Folio</th>
                        <th class="centrarx">Modificar Fecha</th>
                        <th class="centrarx">Imprimir</th>
                        <th class="centrarx">Cliente</th>
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
                                            servicios.id_personaSolicitante,
                                            servicios.nombre_solicitante,
                                            servicios.sexo_solicitante,
                                            estatus_final,
                                            (SELECT departamentos.extension FROM personas INNER JOIN trabajadores 
                                            ON personas.id=trabajadores.id_persona 
                                            INNER JOIN departamentos
                                            ON trabajadores.id_departamento=departamentos.id
                                            WHERE personas.id=servicios.id_personaServidor) AS extServidor,
                                            servicios.accion_realizada,
                                            id_uss,
                                            nombre_solicitante,
                                            modificado
                                          FROM
                                            servicios
                                          LEFT JOIN tipo_servicio ON servicios.id_tipo_servicio = tipo_servicio.id
                                          INNER JOIN areas ON servicios.id_area = areas.id
                                          WHERE
                                            servicios.id_personaServidor = $sIdPersona
                                          AND 
                                            estatus_final in ('Liberado','Cancelado') 
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

                        $nombreServer=$row1[22];
                        $userFoto=$row1[21];
                        $userTipo=$row1[23];
                      }
                      else{
                        $nombreServer="El servicio no fue asignado al personal";
                        $userFoto=0;
                        $userTipo='?';
                      }
                      
                  $fecha=date("Y-m-d");
                  $dias=dias_transcurridos($row1[4],$fecha);
                  $fondoTR=($row1[29]==0)?'':'registroVerde' ;
                  ?> 
                       <tr class="<?php echo "$fondoTR"; ?>" id="fila<?php echo $n;?>">
						              
                          <td class="centrarx"><?php echo $n; ?></td>
                          <td class="centrarx">
              							  <a onclick="solTerminox('<?php echo "$fechaPet" ?>','<?php echo "$horaPet" ?>','<?php echo "$row1[0]" ?>','<?php echo "$row1[22]" ?>','<?php echo "$row1[25]" ?>','<?php echo "$fechaSer" ?>','<?php echo "$horaSer" ?>','<?php echo "$row1[13]" ?>','<?php echo "$fechaTer" ?>','<?php echo "$horaTer" ?>','<?php echo "$row1[26]" ?>','<?php echo "$row1[10]" ?>','<?php echo "$row1[12]" ?>')" data-toggle="modal" class="btn btn-default">
              							  		<i class="<?php echo "$row1[20]"; ?>"></i> <?php echo "$row1[0] | $dias  $row1[10] |"; ?>
              							  </a>
                          </td>

                          <td class="centrarx">
                              <a onclick="cambiarFechaX(
                              '<?php echo "$row1[0]" ?>',
                              '<?php echo "$row1[22]" ?>',
                              '<?php echo "$row1[26]" ?>',
                              '<?php echo "$row1[10]" ?>',
                              '<?php echo "$row1[4]" ?>',
                              '<?php echo "$row1[5]" ?>',
                              '<?php echo "$row1[6]" ?>',
                              '<?php echo "$row1[7]" ?>',
                              '<?php echo "$n" ?>')" data-toggle="modal" class="btn btn-primary">
                                  <i class="fa fa-calendar"></i>
                              </a>
                          </td>



                          <td class="centrarx">
                              	<?php 
              								$liberar=($row1[24]=='Liberado')?' ':'disabled';
              								$boton=($row1[24]=='Liberado')?'btn btn-primary':'btn btn-danger';
              								$ico=($row1[24]=='Liberado')?'fa fa fa-print':'fa fa fa-print';
                  					 		?>                                                       

                            <div class="btn-group">
                              <button type="button" class="btn btn-primary"><i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i> </button>
                             
                              <button type="button" class="btn btn-primary dropdown-toggle"
                                      data-toggle="dropdown" >
                                <span class="caret"></span>
                                <span class="sr-only">Desplegar menú</span>
                              </button>
                             
                              <ul class="dropdown-menu" role="menu">
                                <li><a href="#" onclick="imprimir(<?php echo $row1[0]; ?>)"><i class="fa fa-download fa-lg" aria-hidden="true"></i>Original</a></li>
                                <li class="divider"></li>
                                <li><a href="#" onclick="imprimirx(<?php echo $row1[0]; ?>)"><i class="fa fa-eye fa-lg" aria-hidden="true"></i>Compacta</a></li>
                                
                              </ul>
                            </div> 
                        </td>

                          <!-- fotograria del tecnico de soporte -->
                          <td class="centrarx">
                          <?php 
                           ?>
                            <a onclick="foto('<?php echo Fotografia($userFoto,$userTipo); ?>','<?php echo "$row1[2]"; ?>','<?php echo "$row1[22]"; ?>')" data-toggle="modal" class="btn btn-default btn-xs">
                              <img src="<?php echo Fotografia($userFoto,$userTipo); ?>" class="img-circle foto " alt="User Image">                            
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
                        <th class="centrarx">Modificar Fecha</th>
                        <th class="centrarx">Imprimir</th>
                        <th class="centrarx">Cliente</th>
                      </tr>
                    </tfoot>
                  </table>
                  
                  </div>

                </div><!-- /.box-body -->

              </div><!-- /.box -->
              </form>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <footer class="main-footer">
        <?php include("../plantilla/footers.php") ?>
      </footer>

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
        <?php include("../plantilla/asides.php") ?>
      </aside><!-- /.control-sidebar -->
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->

<!-- VENTANA MODAL COLUMNA TERMINO-->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="modalTerminox">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title centrarx">Información del servicio en termino con folio - <strong><label id="folio2"></label></strong></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Trabajador Atendido - <strong><label id="persona2"></label></strong>
        </p>

        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Extensión telefónica - <strong><label id="extencion2"></strong></label> |   
          Area de Soporte - <strong> <label id="area"></label></strong>
        </p>

        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El servicio fue solicitado el dia  <strong><label id="fechapet2"></label></strong> a las <strong><label id="horapet2"></label></strong>
        </p>



        <p class="izquierda">
          <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          El servicio fue tomado el dia  <strong><label id="fechaser2"></label></strong> a las <strong><label id="horaser2"></label></strong>
        </p>

        <p>
           <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <label id="clasi2"></label>
        </p>

        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          El servicio fue terminado el dia  <strong><label id="fechater"></label></strong> a las <strong><label id="horater"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          Solucion :<strong><label id="solucion"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          <strong>Tipo de Servicio :</strong><label id="tipo"></label>
        </p>
      </div>

      <div class="modal-footer "> 
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
 <!-- VENTANA MODAL -->

<!-- VENTANA MODAL COLUMNA CAMBIO DE FECHA-->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="modalCambiarFecha">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title centrarx">Información del servicio en termino con folio - <strong><label id="folio3"></label></strong></h4>
        <input type="hidden" id="num">
      </div>
      
      <div class="modal-body ">

      <div class="row">

        <div class="col-lg-12">
          <div class="form-group">
              <label for="nombre">Persona que solicita el servicio:</label>
              <input type="text" id="persona3" class="form-control" readonly="">
          </div>
        </div>

        <div class="col-lg-8">
          <div class="form-group">
              <label for="fechaSol">Fecha de solicitud de servicio:</label>
              <input type="date" id="fechaSol" class="form-control" >
          </div>
        </div>

        <div class="col-lg-4">
          <div class="form-group">
              <label for="horaSol">Hora de solicitud :</label>
              <input type="time" id="horaSol" class="form-control" >
          </div>
        </div>

        <div class="col-lg-8">
          <div class="form-group">
              <label for="fechaServ">Fecha de servicio:</label>
              <input type="date" id="fechaServ" class="form-control" >
          </div>
        </div>

        <div class="col-lg-4">
          <div class="form-group">
              <label for="horaServ">Hora de servicio:</label>
              <input type="time" id="horaServ" class="form-control" >
          </div>
        </div>
          <input type="hidden" id="foliox" >
      </div>

      </div>

      <div class="modal-footer "> 
          <button type="button" class="btn btn-primary" id="btnGuardarFecha" onclick="actFecha();">Actualizar Información</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
 <!-- VENTANA MODAL -->

    <!-- Bootstrap 3.3.5 -->
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <!-- Select2 -->
    <script src="../plugins/select2/select2.full.min.js"></script>
    <!-- DataTables -->
    <script src="../plugins/datatables/jquery.dataTables.min.js"></script>

    <script src="../plugins/alertifyjs/alertify.js"></script>
    
    <script src="../plugins/datatables/dataTables.bootstrap.min.js"></script>
    <!-- SlimScroll -->
    <script src="../plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="../plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/app.min.js"></script>
    <!-- datetimePicker -->

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
         "info":     true,
         "searching": true,
         "stateSave": true
         // "dom": '<"top"i>rt<"bottom"flp><"clear">'
    } );
} );

</script>
    <!-- page script -->
    <script>
      $(function () {
        //Initialize Select2 Elements
        $(".select2").select2();

      });
    </script> 



</html>
