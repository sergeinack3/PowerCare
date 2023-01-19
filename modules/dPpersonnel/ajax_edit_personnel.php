<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Personnel\CPersonnel;

CCanDo::checkRead();

$personnel_id = CView::get("personnel_id", "ref class|CPersonnel", true);
$multiple     = CView::get("multiple", "bool default|0");

CView::checkin();

$personnel = new CPersonnel();
$personnel->load($personnel_id);
$personnel->loadRefUser();

$personnel->loadBackRefs("affectations", "affect_id DESC", "0,20");
$personnel->countBackRefs("affectations");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("personnel", $personnel);

$template = 'inc_form_personnel.tpl';
if ($multiple) {
  $template = 'inc_edit_multiple_personnel.tpl';
}

$smarty->display($template);