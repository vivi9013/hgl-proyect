<?php 
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';


//Cambio de piel del sistema
$skin="skin-".$sSkin;
$opa="A";

if ($sColorCaja=='default') {
    $botonModal='botonArregloB';
}
else{
    $botonModal='botonArreglo';
  }
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
    <!-- ACCIONES MODALES -->
    <!-- Scroll Menu -->
    <link href="../plugins/alertaModal/css/sweetalert.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Custom functions file -->
    <script src="../plugins/alertaModal/js/functions.js"></script>
    <!-- Sweet Alert Script -->
    <script src="../plugins/alertaModal/js/sweetalert.min.js"></script>
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
      <li class="header">Opciónes del Módulo   </li>
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
            Tomar servicios
            <small>Lista de servicios pendientes</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
            <li class="active">Lista de servicios</li>
          </ol>
        </section>

        <!-- Main content -->
       <section class="content">
          <div class="row"> <!-- Alta de Actividades -->
            <div class="col-xs-12">

              <div class="box box-<?php echo "$sColorCaja";  ?>">
                <div class="box-header">
                  <h3 class="box-title">Seguimientos de Servicios</h3>
                    <div class="box-tools pull-right">
                      <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>

                    </div>
                </div><!-- /.box-header -->
                <div class="box-body">

                  <div class="table-responsive" id="resultadoSolPendientes">
                  </div>
                </div><!-- /.box-body -->

            </div><!-- /.col -->
          </div><!-- /.row -->
      </section><!-- /.content -->
</div>
  
  </div>
<!-- Abrir ventana modal -->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="modalBotones">
  <div class="modal-dialog">
    <div class="modal-content ">
      
      <div class="modal-header ">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title "><strong>Seleccione una opción para continuar con el servicio</strong></h4>
      </div>
      
      <div class="modal-body">
        <p class="justificado">
          <!-- INPUT OCULTO -->
          <input type="hidden" name="" id="idRegistro">
            <center>
              <div class="row">
                <button type="button" onclick="enviarValorBotones(this.value);" value="Con inventario" class="btn btn-outline <?php echo "$botonModal"; ?>"><i class="fa fa-check-circle fa-lg" aria-hidden="true"></i> Equipo fisico con inventario</button>
              </div>
              <div class="row">
                <button type="button" onclick="enviarValorBotones(this.value);" value="Sin inventario" class="btn btn-outline <?php echo "$botonModal"; ?>"> <i class="fa fa-times-circle fa-lg" aria-hidden="true"></i> Equipo fisico sin inventario</button>
              </div>
              <div class="row">
                <button type="button" onclick="enviarValorBotones(this.value);" value="Especial" class="btn btn-outline <?php echo "$botonModal"; ?>"><i class="fa fa-question-circle fa-lg" aria-hidden="true"></i> Servicio especial</button>
              </div>                  
            </center>                  
        </p>
      </div>

      <div class="modal-footer "> 
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- Cerrar ventana modal -->

<!-- Abrir ventana modal -->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="modalBotones2">
  <div class="modal-dialog">
    <div class="modal-content ">
      
      <div class="modal-header ">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title "><strong>Seleccione la accion final</strong></h4>
      </div>
      
      <div class="modal-body">
        <p class="justificado">
          <!-- INPUT OCULTO -->
          <input type="hidden" name="" id="idRegistro">
            <center>
              <div class="row">
                <button type="button" onclick="enviarValorBotones2(this.value);" value="Con inventario" class="btn btn-outline <?php echo "$botonModal"; ?>">
                <i class="fa fa-power-off" aria-hidden="true"></i>
                    Cerrar servicio
                </button>
              </div>
              <div class="row">
                <button type="button" onclick="enviarValorBotones2(this.value);" value="Con inventario" class="btn btn-outline <?php echo "$botonModal"; ?>">
                <i class="fa fa-times" aria-hidden="true"></i>
                    Cancelar la accion de 'Terminar Servicio'
                </button>
              </div>
              <div class="row">
                <button disabled type="button" onclick="enviarValorBotones2(this.value);" value="Con inventario" class="btn btn-outline <?php echo "$botonModal"; ?>">
                <i class="fa fa-times" aria-hidden="true"></i>
                    Cancelar la accion de 'Modo de servicio'
                </button>
              </div>
              <div class="row">
                <button type="button" onclick="enviarValorBotones2(this.value);" value="Con inventario" class="btn btn-outline <?php echo "$botonModal"; ?>">
                <i class="fa fa-times-circle" aria-hidden="true"></i>
                    Cancelar Servicio
                </button>
              </div>   
              <div class="row">
                <button type="button" onclick="enviarValorBotones2(this.value);" value="Con inventario" class="btn btn-outline <?php echo "$botonModal"; ?>">
                <i class="fa fa-toggle-on" aria-hidden="true"></i>
                    Liberar Servicio
                </button>
              </div>               
            </center>                  
        </p>
      </div>

      <div class="modal-footer "> 
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- Cerrar ventana modal -->

<!-- VENTANA MODAL DE CONFIRMACION  PARA TOMAR UN SERVICIO -->
<div id="modalConfirmacion" class="modal fade modal-<?php echo "$sColorCaja";  ?>" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Confirmación</h4>
      </div>
      <div class="modal-body">
        <p>¿Estas seguro de tomar este servicio?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-<?php echo "$sColorCaja";  ?>" id="botonTomarServicio">Tomar</button>

      </div>
    </div>

  </div>
</div>
 <!-- modal para editar los registros -->
<div id="modalEditar" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Editar información</h4>
      </div>
      <div class="modal-body">
        <form action="#" method="post" class="in-line" id="formActualizar">
           <input type="text" name="id" id="id">
            <div class="form-group">
               <label for="area" class="control-label">Area</label>
               <input type="text" name="siglas" id="area" class="form-control">
            </div>
            <div class="form-group">
               <label for="area" class="control-label">Siglas</label>
               <input type="text" name="siglas" id="siglas" class="form-control">
            </div>
            <button type="submit" id="enviar" style="display:none;">enviar</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success" onclick="$('#enviar').click();">Actualizar</button>
      </div>
    </div>

  </div>
</div>
</body>
<!-- VENATNA MODAL -->
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


    <!-- jQuery 2.1.4 -->
    <script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <!-- Select2 -->
    <script src="../plugins/select2/select2.full.min.js"></script>
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
    <!-- page script -->
<script type="text/javascript">
 llenarSolPendientes();
$(document).ready(function() {
  
    $('#example1').DataTable( {

        paging:         false,
            "language": {
               // "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                "url": "../plugins/datatables/langauge/Spanish.json"
            }        
    } );
} );

</script>
  <script>
    $(function () {
      //Initialize Select2 Elements
      $(".select2").select2();

      //Datemask dd/mm/yyyy
      $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
      //Datemask2 mm/dd/yyyy
      $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
      //Money Euro
      $("[data-mask]").inputmask();

      //Date range picker
      $('#reservation').daterangepicker();
      //Date range picker with time picker
      $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY h:mm A'});
      //Date range as a button
      $('#daterange-btn').daterangepicker(
          {
            ranges: {
              'Today': [moment(), moment()],
              'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              'Last 7 Days': [moment().subtract(6, 'days'), moment()],
              'Last 30 Days': [moment().subtract(29, 'days'), moment()],
              'This Month': [moment().startOf('month'), moment().endOf('month')],
              'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
          },
      function (start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
      }
      );

      //iCheck for checkbox and radio inputs
      $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
      });
      //Red color scheme for iCheck
      $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
        checkboxClass: 'icheckbox_minimal-red',
        radioClass: 'iradio_minimal-red'
      });
      //Flat red color scheme for iCheck
      $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass: 'iradio_flat-green'
      });

      //Colorpicker
      $(".my-colorpicker1").colorpicker();
      //color picker with addon
      $(".my-colorpicker2").colorpicker();

      //Timepicker
      $(".timepicker").timepicker({
        showInputs: false
      });
    });
  </script> 
</html>
