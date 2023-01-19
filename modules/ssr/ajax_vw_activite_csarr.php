<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CActiviteCsARR;

CCanDo::checkRead();
$code = CView::get("code", "str");
CView::checkin();

$type_seance = array();

$activite = CActiviteCsARR::get($code);

$reference = $activite->_ref_reference;

$type_seance = array(
  "dediee"     => $reference->dedie != 'non' ? false : true,
  "non_dediee" => $reference->non_dedie != 'non' ? false : true,
  "collective" => false
);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("activite"   , $activite);
$smarty->assign("type_seance", $type_seance);
$smarty->display("inc_vw_activite_csarr");
