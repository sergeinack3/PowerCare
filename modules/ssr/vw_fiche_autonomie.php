<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CFicheAutonomie;

CCanDo::checkRead();
$sejour_id = CValue::getOrSession("sejour_id");

CAccessMedicalData::logAccess("CSejour-$sejour_id");

// Fiche autonomie
$fiche_autonomie            = new CFicheAutonomie();
$fiche_autonomie->sejour_id = $sejour_id;
$fiche_autonomie->loadMatchingObject();

// Bilan SSR
$bilan            = new CBilanSSR();
$bilan->sejour_id = $sejour_id;
$bilan->loadMatchingObject();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("fiche_autonomie", $fiche_autonomie);
$smarty->assign("bilan", $bilan);

$smarty->display("inc_form_fiche_autonomie");
