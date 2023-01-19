<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CDoObjectAddEdit;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$do = new CDoObjectAddEdit("CSejour");
$do->doBind();

/** @var CSejour $sejour */
$sejour = $do->_obj;
$sejour->annule = 0;

// Ne pas créer d'affectation
$sejour->_create_affectations = false;

// Ne pas synchroniser les affectations
$sejour->_no_synchro = true;

// Retrait de la sortie réelle
if ($sejour->sortie_reelle) {
  $sejour->sortie_reelle = "";

  $msg = $sejour->store();

  CAppUI::setMsg($msg ? : "CSejour-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

  if ($msg) {
    $do->doRedirect();
    return;
  }
}

// Retrait de l'entrée réelle
if ($sejour->entree_reelle) {
  $sejour->entree_reelle = "";

  $msg = $sejour->store();

  CAppUI::setMsg($msg ? : "CSejour-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

  if ($msg) {
    $do->doRedirect();
    return;
  }
}

// Annulation du séjour
$sejour->clearBackRefCache("affectations");
$sejour->annule = 1;

$msg = $sejour->store();

CAppUI::setMsg($msg ? : "CSejour-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

$do->doRedirect();