<?php

/**
 * @package Mediboard\Livi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 *
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();
$smarty->display("configure.tpl");
