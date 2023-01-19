<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Pmsi\CCIM10;

CCanDo::checkRead();
$code = CView::get("code", "str");
CView::checkin();

$cim = CCIM10::get($code);

echo json_encode(array("type" => $cim->type_mco, "code" => $code, "shortname" => $cim->libelle_court, "longname" => $cim->libelle));