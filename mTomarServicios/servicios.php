<?php
include "../conexion/conexion.php";

$area= $_POST["id_area"];

mysql_query("SET NAMES utf8");

$consulta = mysql_query("SELECT
							id,
							servicio
						FROM
							tipo_servicio
						WHERE
							id_area = '$area'
						AND activo = 1
						ORDER BY servicio",$conexion)or die(mysql_error());

while($row = mysql_fetch_row($consulta))
{  
	if ($rowl[0]!=$row[0]) {
    ?>
    <option value="<?php echo $row[1];?>"><?php echo $row[1];?></option>
    <?php
	}

}
?>
<script>

  // $("#serviciosSI").select2();
  // $("#serviciosSM").select2();
  // $("#servicios").select2();
</script>