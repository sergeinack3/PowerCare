<?php
/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Provenance\CProvenance;

CCanDo::checkAdmin();

$provenance_id = CView::get('provenance_id', 'ref class|CProvenance', true);
$group_id      = CGroups::loadCurrent()->_id;

CView::checkin();

$provenance = CProvenance::findOrNew($provenance_id);

$smarty = new CSmartyDP();
$smarty->assign('group_id', $group_id);
$smarty->assign('provenance', $provenance);
$smarty->display("inc_edit_provenance.tpl");