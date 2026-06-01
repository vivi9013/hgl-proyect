<?php 
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

//combo
include'combos.php';

$IdArchivo=$_GET['id'];
//Cambio de piel del sistema
$skin="skin-".$sSkin;

mysql_query("SET NAMES utf8");
$consulta=mysql_query("SELECT
                          carga_archivos.id_archivo,
                          carga_archivos.nombre,
                          catego_archivos.id_catego_archivos,
                          catego_archivos.categoria,
                          carga_archivos.descripcion_archivo,
                          carga_archivos.activo
                        FROM
                          carga_archivos
                        INNER JOIN catego_archivos ON catego_archivos.id_catego_archivos = carga_archivos.id_catego
                        WHERE
                          carga_archivos.id_archivo = '$IdArchivo'",$conexion) or die (mysql_error());
$row=mysql_fetch_row($consulta);

$opa="B";

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
    <link rel="stylesheet" href="../plugins/select2/select2.css">
    <!-- estilo de carga de archivos -->
    <link href="../dist/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
        <script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
        <script src="../dist/js/fileinput.js" type="text/javascript"></script>
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
          <?php  include'../plantilla/users.php';?>
          <!-- sidebar menu: : style can be found in sidebar.less -->

      <ul class="sidebar-menu">
      <li class="header">Módulo</li>
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
            Documento
            <small>Carga de Archivos</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i>Panel de Control</a></li>
            <li><a href="lista.php"><i class="fa fa-list"></i>Lista</a></li>
            <li class="active">Carga de archivos</li>
          </ol>
        </section>

        <!-- Main content -->
       <section class="content">

        <!-- Alerta Modal -->
          <?php 
            if ( isset($_GET["var"]) && $_GET["var"] == "exito" ) {
              echo "<script>
              jQuery(function()

                {
                  swal(\"¡Operación Satisfactoria!\", \"El registro se ha actualizado correctamente.\", \"success\");
                });

              </script>";
            }
          ?> 
        <!-- Alerta Modal -->  

          <div class="box box-<?php echo "$sColorCaja";  ?>">
            <div class="box-header with-border"> 
            <!--ROW de la categoria-->
              <h3 class="box-title">Cargar Documento para la categoría: <?php echo "$row[3]"; ?></h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>

              </div>
            </div><!-- /.box-header -->
            <div class="box-body">
              <div class="row">

                <div class="col-xs-12  col-sm-12 col-md-12 col-lg-12 ">
                <div class="center-block">
          
                  <form enctype="multipart/form-data" action="subir-archivos.php" method="POST" accept-charset="utf-8"> 
                  <input type="hidden" name="MAX_FILE_SIZE" value="50000000" /> 
                  <div class="form-group">
                      <input id="file-3" name="archivo-a-subir" type="file" class="file subida" accept=".pdf" multiple=true -preview-file-type="any">
                      <input type="hidden" name="archivo" value="<?php echo "$row[1]"; ?>">
                      <input type="hidden" name="catego" value="<?php echo "$row[3]"; ?>">
                     <!-- /.row  <input type="hidden" name="tetra" value="<?php echo "$row[7]"; ?>"> -->
                  </div>
                </div>
                </div>
              </div><!-- /.row -->

            </div><!-- /.box-body -->
            <div class="box-footer">
                    
            </div>
            </form>
          </div><!-- /.box -->
          </div><!-- /.row --><!-- Alta de Actividades -->

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
    <script>
      $(function () {
        //Initialize Select2 Elements
        $(".select2").select2();
      });
    </script> 
</html>
