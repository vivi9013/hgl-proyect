    <?php 
switch ($opa) {
  case 'A':
        $va1="class=\"active\"";
    break;
  case 'B':
        $va2="class=\"active\"";
    break;
  case 'C':
        $va3="class=\"active\"";
    break;
  case 'D':
        $va4="class=\"active\"";
    break;
  case 'F':
        $va5="class=\"active\"";
    break;
  case 'G':
        $va6="class=\"active\"";
    break;
  case 'H':
        $va6="class=\"active\"";
    break;
  case 'I':
        $va6="class=\"active\"";
    break;
}
 ?>
            <li class="active ">
              <a href="#">
                <i class="fa fa-wrench"></i> <span>Tomar Servicios</span> 
                <ul class="treeview-menu">
                  <li <?php echo "$va1"; ?>>
                    <a href="index.php"><i class="fa fa-th-list" aria-hidden="true"></i>
                      Servicios Pendientes
                    </a>
                  </li>

                  <li <?php echo "$va2"; ?>>
                    <a href="seguimiento.php"><i class="fa fa-eye" aria-hidden="true"></i>
                      Seguimiento de servicios
                    </a>
                  </li>

                  <li <?php echo "$va3"; ?>>
                    <a  href="libercancel.php"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                      <!-- <small class="label pull-right bg-yellow">Reporte</small> -->
                      Liberados y cancelados
                    </a>
                  </li>

                </ul>
              </a>
            </li>
