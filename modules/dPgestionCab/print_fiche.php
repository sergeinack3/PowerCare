<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\GestionCab\CFichePaie;

CCanDo::checkRead();

$fiche_paie_id = CValue::getOrSession("fiche_paie_id", null);

$fichePaie = new CFichePaie();
$fichePaie->load($fiche_paie_id);

if (!$fichePaie->fiche_paie_id) {
  CAppUI::setMsg("Vous n'avez pas choisi de fiche de paie", UI_MSG_ERROR);
  CAppUI::redirect("m=dPgestionCab&tab=edit_paie");
}

if ($fichePaie->final_file) {
    echo $fichePaie->final_file;
}
else {
  $fichePaie->loadRefsFwd();
  $fichePaie->_ref_params_paie->loadRefsFwd();

  // Création du template
  $smarty = new CSmartyDP();

  $smarty->assign("fichePaie" , $fichePaie);

  $smarty->display("print_fiche");
}
