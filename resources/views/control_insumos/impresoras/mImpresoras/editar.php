<?php 
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

//combo
include'combos.php';

$idimpresora=$_GET['id'];
$idpersonas=$_GET['per'];
//Cambio de piel del sistema
$skin="skin-".$sSkin;

                  mysql_query("SET NAMES utf8");
                  $consulta=mysql_query("SELECT
                                            id_impresora,
                                            inventario,
                                            serie,
                                            modelo,
                                            marca,
                                            descripcion,
                                            tecnologia,
                                            consumible,
                                            red,
                                            ip,
                                            tipo,
                                            comodato,
                                            activo
                                          FROM impresoras
                                        WHERE 
                                          id_impresora ='$idimpresora'",$conexion) or die (mysql_error());
                                          $row=mysql_fetch_row($consulta);

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Custom functions file -->
    <script src="../plugins/alertaModal/js/functions.js"></script>
    <!-- Sweet Alert Script -->
    <script src="../plugins/alertaModal/js/sweetalert.min.js"></script>
    <!-- ACCIONES MODALES -->

  <!-- VALIDAR IP -->
  <script type="text/javascript" src="../plugins/checkBdWord/validar.js" ></script> 
  <script type="text/javascript">
  $(document).ready(function() {  
    $('#ip').blur(function(){
      
      $('#Info').html('<img  class="valCorrecto" src="../plugins/checkBdWord/loader.gif" alt="" />').fadeOut(1000);

      var username = $(this).val();   
      var dataString = 'ip='+username;
      
      $.ajax({
              type: "POST",
              url: "../plugins/checkBdWord/ipImpresora/check_username_availablity.php",
              data: dataString,
              success: function(data) {
          $('#Info').fadeIn(0).html(data);
          //alert(data);
              }
          });
      });              
  });    
  </script> 

   <!-- VALIDAR NOMBRE DE EQUIPO -->
  <script type="text/javascript" src="../plugins/checkBdWord/validar.js" ></script> 
  <script type="text/javascript">
  $(document).ready(function() {  
    $('#nomEquipo').blur(function(){
      
      $('#Info1').html('<img  class="valCorrecto" src="../plugins/checkBdWord/loader.gif" alt="" />').fadeOut(1000);

      var username1 = $(this).val();   
      var dataString = 'nomEquipo='+username1;
      
      $.ajax({
              type: "POST",
              url: "../plugins/checkBdWord/nomEquipo/check_username_availablity.php",
              data: dataString,
              success: function(data) {
          $('#Info1').fadeIn(0).html(data);
          //alert(data);
              }
          });
      });              
  });    
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
            Impresoras
            <small>Altas</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
            <li><a href="lista.php"><i class="fa fa-list"></i>Lista</a></li>
            <li class="active">Altas</li>
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
                  swal(\"¡Operación Satisfactoria!\", \"El registro se ha guardado correctamente.\", \"success\");
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
            <div class="col-xs-12">

              <div class="box box-<?php echo "$sColorCaja";  ?>">
                <div class="box-header">
                  <h3 class="box-title">Registra la Información solicitada</h3>
              <div class="box-tools pull-right">
                <button class="btn bt
                n-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>

              </div>
                </div><!-- /.box-header -->
                <form name='formulario' method='post' action='actualizar.php'>
                <input type="hidden" name="id_impresora" value="<?php echo $idimpresora;?>">     

                    <div class="box-body">

                    <div class="row">
                    
                       <div class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
                        <div class="form-group">
                            <label for="nombre">Inventario:</label>
                            <select name="inventario" class="form-control select2" id="inventario" style="width: 100%;">
                            <?php
                            for($i=0;$i<$num3;$i++) 
                            {
                              $id=mysql_result($combo3,$i,'inventario');
                              $nombre=mysql_result($combo3,$i,'inventario');
                              echo "<option value=\"$id\" >$nombre</option>";
                            }
                            ?> 
                          </select>
                            <!--<input type="text" name="inventario" class="form-control" id="inventario"  placeholder="Coloca el numero de inventario" autofocus required>-->
                            
                        </div>
                      </div>
                        
                      <div class="col-xs-12 col-sm-4 col-md-4 <col-lg-34></col-lg-4>">
                          <div class="form-group">
                            <label for="tipo">Tipo de Impresora:</label>
                            <select name="tipo" class="form-control select2" id="tipo" style="width: 100%;">
                            <option value="<?php echo $row[10];?>"><?php echo $row[10]; ?></option>
                            <option value="Lasser">Lasser</option>
                            <option value="Inyeccion de tinta">Inyeccion de Tinta</option>
                            <option value="Matriz de Puntos">Matriz de Puntos</option>
                            <option value="Terminca">Termica</option>
                          </select>                      
                        </div>
                      </div>

                      <div class="col-xs-12 col-sm-4 col-md-4 <col-lg-4></col-lg-4>">
                        <div class="form-group">
                            <label for="serie">Serie:</label>
                            <input type="text" name="serie" class="form-control" value="<?php echo $row[2] ?>" id="serie" placeholder="Coloque el numero de serie de la impresora" autofocus required>
                        </div>
                      </div>
                      

                    </div><!-- /.row -->
     
                    <div class="row">
                    
                      <div class="col-xs-12 col-sm-6 col-md-3 <col-lg-3></col-lg-3>">
                        <div class="form-group">
                          <label for="modelo">Modelo:</label>
                          <input type="text" name="modelo" class="form-control" value="<?php echo $row[3] ?>" id="modelo" placeholder="Coloque el modelo de la impresora">    
                        </div><!-- /.form-group -->
                      </div>

                       <div class="col-xs-12 col-sm-6 col-md-3 <col-lg-3></col-lg-3>">
                        <div class="form-group">
                            <label for="marca">Marca:</label>
                            <input type="text" name="marca" class="form-control" value="<?php echo $row[4] ?>" id="marca" placeholder="Coloca la marca de la impresora" autofocus required>
                        </div>
                      </div>

                      <div class="col-xs-12 col-sm-6 col-md-3 <col-lg-3></col-lg-3>">
                          <div class="form-group">
                            <label for="descripcion">Descripción:</label>
                            <input type="text" name="descripcion" class="form-control" value="<?php echo $row[5] ?>" id="descripcion" placeholder="Coloque una descripcion de la impresora" autofocus required>
                        </div>
                      </div>
                      
                      <div class="col-xs-12 col-sm-6 col-md-3 <col-lg-3></col-lg-3>">
                        <div class="form-group">
                            <label for="tecnologia">Tecnologia:</label>
                            <input type="text" name="tecnologia" class="form-control" value="<?php echo $row[6] ?>" id="tecnologia" placeholder="Coloque la tecnologia de la impresora" autofocus required>
                        </div>
                      </div>
                      

                    </div><!-- /.row -->

                  <div class="row">

                    <div class="col-xs-12 col-sm-6 col-md-3 <col-lg-3></col-lg-3>">
                        <div class="form-group">
                          <label>Consumible:</label>
                           <select name="consumible" class="form-control select2" id="consumible" style="width: 100%;">
                              <option value="<?php echo $row[7];?>"><?php echo $row[7]; ?></option>
                              <option value="Tonner">Tonner</option>
                              <option value="Cartucho">Cartucho</option>
                              <option value="Cinta">Cinta</option>
                          </select>                              
                        </div><!-- /.form-group -->
                      </div>
                      
                      <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label>Red:</label>
                             <select name="red" class="form-control select2" id="red" style="width: 100%;">
                             <option value="<?php echo $row[8];?>"><?php echo $row[8]; ?></option>
                              <option value="Si">Si</option>
                              <option value="No">No</option>
                          </select>                              
                        </div>
                      </div>    

                      <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label for="ip" id="Info">IP:</label>  
                            <input type="text" name="ip" class="form-control" value="<?php echo $row[9] ?>" id="ip" value="10.19.36." placeholder="Coloque la ip de la impresora" autofocus required>                    
                        </div>

                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                        <div class="form-group">
                            <label>Comodato:</label>  
                            <select name="comodato" class="form-control select2" id="comodato" style="width: 100%;">
                              <option value="<?php echo $row[11];?>"><?php echo $row[11]; ?></option>
                              <option value="Si">Si</option>
                              <option value="No">No</option>
                          </select>                              
                        </div>

                    </div>
                    </div>
                    </div>
                    <!-- /.box-body -->
                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right">Guardar Información</button>
                  </div>
                  </form>
                </div><!-- /.box -->

            </div><!-- /.col -->
          </div><!-- /.row --><!-- Alta de Actividades -->

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
</html>
