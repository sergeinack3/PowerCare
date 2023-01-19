<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;

CCanDo::checkRead();

$_GET["all_prats"] = "1";
$_GET['display_operations'] = 1;
$_GET['display_consultations'] = 0;
$_GET['display_seances'] = 0;
$_GET['display_sejours'] = 0;

CAppUI::requireModuleFile("dPboard", "vw_interv_non_cotees");
