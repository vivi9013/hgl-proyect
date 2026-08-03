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

mysql_query("SET NAMES utf8");
$consulta1=mysql_query("SELECT
                          servicios.id, 
                          servicios.id_area,
                          servicios.departamento,
                          servicios.id_personaSolicitante,
                          servicios.fecha_peticion,
                          servicios.hora_peticion,
                          servicios.fecha_tomado,
                          servicios.hora_tomado,
                          servicios.fecha_termino,
                          servicios.hora_termino,
                          tipo_servicio.servicio,
                          areas.activo,
                          areas.area,
                          servicios.clasificacion_servicio,
                          servicios.descripcion_servicio,
                          servicios.pendiente,
                          servicios.proceso,
                          servicios.terminado,
                          servicios.liberado,
                          servicios.id_usc,
                          areas.icono,
                          servicios.id_personaServidor,
                          servicios.nombre_Servidor,
                          servicios.sexo_solicitante,
                          servicios.nombre_solicitante,
                          servicios.ext_telefonica,
                          servicios.sede,
                          servicios.abre_sede,
                          servicios.id_sede,
                          servicios.accion_realizada,
                          servicios.tipo_servicio
                        FROM
                          servicios
                        LEFT JOIN tipo_servicio ON servicios.id_tipo_servicio = tipo_servicio.id
                        INNER JOIN areas ON servicios.id_area = areas.id

                        AND 
                          (liberado = 0)  
                        ORDER BY
                          servicios.id desc",$conexion) or die (mysql_error());
     
//Descargamos el arreglo que arroja la consulta
$n=1;

?>
<table id="example1" class="display table table-condensed table-bordered table-hover" 
  <thead>
    <tr class="info">
      <th class="centrarx">#</th>
      <th class="centrarx">Folio</th>
      <th class="centrarx">Atiende</th>
      <th class="centrarx">Proceso</th>
      <th class="centrarx">Terminado</th>
      <th class="centrarx">Acción</th>
    </tr>
  </thead>
  <tbody >
<?php
while ($row1=mysql_fetch_row($consulta1))
{
      $fecha=date("Y-m-d");
      // --------------------------------------------------
      $fechaPet = strtotime($row1[4]);
      $fechaPet=  date("d-m-Y", $fechaPet); //06:23 pm   

      $horaPet = strtotime($row1[5]);
      $horaPet=  date("h:i a", $horaPet); //06:23 pm
      // --------------------------------------------------
      // --------------------------------------------------
      $fechaTom = strtotime($row1[6]);
      $fechaTom=  date("d-m-Y", $fechaTom); //06:23 pm   

      $horaTom = strtotime($row1[7]);
      $horaTom=  date("h:i a", $horaTom); //06:23 pm
      // --------------------------------------------------    
      // --------------------------------------------------
      $fechaTer = strtotime($row1[8]);
      $fechaTer=  date("d-m-Y", $fechaTer); //06:23 pm   

      $horaTer = strtotime($row1[9]);
      $horaTer=  date("h:i a", $horaTer); //06:23 pm
      // --------------------------------------------------   
      $dias=dias_transcurridos($row1[4],$fecha);

    if ($row1[21]!=null) {

      $nombreServer=$row1[22];
      $userFoto=$row1[21];
      $userTipo=$row1[23];
    }
    else{
      $nombreServer="El servicio aún no ha sido elegido";
      $userFoto=0;
      $userTipo='?';
    }
?> 
     <tr>
        
        <!-- NUMERO CONSECUTIVO -->
        <td class="centrarx">
          <?php echo $n; ?>
        </td>
        <!-- NUMERO CONSECUTIVO -->
        
        <!-- FOLIO -->
        <td class="centrarx">
            <a href="javascript:ventanaFolio();" data-toggle="modal" class="btn btn-<?php echo "$sColorCaja";  ?>">
                <i class="<?php echo " $row1[20]"; ?>"></i> <?php echo "$row1[0] | $row1[27] | $dias "; ?>
            </a>
        </td>
        <!-- FOLIO -->
        
        <!-- FOTOGRAFIA -->
        <td class="centrarx">
        <?php 
         ?>
            <a href="#ventanaFoto<?php echo "$n"; ?>" data-toggle="modal" class="btn btn-default btn-xs">
              <img src="<?php echo Fotografia($userFoto,$userTipo); ?>" class="img-circle foto " alt="User Image">                            
            </a> 

        </td>
        <!-- FOTOGRAFIA -->

        <!-- PROCESO -->
        <td class="centrarx">
        <?php 
          $liberar=($row1[15]=='1' && $row1[16]=='1')?' ':'disabled';
          $boton=($row1[15]=='1' && $row1[16]=='1')?'btn btn-success':'btn btn-warning';
          $ico=($row1[15]=='1' && $row1[16]=='1')?'fa fa-check-circle':'fa fa-times-circle';
         ?>
            <a href="#ventanaProceso<?php echo "$n"; ?>" data-toggle="modal" class="<?php echo "$boton $liberar"; ?>">
              <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
            </a> 
        </td>
        <!-- PROCESO -->

        <!-- TERMINADO -->
        <td class="centrarx">
        <?php 
            $liberar=($row1[15]=='1' && $row1[16]=='1' && $row1[17])?' ':'disabled';
            $boton=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'btn btn-success':'btn btn-warning';
            $ico=($row1[15]=='1' && $row1[16]=='1'&& $row1[17])?'fa fa-check-circle':'fa fa-times-circle';
           ?>
            <a href="#ventanaTerminado<?php echo "$n"; ?>" data-toggle="modal" class="<?php echo "$boton $liberar"; ?>">
              <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
            </a> 
        </td>
        <!-- TERMINADO -->

        <!-- ACCION -->
        <td class="centrarx">
          <?php 

              if ($row1[21]==null and $row1[13]==null ) {
                  $liberar=' ';
                  $boton='btn btn-primary';
                  $ico='fa fa-check-circle';
                  ?>
                  <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                  <a href="javascript:confirmar(<?php echo $row1[0];?>)" class="<?php echo "$boton $liberar"; ?>">
                    <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                  </a> 

                  <?php 
              }


                if ($row1[21]<>null and $row1[13]==null ){
                          $liberar=' ';
                          $boton='btn btn-warning';
                          $ico='fa fa-check-square';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:enviarDatos(<?php echo $row1[0];?>)"  class="<?php echo "$boton $liberar"; ?>">
                            <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          </a> 

                          <?php     
                }
              if ($row1[13]<>null and $row1[17]==0 ){
                  switch ($row1[13]) {
                    case 'Con inventario':
                          $liberar=' ';
                          $boton='btn btn-info';
                          $ico='fa fa-dot-circle-o';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <form action="inventariox.php" method="POST">
                          		<input type="hidden" value="<?php echo $row1[0];?>" name="idServicio">
                          		<button type="submit" class="<?php echo "$boton $liberar"; ?>">
                          			<i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          		</button>
                          </form>

                          <?php     
                      break;
                    case 'Sin inventario':
                          $liberar=' ';
                          $boton='btn btn-info';
                          $ico='fa fa-circle-o';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:enviarDatos(<?php echo $row1[0];?>)"  class="<?php echo "$boton $liberar"; ?>">
                            <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          </a> 

                          <?php     
                      break;
                    case 'Especial':
                          $liberar=' ';
                          $boton='btn btn-info';
                          $ico='fa fa-circle';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:enviarDatos(<?php echo $row1[0];?>)"  class="<?php echo "$boton $liberar"; ?>">
                            <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          </a> 

                          <?php     
                      break;
                  }
                }
                if ($row1[15]==1 and $row1[16]==1   and $row1[17]==1  ){
                          $liberar=' ';
                          $boton='btn btn-warning';
                          $ico='fa fa-bell';

                          ?>
                          <!-- SE DIRECCIONA A UNA FUNCION DE JAVA POR EL ENLACE AGREGANDO EL ID -->
                          <a href="javascript:enviarDatos2(<?php echo $row1[0];?>)"  class="<?php echo "$boton $liberar"; ?>">
                            <i class="<?php echo "$ico"; ?> fa-lg" aria-hidden="true"></i>
                          </a> 

                          <?php     
                }
          ?>
        </td>
      </tr>



 <!-- VENTANA MODAL COLUMNA FOTO -->
 <div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaFoto<?php echo "$n"; ?>">
  <div class="modal-dialog">
    <div class="modal-content ">
      
      <div class="modal-header ">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title"><strong><?php echo "Atiende - $nombreServer"; ?></strong></h4>
      </div>
      
      <div class="modal-body">
        <p class="justificado">
        <a href="<?php echo "#ventanax$n"; ?>" data-toggle="modal">
            <img src="<?php echo Fotografia($userFoto,$userTipo); ?>" class="img-thumbnail fotomodal" alt="User Image">
          </a> 
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
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaProceso<?php echo "$n"; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title"><?php echo "Información del servicio en proceso con folio - <strong>$row1[0]</strong>"; ?></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <?php echo "Persona a la que se atiende - <strong>$row1[24]</strong>" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <?php echo "El servicio fue solicitado el dia  <strong>$fechaPet</strong> a las <strong>$horaPet</strong>" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <?php echo "Extensión telefónica - <strong>$row1[25]</strong> | <strong>$row1[26]</strong>" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <?php echo "El servicio fue tomado el dia  <strong>$fechaTom</strong> a las <strong>$horaTom</strong>" ; ?>
        </p>

        <p>
          <?php 
              switch ($row1[13]) {
                case 'Con inventario':
                    $cServicio="El servicio incluye mobiliario con un inventario registrado";
                break;
                case 'Sin inventario':
                    $cServicio="El servicio incluye mobiliario sin un inventario registrado";
                break;
                case 'Especial':
                    $cServicio="El servicio pertenece a una peticion especial y en la cual no hay mobiliario involucrado";
                break;
                default:
                   $cServicio="Aún no se ha seleccionado ningun tipo de servicio";
                  break;
              }
           ?>
           <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <?php echo "$cServicio" ; ?>
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
<div class="modal fade modal-<?php echo "$sColorCaja";  ?>" id="ventanaTerminado<?php echo "$n"; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <button tyle="button" class="close" data-dismiss="modal" aria-hidden="true">&times; </button>
        <h4 class="modal-title"><?php echo "Información del servicio en proceso con folio - <strong>$row1[0]</strong>"; ?></h4>
      </div>
      
      <div class="modal-body ">
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <?php echo "Persona a la que se atiende - <strong>$row1[24]</strong>" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <?php echo "El servicio fue solicitado el dia  <strong>$fechaPet</strong> a las <strong>$horaPet</strong>" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <?php echo "Extensión telefónica - <strong>$row1[25]</strong> | <strong>$row1[26]</strong>" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <?php echo "El servicio fue tomado el dia  <strong>$fechaTom</strong> a las <strong>$horaTom</strong>" ; ?>
        </p>

        <p>
          <?php 
              switch ($row1[13]) {
                case 'Con inventario':
                    $cServicio="El servicio incluye mobiliario con un inventario registrado";
                break;
                case 'Sin inventario':
                    $cServicio="El servicio incluye mobiliario sin un inventario registrado";
                break;
                case 'Especial':
                    $cServicio="El servicio pertenece a una peticion especial y en la cual no hay mobiliario involucrado";
                break;
                default:
                   $cServicio="Aún no se ha seleccionado ningun tipo de servicio";
                  break;
              }
           ?>
           <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>
          <?php echo "$cServicio" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          <?php echo "El servicio fue terminado el dia  <strong>$fechaTer</strong> a las <strong>$horaTer</strong>" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          <?php echo "<strong>Solucion :</strong> $row1[29]" ; ?>
        </p>
        <p class="izquierda">
          <i class="fa fa-chevron-left" aria-hidden="true"></i>
          <?php echo "<strong>Tipo de Servicio :</strong> $row1[30]" ; ?>
        </p>
      </div>

      <div class="modal-footer "> 
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
 <!-- VENTANA MODAL -->
<?php 
++$n;
}
?>

    </tbody>
    <tfoot>
      <tr>
        <th class="centrarx">#</th>
        <th class="centrarx">Folio</th>
        <th class="centrarx">Atiende</th>
        <th class="centrarx">Proceso</th>
        <th class="centrarx">Terminado</th>
        <th class="centrarx">Acción</th>
      </tr>
    </tfoot>
  </table>


