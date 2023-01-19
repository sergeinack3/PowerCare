<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id  = CView::post("sejour_id", "ref class|CSejour");
$service_id = CView::post("service_id", "ref class|CService");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

$sejour->loadNDA();
$seances = $sejour->loadListFromNDA($sejour->_NDA);
$entree_reelle = $sejour->entree_reelle;

$sejour->entree_reelle = "";

$msg = $sejour->store();

CAppUI::setMsg($msg ? : CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

// Annulation de la séance courante
$sejour->annule = 1;

$msg = $sejour->store();

CAppUI::setMsg($msg ? : CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

// Création du séjour en hospi complète
$sejour_hospi = new CSejour();
$sejour_hospi->entree_reelle = $entree_reelle;

$fields = array(
  "patient_id", "praticien_id", "group_id", "libelle",
  "entree_prevue", "sortie_prevue"
);

foreach ($fields as $_field) {
  $sejour_hospi->$_field = $sejour->$_field;
}

$sejour_hospi->_id        = "";
$sejour_hospi->type       = "comp";
$sejour_hospi->_NDA       = null;
$sejour_hospi->service_id = $service_id;
$sejour_hospi->sortie_prevue = CMbDT::dateTime("+1 day", $sejour_hospi->sortie_prevue);

$msg = $sejour_hospi->store();

CAppUI::setMsg($msg ? : CAppUI::tr("CSejour-msg-create"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

// Annulation des séances suivantes
foreach ($seances as $_seance) {
  if ($_seance->_id === $sejour->_id) {
    continue;
  }

  if ($_seance->entree >= $sejour->entree) {
    $_seance->annule = 1;
  }

  $msg = $_seance->store();

  CAppUI::setMsg($msg ? : CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();