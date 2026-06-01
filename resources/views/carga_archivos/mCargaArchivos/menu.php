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

                <i class="fa fa-archive"></i> <span>Archivos</span> 
                
                <ul class="treeview-menu">
                  <li <?php echo "$va1"; ?>>
                    <a href="index.php"><i class="fa fa-plus-square-o" aria-hidden="true"></i>
                      Alta
                    </a>
                  </li>
                  <li <?php echo "$va2"; ?>>
                    <a href="lista.php"><i class="fa fa-list-ul" aria-hidden="true"></i>
                      Lista y Edición
                    </a>
                  </li>
                  <li <?php echo "$va3"; ?>>
                    <a  href="reportes.php"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                      <!-- <small class="label pull-right bg-yellow">Reporte</small> -->
                      Reportes
                    </a>
                  </li>
                  <li <?php echo "$va4"; ?>>
                    <a  href="graficas.php"><i class="fa fa-line-chart" aria-hidden="true"></i>
                      <!-- <small class="label pull-right bg-yellow">Reporte</small> -->
                      Gráficas
                    </a>
                  </li>
                </ul>
              </a>
            </li>
