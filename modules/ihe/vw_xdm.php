<?php

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

//template
$smarty = new CSmartyDP();
$smarty->display("vw_xdm.tpl");