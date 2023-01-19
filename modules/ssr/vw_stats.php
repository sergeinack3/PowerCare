<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("date", CMbDT::date());

$smarty->display("vw_stats");
