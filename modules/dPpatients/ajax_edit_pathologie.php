<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPathologie;

CCanDo::checkEdit();

$pathologie_id = CView::get("pathologie_id", "ref class|CPathologie");

CView::checkin();

$pathologie = new CPathologie();
$pathologie->load($pathologie_id);

$smarty = new CSmartyDP();

$smarty->assign("pathologie", $pathologie);

$smarty->display("inc_edit_pathologie.tpl");