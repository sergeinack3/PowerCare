<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CCorrespondantCourrier;
use Ox\Mediboard\CompteRendu\CDestinataire;
use Ox\Mediboard\Files\MailReceiverService;
use Ox\Mediboard\Patients\CPatient;

/**
 * Modification des correspondants d'un document
 */

$object_guid     = CView::get("object_guid", "str");
$compte_rendu_id = CView::get("compte_rendu_id", "ref class|CCompteRendu");

CView::checkin();

$compte_rendu = new CCompteRendu();
$compte_rendu->load($compte_rendu_id);

$object = CMbObject::loadFromGuid($object_guid);

$compte_rendu->_ref_object = $object;
$compte_rendu->loadRefsCorrespondantsCourrierByTagGuid();

$destinataires = (new MailReceiverService($object))->getReceivers();

if (!isset($destinataires["CMedecin"])) {
    $destinataires["CMedecin"] = [];
}

// Fusion avec les correspondants ajoutés par l'autocomplete
$compte_rendu->mergeCorrespondantsCourrier($destinataires);

$empty_corres = new CCorrespondantCourrier();
$empty_corres->valueDefaults();

$patient = ($compte_rendu->getPatient()) ?: new CPatient();

$smarty = new CSmartyDP();

$smarty->assign("compte_rendu", $compte_rendu);
$smarty->assign("destinataires", $destinataires);
$smarty->assign("empty_corres", $empty_corres);
$smarty->assign("patient", $patient);

$smarty->display("inc_edit_correspondants_courrier");
