<?php 
//se manda llamar la conexion
include("../conexion/conexion.php");

//verifico inicio de sesion
include("../sesiones/verificar_sesion.php");

//cargo variables de sesion
include("../sesiones/variables_sesion.php");

//cargo variables de sesion
include("combos.php");

$pidServicio=$_POST['idServicio'];

//Cambio de piel del sistema
$skin="skin-".$sSkin;
$opa="B";
 ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Hospital General de Linares</title>
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
    <script src="funciones.js"></script>
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
            Lista del Mobiliario
            <small>Lista</small>
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
                  <h3 class="box-title">Lista del Mobiliario  </h3>
              <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
                </div><!-- /.box-header -->

                <div class="box-body">

                <div class="container-fluid">
                  <div class="row">
                    <a href="index.php"  class="btn btn-<?php echo "$sColorCaja centrar";  ?> pull-right marco">
                      <i class="fa fa-reply-all" aria-hidden="true"></i> Lista de servicios pendientes. 
                    </a> 
                  </div>
                </div>

                  <div class="table-responsive">
                  <table id="example1" class="table table-condensed table-bordered table-striped ">
                    <thead>
                      <tr class="info">
                        <th>#</th>
                        <th>Inventario</th>
                        <th>Descripcion</th>
                        <th>Responsable</th>
                        <th>Tipo de equipo</th>
                        <th>Seleccionar</th>
                      </tr>
                    </thead>
                    <tbody>
                  <?php 
                  mysql_query("SET NAMES utf8");
                  $consulta=mysql_query("SELECT
                                          mobiliario.id,
                                          mobiliario.descripcion,
                                          mobiliario.modelo,
                                          mobiliario.inventario,
                                        IF (
                                          mobiliario.id_persona = 0,
                                          'Sin responsable',
                                          CONCAT(
                                            personas.ap_paterno,
                                            ' ',
                                            personas.ap_materno,
                                            ' ',
                                            personas.nombre
                                          )
                                        ) AS responsable,
                                         tipo_mobiliario.tipo,
                                          mobiliario.marca,
                                          mobiliario.serie
                                        FROM
                                          mobiliario
                                        LEFT JOIN personas ON mobiliario.id_persona = personas.id
                                        INNER JOIN tipo_mobiliario ON tipo_mobiliario.id = mobiliario.id_tipo_mobiliario
                                        WHERE
                                          mobiliario.activo = 1",$conexion) or die (mysql_error());
                     
                  //Descargamos el arreglo que arroja la consulta
                  $n=1;
                  $consulta1=mysql_query("SELECT
                                            accion_realizada,
                                            descripcion_servicio,
                                            tipo_servicio,
                                            id_mobiliario
                                          FROM
                                            servicios
                                          WHERE
                                            id = $pidServicio",$conexion) or die (mysql_error());
                     
                  //Descargamos el arreglo que arroja la consulta
        
                  $row1=mysql_fetch_row($consulta1);

                  while ($row=mysql_fetch_row($consulta))
                  {
                  	if($row[0]==$row1[3]){
                  		$boColor="success";
                  	}
                  	else
                  	{
                  		$boColor="default";
                  	}
                  ?> 
                  <tr>

  						      <td ><?php echo $n; ?></td>
                    <td ><?php echo $row[3]; ?></td>
                    <td class="centrar">
                      <a href="#ventanaDesc<?php echo "$n"; ?>" data-toggle="modal" class="btn btn-<?php echo "$sColorCaja";  ?>">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                      </a>
                    </td>
                    <td ><?php echo $row[4]; ?></td>
                    <td ><?php echo $row[5]; ?></td>
                    <td class="centrar"> 
                        <a href="javascript:modificar('<?php echo $row[0];?>','<?php echo $pidServicio;?>','<?php echo $row[3];?>','<?php echo $row[1];?>','<?php echo $row1[0];?>','<?php echo $row1[1];?>','<?php echo "$row1[2]";?>')" class="btn btn-<?php echo "$boColor"; ?>"><i class="fa fa-check" aria-hidden="true"></i></a>
                        <!--                                idMobiliario            idServicio                   inventario                 descripcion            accionRealizada          sol                         tipo-->
                    </td>
                  </tr>
                  <!-- VENTANA MODAL COLUMNA FOLIO -->
                  <div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaDesc<?php echo "$n"; ?>">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        
                        <div class="modal-header">
                        <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
                          <h4 class="modal-title"><?php echo "Descripcion del equipo con inventario - $row[3]"; ?></h4>
                        </div>
                        
                        <div class="modal-body ">
                          <p class="izquierda">
                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            <?php echo "<strong>Descripcion del equipo </strong>$row[1]"; ?>
                          </p>
                          <p class="izuierda ">
                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            <?php echo "Marca - <strong>$row[6]</strong> | Modelo - <strong>$row[2]</strong> | Serie <strong>$row[7]</strong>" ; ?>
                          </p>
                          <p class="izuierda ">
                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            <?php echo "Responsable - <strong>$row[4]</strong>" ; ?>
                          </p>
                          <p class="izuierda ">
                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                            <?php echo "Tipo de Mobiliario - <strong>$row[5]</strong>" ; ?>
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
                  <?php 
                  ++$n;
                  }
                   ?>
                    </tbody>
                    <tfoot>
                      <tr>
                       <th>#</th>
                        <th>Inventario</th>
                        <th>Descripcion</th>
                        <th>Responsable</th>
                        <th>Tipo de equipo</th>
                        <th>Seleccionar</th>
                      </tr>
                    </tfoot>
                  </table>
                  
<!-- modal para editar los registros -->
<div id="modalEditar" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Equipo con inventario</h4>
      </div>
      <div class="modal-body">
        <form action="#" method="post" class="in-line" id="formActualizar">
           <input type="hidden" name="idMob" id="idMobiliario">
           <input type="hidden" name="idServ" id="idServicio">
            <div class="form-group">
               <label for="inv" class="control-label">Inventario :</label>
               <input type="text" name="inv" id="inv" class="form-control  "disabled>
            </div>
            <div class="form-group">
               <label for="desc" class="control-label">Descripción del mobiliario :</label>
               <!-- <input type="text" name="desc" id="desc" class="form-control"> -->
               <textarea name="desc" id="desc" rows=5 class="form-control" disabled></textarea>
            </div>
            <div class="form-group">
               <label for="sol" class="control-label">Servicio solicitado :</label>
               <!-- <input type="text" name="desc" id="desc" class="form-control"> -->
               <textarea name="sol" id="sol" rows=2 class="form-control" disabled></textarea>
            </div>
            <div class="form-group">
               <label for="tipo" class="control-label">Tipo de Servicio :</label>
	                        <select name="tipo" id="tipo" class="form-control select2" style="width: 100%;">
	                          <option selected="selected" value="<?php echo "$row1[2]";?>"><?php echo "$row1[2]";?></option>
	                          <?
	                          for($i=0;$i<$num2;$i++) 
	                          {
	                            $id=mysql_result($combo2,$i,'servicio');
	                            $usuario=mysql_result($combo2,$i,'servicio');
	                            echo "<option value=\"$id\" >$usuario</option>";
	                          }
	                          ?> 
	                        </select>
               <!-- <input type="text" name="tipo" id="tipo" class="form-control " > -->

            </div>
            <div class="form-group">
               <label for="accion" class="control-label">Accion Realizada :</label>
               <!-- <input type="text" name="servicio" id="servicio" class="form-control"> -->
               <textarea name="accion" id="accion" rows=3 class="form-control" required></textarea>
            </div>
            <button type="submit" id="enviar" style="display:none;">enviar</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success" onclick="$('#enviar').click();">Registrar Servicio</button>
      </div>
    </div>

  </div>
</div>

  </div>
</div>
</body>
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
		        }
		    } );
		} );

	</script>
    <!-- page script -->
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
        <script src="procesarFormularios.js"></script>
</html>
