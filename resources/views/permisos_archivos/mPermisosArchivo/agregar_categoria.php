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
$pro=$_GET['id'];
                  mysql_query("SET NAMES utf8");
                  $consulta1=mysql_query("SELECT
                                            CONCAT(
                                              ap_paterno,
                                              ' ',
                                              ap_materno,
                                              ' ',
                                              nombre
                                            )
                                          FROM
                                            personas
                                          WHERE
                                            id =$pro",$conexion) or die (mysql_error());
                  $row1=mysql_fetch_row($consulta1)
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
            Trabajador
            <small>Lista de categorías</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
            <li><a href="index.php"><i class="fa fa-home"></i>Lista</a></li>
            <li class="active">Categorías</li>
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
           <div class="row"> <!-- Alta de Actividades -->

          </div><!-- /.row --><!-- Alta de Actividades -->

          <div class="row">
            <div class="col-xs-12">

              <div class="box box-<?php echo "$sColorCaja";  ?>">
                <div class="box-header">
                  <h3 class="box-title">Trabajador -> (<?php echo "$row1[0]"; ?> ) </h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>

              </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                <form name="form2" method="POST" action="agregar_quitar.php">
                  <div class="table-responsive">
                  <table id="example2" class="table table-condensed table-bordered table-hover ">

                    <thead>
                      <tr class="info">
                        <th>#</th>
                        <th>Categorías</th>
                        <th>Agregar/Quitar</th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                  mysql_query("SET NAMES utf8");
                  $consulta=mysql_query("SELECT
                                            catego_archivos.id_catego_archivos,
                                            catego_archivos.categoria,
                                            catego_archivos.fecha_registro,
                                            catego_archivos.hora_registro,
                                            catego_archivos.activo
                                          FROM
                                            catego_archivos 
                                          WHERE
                                            catego_archivos.activo in (1,0)
                                         ORDER BY catego_archivos.categoria ASC",$conexion) or die (mysql_error());
                  //Descargamos el arreglo que arroja la consulta
                  $n=1;
                  while ($row=mysql_fetch_row($consulta))
                  {
                    mysql_query("SET NAMES utf8");
                    $consulta3 = mysql_query("SELECT
                                                  trabajador_categorias.id
                                                FROM
                                                  trabajador_categorias
                                                WHERE
                                                  id_categoria = $row[0]
                                                AND id_trabajador = $pro
                                                LIMIT 1",$conexion);
                    $CONT=mysql_num_rows($consulta3);
                    $row3=mysql_fetch_row($consulta3);

                  	$activo=($CONT==1)?"<i class='fa fa-minus-square' aria-hidden='true'></i>":"<i class='fa fa-plus-square' aria-hidden='true'></i>"; 
                  	$stu=($CONT==1)?" ":"inactivo";

                  	$archivo=($CONT==1)?"restar.php":"sumar.php"; 
                  ?> 
                       <tr class="" id="Grupo<?php echo $row[0];?>">
						              <td class="<?php echo $stu; ?>"><?php echo $n; ?></td>
                          
                          <td class="<?php echo $stu; ?>"><?php echo $row[1]; ?></td>

                    

                          <td class="centrar"><input  type="checkbox" name="<?php echo "s$n-$pro"; ?>" value="<?php echo $row[0];?>" id="check_grupo<?php echo $row[0];?>" onclick="seleccionar_grupo(<?php echo $row[0];?>,this.id)"> </td>

                        </tr>
                  <?php 
                  ++$n;
                  }
                   ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>#</th>
                        <th>Categorías</th>
                        <th>Agregar/Quitar</th>
                      </tr>
                    </tfoot>
                  </table>
                  
                  </div>

                </div><!-- /.box-body -->
                <div class="box-footer">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                      <label for="" class="text-left"><a href="javascript:seleccionar_todos();"><span class="fa fa-check-square"></span> Marcar todos</a> / <a href="javascript:desmarcar_todos();"><span class="fa fa-minus-square"></span> Desmarcar todos</a></label>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                      <button disabled class="btn btn-default" id="boton_reciclar">Agregar o quitar categoría </button>    
                    </div>
                </div>
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

    $('#example2').DataTable( {
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

