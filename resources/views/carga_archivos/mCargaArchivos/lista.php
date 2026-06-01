<?php 
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

//cargo variables de sesion
include("../sesiones/variables_sesion.php");

include"../funciones/quitarAcentos.php";

//Cambio de piel del sistema
$skin="skin-".$sSkin;
$opa="A";
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
      <li class="header">Opciones del Módulo</li>
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
            Lista de Archivos
           <!-- <small>Lista</small> -->
          </h1>
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
            <li class="active">Lista</li>
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

        <!-- Alerta Modal -->
          <?php 
            if ( isset($_GET["var"]) && $_GET["var"] == "exitog" ) {
              echo "<script>
              jQuery(function()

                {
                  swal(\"¡Operación Satisfactoria!\", \"El registro se ha guardado correctamente.\", \"success\");
                });

              </script>";
            }
          ?> 
        <!-- Alerta Modal --> 

          <div class="row">
            <div class="col-xs-12">

              <div class="box box-<?php echo "$sColorCaja";  ?>">
                <div class="box-header">
                  <h3 class="box-title">Lista de Archivos</h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>

              </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div class="table-responsive">
                  <table id="example1" class="table table-condensed table-bordered table-striped ">
                    <thead>
                      <tr class="info">
                        <th>#</th>
                        <th>Editar</th>
                        <th>Cargar</tDh>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Versión del archivo</th>
                        <th>Descargar</tDh>
                        <th>Status</tDh>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                  mysql_query("SET NAMES utf8");
                  $consulta=mysql_query("SELECT
                                            carga_archivos.id_archivo,
                                            carga_archivos.nombre,
                                            catego_archivos.categoria,
                                            carga_archivos.descripcion_archivo,
                                            carga_archivos.version_archivo,                                           
                                            carga_archivos.activo
                                          FROM
                                            carga_archivos
                                          INNER JOIN catego_archivos ON catego_archivos.id_catego_archivos = carga_archivos.id_catego
                                          ORDER BY
                                            carga_archivos.id_archivo DESC",$conexion) or die (mysql_error());
          
                  //Descargamos el arreglo que arroja la consulta
                  $n=1;
                  while ($row=mysql_fetch_row($consulta))
                  {
                  	$activo=($row[5]==1)?"<i class='fa fa-check-square-o' aria-hidden='true'></i>":"<i class='fa fa-square-o' aria-hidden='true'></i>"; ; 
                  	$stu=($row[5]==1)?" ":"inactivo"; 
         
                  ?> 
                       <tr>
						<td class="<?php echo $stu; ?>"><?php echo $n; ?></td>
						<td class="centrar"> 
							<a href="editar.php?id=<?php echo $row[0]; ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
						</td>
						<td class="<?php echo $stu; ?> centrar">
						 <a href="cargar.php?id=<?php echo $row[0]; ?>"><i class="fa fa-upload" aria-hidden="true"></i></a>
                        </td>
                        <td class="<?php echo $stu; ?>"><?php echo $row[1]; ?></td>
                        <td class="<?php echo $stu; ?>"><?php echo $row[2]; ?></td>
                        <td class="<?php echo $stu; ?>"><?php echo $row[3]; ?></td>
                        <td class="<?php echo $stu; ?> centrar"><?php echo $row[4]; ?></td>
                
                        <td class="<?php echo $stu; ?> centrar">

                            <?php 

                              $Carpeta=sanear_string($row[2]);
                           
                              // funcion para quitar acentos
                              $Archivo=(sanear_string($row[1]).".pdf");

                              $direccion="hojasArchivos/".utf8_decode("$Carpeta")."/".($Archivo);
                              if (file_exists("hojasArchivos/".utf8_decode("$Carpeta")."/".utf8_decode($Archivo)) )
                              {
                                echo "<a target=\"_blank\" href=\"".
                                    $direccion
                                    ."\"><i class=\"fa fa-download\" aria-hidden=\"true\"></i></a>";
                              } 
                              else
                              {
                                echo "
                                        </span>
                                        <span class=\"fa-stack \">
                                          <i class=\"fa fa-download fa-stack-1x\"></i>
                                          <i class=\"fa fa-ban fa-stack-2x text-danger\"></i>
                                        </span>
                                      ";
                              }
                            // 
                            ?>
				
                      </td>

                        <td class="centrar"> <a href="status.php?val=<?php echo $row[5]; ?>&id=<?php echo $row[0]; ?>"><?php echo $activo; ?></a></td>
						                  
                      </tr>
                  <?php 
                  ++$n;
                  }
                   ?>

                    </tbody>
                    <tfoot>
                      <tr>
                        <th>#</th>
                        <th>Editar</th>
                        <th>Cargar</tDh>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Versión del archivo</th>
                        <th>Descargar</th>
                        <th>Status</th>
                      </tr>
                    </tfoot>
                  </table>
                  </div>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
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
<script type="text/javascript">

$(document).ready(function() {

    $('#example1').DataTable( {
        "language": {
           // "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            "url": "../plugins/datatables/langauge/Spanish.json"
        },
        "order": [[ 0, "desc" ]],
       "paging":   true,
        "ordering": true,
        "info":     false,
        "searching": true,
         stateSave: true
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
