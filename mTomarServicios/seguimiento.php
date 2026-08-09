<?php 
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

//cargo variables de sesion
include("../sesiones/variables_sesion.php");

include("combos.php");

//Cambio de piel del sistema
$skin="skin-".$sSkin;
if ($sColorCaja=='default') {
    $botonModal='botonArregloB';
}
else{
    $botonModal='botonArreglo';
  }
$opa="B";
 ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>ITLinares</title>

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
    <script src="funciones.js"></script>
  
    <!-- Scroll Menu -->
    <link href="../plugins/alertaModal/css/sweetalert.css" rel="stylesheet">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- jQuery 2.1.4 -->
    <script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="../plugins/alertaModal/js/functions.js"></script>
    <!-- Sweet Alert Script -->
    <link rel="stylesheet" href="../plugins/sweetalert2-master/dist/sweetalert2.css">
      <link rel="stylesheet" type="text/css" href="../plugins/alertifyjs/css/alertify.css">
  <link rel="stylesheet" type="text/css" href="../plugins/alertifyjs/css/themes/default.rtl.css">
    <!-- ACCIONES MODALES -->
    <script>
    </script>
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
            Lista de Servicios
          </h1>
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
            <li><a href="index.php"><i class="fa fa-home"></i>Catalogo</a></li>
            <li class="active">Lista de servicios</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content" id="llenar_listaXX">
          <!-- Acordeon -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

<!-- VENTANA MODAL COLUMNA FOLIO -->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="modalArea">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title centrarx"><?php echo "Información de la solicitud de servicio"; ?></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-asterisk" aria-hidden="true"></i>
          <strong>Descripcion del servicio solicitado </strong>- <label id="desc"></label>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El folio del servicio es  <strong><label id="folio"></label></strong> ligado al area de <strong><label id="areaSolicitada"></label></strong>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El servicio fue solicitado el dia  <strong><label id="fechapet"></label></strong> a las <strong><label id="horapet"></label></strong>
        </p>
        <p class="izuierda ">
          <?php // echo "Extensión telefónica - <strong>$row1[25]</strong>" ; ?>
        </p>
      </div>

      <div class="modal-footer "> 
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>
 <!-- VENTANA MODAL -->

 <!-- VENTANA MODAL COLUMNA PROCESO-->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="modalProceso">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title centrarx">Información del servicio en proceso con folio - <strong><label id="folio1"></label></strong></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Personal de soporte que atiende - <strong><label id="persona1"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El servicio fue solicitado el dia  <strong><label id="fechapet1"></label></strong> a las <strong><label id="horapet1"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Extensión telefónica - <strong><label id="extencion1"></label>
        </p>
        <p class="izquierda">
          <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          El servicio fue tomado el dia  <strong><label id="fechaser1"></label></strong> a las <strong><label id="horaser1"></label></strong>
        </p>

        <p>

           <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <label id="clasi1"></label>
        </p>
      </div>

      <div class="modal-footer "> 
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
 <!-- VENTANA MODAL -->

 <!-- VENTANA MODAL COLUMNA TERMINO-->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="modalTermino">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title centrarx">Información del servicio en termino con folio - <strong><label id="folio2"></label></strong></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Personal de soporte que atiende - <strong><label id="persona2"></label></strong>
        </p>

        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El servicio fue solicitado el dia  <strong><label id="fechapet2"></label></strong> a las <strong><label id="horapet2"></label></strong>
        </p>

        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Extensión telefónica - <strong><label id="extencion2"></label>
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
          <strong>Solucion :</strong><label id="solucion"></label>
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
  </td>

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


    <!-- Select2 -->
    <script src="../plugins/select2/select2.full.min.js"></script>

    <!-- Sweet Alert Script -->
    <script src="../plugins/sweetalert2-master/dist/sweetalert2.js"></script>

  <!-- inicio script -->
<script type="text/javascript">
	llenarListax();
</script>


  <script>
    $(function () {
      //Initialize Select2 Elements
      $(".select2").select2();

    });
  </script> 

<script src="../plugins/alertifyjs/alertify.js"></script>
</html>