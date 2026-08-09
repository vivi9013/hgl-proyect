<?php 
//se manda llamar la conexion
include'../conexion/conexion.php';

//verifico inicio de sesion
include'../sesiones/verificar_sesion.php';

//cargo variables de sesion
include'../sesiones/variables_sesion.php';

//Funcion que permite mostrar foto
include '../funciones/mostrarFoto.php';

include'../funciones/diasTranscurridos.php';
 ?>

<div class="container-fluid">
  <div class="row">
              <a href="javascript:llenarSolPendientes()"  class="btn btn-<?php echo "$sColorCaja centrar";  ?> pull-right marco">
                <i class="fa fa-reply-all" aria-hidden="true"></i> Lista de servicios pendientes.
              </a> 
  </div>
</div>



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
                          mobiliario.activo = 1",$conexion) or die (mysql_error());
     
  //Descargamos el arreglo que arroja la consulta
  $n=1;
  while ($row=mysql_fetch_row($consulta))
  {
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
<a href="javascript:modificar('<?php echo $row[2];?>','<?php echo $row[3];?>')">Modificar modal</a>
    </td>
  </tr>
  <!-- VENTANA MODAL COLUMNA descripcion -->
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
          <p class="izquierda ">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
            <?php echo "Marca - <strong>$row[6]</strong> | Modelo - <strong>$row[2]</strong> | Serie <strong>$row[7]</strong>" ; ?>
          </p>
          <p class="izquierda ">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
            <?php echo "Responsable - <strong>$row[4]</strong>" ; ?>
          </p>
          <p class="izquierda ">
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
<!-- jQuery 2.1.4 -->

<!-- Bootstrap 3.3.5 -->
<!-- <script src="../bootstrap/js/bootstrap.min.js"></script> -->
<!-- Select2 -->
<script src="procesarFormularios.js"></script>
<!-- DataTables -->
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>


<script src="../plugins/datatables/dataTables.bootstrap.min.js"></script>


<script type="text/javascript">

$(document).ready(function() {
  
    $('#example2').DataTable( {
        "language": {
           // "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            "url": "../plugins/datatables/langauge/Spanish.json"
        }
    } );
} );

</script>