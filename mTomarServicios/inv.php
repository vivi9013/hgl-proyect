<?php 


//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

//Funcion que permite mostrar foto
include("../funciones/mostrarFoto.php");

include'../funciones/diasTranscurridos.php';

include("combos.php");

$pIdArea=$_POST["id_area"]; 
$pIdServicio=$_POST["id_servicio"]; 
$pServSol=$_POST["ServSol"];

?>
<!-- <script src="funciones.js"></script> -->

<div class="row">
<div class="col-xs-12">

  <div class="box box-<?php echo "$sColorCaja";  ?>">
    <div class="box-header">
      <h3 class="box-title">Lista de Mobiliario</h3> 
      <input type="hidden" value="<?php echo "$pIdArea"; ?>" id="idArea">
      <input type="hidden" value="<?php echo "$pIdServicio"; ?>" id="idServiciox">
      <div class="box-tools pull-right">
        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
      </div>
    </div><!-- /.box-header -->
    <div class="box-body">
      <div class="table-responsive">
                  <table id="example2" class="table table-condensed table-bordered table-striped ">
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
                                          mobiliario.id_area = $pIdArea AND mobiliario.activo=1",$conexion) or die (mysql_error());
                     
                  //Descargamos el arreglo que arroja la consulta
                  $n=1;

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
                      <a href="javascript:ventanaDesc('<?php echo $row[3];?>','<?php echo $row[1];?>','<?php echo $row[6];?>','<?php echo $row[2];?>','<?php echo $row[7];?>','<?php echo $row[4];?>','<?php echo $row[5];?>')" class="btn btn-<?php echo "$sColorCaja";  ?>">
                        <i class="fa fa-eye" aria-hidden="true"></i>
                      </a>
                    </td>
                    <td ><?php echo $row[4]; ?></td>
                    <td ><?php echo $row[5]; ?></td>
                    <td class="centrar"> 
                        <a href="javascript:modificar('<?php echo $row[0];?>','<?php echo $pIdServicio;?>','<?php echo $row[3];?>','<?php echo $row[1];?>','<?php echo $row1[0];?>','<?php echo $row1[1];?>','<?php echo "$row1[2]";?>')" class="btn btn-<?php echo "$boColor"; ?>"><i class="fa fa-check" aria-hidden="true"></i></a>
                        <!--                                idMobiliario            idServicio                   inventario                 descripcion            accionRealizada          sol                         tipo-->
                    </td>
                  </tr>

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
      </div>
    </div><!-- /.box-body -->
  </div><!-- /.box -->
</div><!-- /.col -->
</div><!-- /.row -->       

<!-- VENTANA MODAL COLUMNA FOLIO -->
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaDesc">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title">Descripcion del equipo con inventario - <label id="inv"></label></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <strong>Descripcion del equipo </strong><label id="desc"></label>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Marca - <strong><label id="marca"></label></strong> | Modelo - <strong><label id="modelo"></label></strong> | Serie <strong><label id="serie"></label></strong>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Responsable - <strong><label id="resp"></label></strong>
        </p>
        <p class="izuierda ">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          Tipo de Mobiliario - <strong><label id="tipo"></label></strong>
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

<!-- modal para editar los registros -->
<div id="modalEditar" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Servicio mobiliario con inventario - Folio -<label id="folio"></label></h4>
      </div>
      <div class="modal-body">
        <form action="#" method="post" class="in-line" id="formActualizarx">
           <input type="hidden" name="idMob" id="idMobiliario">
           <input type="hidden" name="idServ" id="idServicio">
            <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon" >Inventario</span>
              <input type="text" class="form-control" name="inv" id="invx" disabled>
            </div>
            </div>

            <div class="form-group">
               <label for="desc" class="control-label">Descripción del mobiliario :</label>
               <!-- <input type="text" name="desc" id="desc" class="form-control"> -->
               <textarea name="desc" id="descx" rows=1 class="form-control" disabled></textarea>
            </div>
            <div class="form-group">
               <label for="sol" class="control-label">Servicio solicitado :</label>
               <!-- <input type="text" name="desc" id="desc" class="form-control"> -->
               <textarea name="sol" id="sol"  value="" rows=1 class="form-control" disabled><?php echo "$pServSol"; ?></textarea>
            </div>
            <div class="form-group">
               <label for="tipo" class="control-label">Tipo de Servicio :</label>
                <select class="form-control select2" style="width: 100%;"  id="servicios"  required="" onchange="MSJmostrarValor(this.value);">
                </select>

            </div>
            <div class="form-group">
               <label for="accion" class="control-label">Accion Realizada :</label>
               <!-- <input type="text" name="servicio" id="servicio" class="form-control"> -->
               <textarea name="accion" id="accion" rows=1 class="form-control" required placeholder="Escribe..." onclick="MSJaccion();"></textarea>
            </div>
            <button type="submit" id="enviarx" style="display:none;">enviar</button>
        </form>
      </div>
      <div class="modal-footer">
        <a href="#"  onclick="cancelarSeleccion($('#idServicio').val())" class="btn btn-default pull-left">Cancelar selección de servicio</a>
        <button type="submit" class="btn btn-success" id="botonI" onclick="$('#enviarx').click();">Registrar Servicio</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        
      </div>
    </div>

  </div>
</div>


<script type="text/javascript">

$(document).ready(function() {

    $('#example2').DataTable( {
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

  <script>
    $(function () {
      //Initialize Select2 Elements
      $(".select2").select2();

    });
  </script> 

  <script src="procesarFormularios.js"></script>