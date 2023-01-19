<?php
/**
 * @package Mediboard\openData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\OpenData\CHDEtablissement;

CCanDo::checkRead();

$HDEtablissement_id = CView::get("HDEtablissement_id", "ref class|CHDEtablissement");

CView::checkin();

$HDEtablissement = new CHDEtablissement();
$HDEtablissement->load($HDEtablissement_id);

$HDEtablissement->needsEdit();

$smarty = new CSmartyDP();
$smarty->assign("HDEtablissement", $HDEtablissement);
$smarty->display("edit_HDEtablissement.tpl");