<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkEdit();

$grossesse_id = CValue::get("grossesse_id");

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefParturiente();

$dossier = $grossesse->loadRefDossierPerinat();

$sejours = $grossesse->loadRefsSejours();
foreach ($sejours as $sejour) {
  $sejour->loadRefPraticien();
  $sejour->getSA();
}

$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);

$smarty->display("dossier_tableau_hospit_grossesse.tpl");
