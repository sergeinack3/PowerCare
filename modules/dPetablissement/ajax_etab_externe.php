<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CEtabExterne;

CCanDo::checkRead();
$etab_id  = CView::get("etab_id", "ref class|CEtabExterne");
$selected = CView::get("selected", "bool default|0");
CView::checkin();

// Récupération des etablissements externes
$etab_externe = new CEtabExterne();
if ($etab_id) {
  $etab_externe->load($etab_id);
  $etab_externe->loadRefsNotes($etab_id);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("etab_externe", $etab_externe);
$smarty->assign("selected"    , $selected);
$smarty->display("inc_etab_externe.tpl");
