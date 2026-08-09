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
$opa="A";
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
    <!-- Sweet Alert -->
    <link rel="stylesheet" href="../plugins/sweetalert2-master/dist/sweetalert2.css">
    <!-- alertifi -->
    <link rel="stylesheet" href="../plugins/alertifyjs/css/alertify.css">
    <link rel="stylesheet" href="../plugins/alertifyjs/css/themes/default.css">

    <link rel="stylesheet" type="text/css" href="../dist/css/animate.css">

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
          <h1 id="titulo">Lista de Servicios</h1>
            <a href="javascript:llenarSolPendientes();" class="btn btn-primary btn-md" id="botonTitle"> <i class="fa fa-reply" aria-hidden="true"></i> &nbsp; Regresar a la lista de servicios</a>
          
          <ol class="breadcrumb">
            <li><a href="../inicio/index.php"><i class="fa fa-dashboard"></i> Panel de Control</a></li>
            <li><a href="index.php"><i class="fa fa-home"></i>Catalogo</a></li>
            <li class="active">Lista de servicios</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content" id="resultadoSolPendientes">
          <!-- Acordeon -->
        </section><!-- /.content -->
        <section class="content" id="inventarios">
          <!-- Acordeon -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

<!-- CODIGO VENTANAS MODALES - PRIMERA LISTA DE SERVICIOS -->
<!-- VENTANA MODAL COLUMNA FOLIO -->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaFolio">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title"><?php echo "Información de la solicitud de servicio"; ?></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-asterisk" aria-hidden="true"></i>
          <strong>Descripción del servicio solicitado </strong> : <label id="descVF"></label>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El folio del servicio es  
          <strong><label id="folioVF"></label></strong> 
          ligado al area de 
          <strong><label id="areaVF"></label></strong>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El servicio fue solicitado el dia  
          <strong><label id="fechaVF"></label></strong> 
          a las 
          <strong><label id="horaVF"></label></strong>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          La sede a la que pertence el servicio es <strong><label id="sedeVF"></label></strong>
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

<!-- Abrir ventana modal -->
 <!-- VENTANA MODAL COLUMNA PROCESO-->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaProceso">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title">Información del servicio en proceso con folio - <strong><label id="folioVP"></label></strong></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Persona a la que se atiende - <strong><label id="solicitanteVP"></label></strong>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El servicio fue solicitado el dia  
          <strong><label id="fechaVP"></label></strong> 
          a las 
          <strong><label id="horaVP"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Extensión telefónica - <strong><label id="telVP"></label></strong> | <strong><label id="sedeVP"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          El servicio fue tomado el dia  <strong><label id="fechaVPt"></label></strong> a las <strong><label id="horaVPt"></label></strong>
        </p>

        <p>
           <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <label id="servicioVP"></label>
        </p>
      </div>

      <div class="modal-footer "> 
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
 <!-- VENTANA MODAL -->
<!-- Cerrar venana modal -->
 <!-- VENTANA MODAL COLUMNA TERMINO-->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaTerminado">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title">Información del servicio en proceso con folio - <strong><label id="folioVT"></label></strong></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Persona a la que se atiende - <strong><label id="solicitanteVT"></label></strong>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          El servicio fue solicitado el dia  
          <strong><label id="fechaVT"></label></strong> 
          a las 
          <strong><label id="horaVT"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Extensión telefónica - <strong><label id="telVT"></label></strong> | <strong><label id="sedeVT"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          El servicio fue tomado el dia  <strong><label id="fechaVPtT"></label></strong> a las <strong><label id="horaVPtT"></label></strong>
        </p>

        <p>
           <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <label id="servicioVT"></label>
        </p>

        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          El servicio fue terminado el dia  <strong><label id="fechaVTER"></label></strong> a las <strong><label id="horaVTER"></label></strong>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          <strong>Solucion :</strong> <label id="solVTER"></label>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          <strong>Tipo de Servicio :</strong> <label id="tipoVTER"></label>
        </p>
      </div>

      <div class="modal-footer "> 
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
 <!-- VENTANA MODAL -->
<!-- TERMINO LISTA DE SERVICIOS -->

<!-- MODALES actualizar SERVICIO -->

<!-- modal para actualizar servicio sin inventario -->
<div id="modalSinMob" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Servicio especial sin Mobiliario - Folio -<label id="folioSM"></label></h4>
      </div>
      <div class="modal-body">
        <form action="#" method="post" class="in-line" id="formActualizarxSM">
           <input type="hidden" name="idMob" id="idMobiliarioSM">
           <input type="hidden" name="idServ" id="idServicioSM">
            <div class="form-group">
               <input type="hidden" name="inv" id="invxSM" class="form-control" >
            </div>
            <div class="form-group">
               <input type="hidden" name="descxSM" id="descxSM" class="form-control" >
            </div>
            <div class="form-group">
               <label for="sol" class="control-label">Servicio solicitado :</label>
               <!-- <input type="text" name="desc" id="desc" class="form-control"> -->
               <textarea name="sol" id="solSM"  value="" rows=1 class="form-control" disabled><?php echo "$pServSol"; ?></textarea>
            </div>
            <div class="form-group">
               <label for="tipo" class="control-label">Tipo de Servicio :</label>
                <select class="form-control select2" style="width: 100%;"  id="serviciosSM" required="" onchange="MSJmostrarValor(this.value);">
                </select>

            </div>
            <div class="form-group">
               <label for="accion" class="control-label">Acción Realizada :</label>
               <!-- <input type="text" name="servicio" id="servicio" class="form-control"> -->
               <textarea name="accion" id="accionSM" rows=1 class="form-control" required placeholder="Escribe..." onclick="MSJaccion();"></textarea>
            </div>
            <button type="submit" id="enviarxSM" style="display:none;">enviar</button>
        </form>
      </div>
      <div class="modal-footer">
        <a href="#"  onclick="cancelarSeleccion($('#idServicioSM').val())" class="btn btn-default pull-left">Cancelar selección de servicio</a>
        <button type="submit" class="btn btn-success" id="botonSM" onclick="$('#enviarxSM').click();">Registrar Servicio</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
      </div>
    </div>

  </div>
</div>

<!-- modal para actualizar servicio sin inventario -->
<div id="modalSinInv" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Equipo sin inventario - Folio - <label id="folioSI"></label></h4>
      </div>
      <div class="modal-body">
        <form action="#" method="post" class="in-line" id="formActualizarxSI">
           <input type="hidden" name="idMob" id="idMobiliarioSI">
           <input type="hidden" name="idServ" id="idServicioSI">
            <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon">Inventario</span>
              <input type="text" class="form-control" name="inv" id="invxSI" onclick="MSJseleccionarTexto('invxSI');">
            </div>
            </div>
            <div class="form-group">
               <label for="desc" class="control-label">Descripción del mobiliario :</label>
               <!-- <input type="text" name="desc" id="desc" class="form-control"> -->
               <textarea name="desc" id="descxSI" rows=1 class="form-control" onclick="MSJseleccionarTexto('descxSI')"></textarea>
            </div>
            <div class="form-group">
               <label for="sol" class="control-label">Servicio solicitado :</label>
               <!-- <input type="text" name="desc" id="desc" class="form-control"> -->
               <textarea name="sol" id="solSI"  value="" rows=1 class="form-control" disabled><?php echo "$pServSol"; ?></textarea>
            </div>
            <div class="form-group">
               <label for="tipo" class="control-label">Tipo de Servicio :</label>
                <select class="form-control select2" style="width: 100%;"  id="serviciosSI" required="" onchange="MSJmostrarValor(this.value);">
                </select>

            </div>
            <div class="form-group">
               <label for="accion" class="control-label">Acción Realizada :</label>
               <!-- <input type="text" name="servicio" id="servicio" class="form-control"> -->
               <textarea name="accion" id="accionSI" rows=1 class="form-control" required placeholder="Escribe..." onclick="MSJaccion();"></textarea>
            </div>
            <button type="submit" id="enviarxSI" style="display:none;">enviar</button>
        </form>
      </div>
      <div class="modal-footer">
        <a href="#"  onclick="cancelarSeleccion($('#idServicioSI').val())" class="btn btn-default pull-left">Cancelar selección de servicio</a>
        <button type="submit" class="btn btn-success" id="botonSI" onclick="$('#enviarxSI').click();">Registrar Servicio</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>

      </div>
    </div>

  </div>
</div>



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

    <script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>


    <!-- Select2 -->
    <script src="../plugins/select2/select2.full.min.js"></script>

    <!-- Sweet Alert Script -->
    <script src="../plugins/sweetalert2-master/dist/sweetalert2.js"></script>

    <!-- AdminLTE App -->
    <script src="../dist/js/app.min.js"></script>



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

  <script>
    $(function () {
      //Initialize Select2 Elements
      $(".select2").select2();

    });
  </script> 

<script src="../plugins/alertifyjs/alertify.js"></script>

<script src="funciones.js"></script>
<script src="procesarFormularios.js"></script>
<!-- inicio script -->
<script type="text/javascript">
	llenarSolPendientes();
</script>
</html>