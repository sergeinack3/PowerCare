<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultationCategorie;

CCanDo::checkRead();

$patient_id   = CView::getRefCheckRead('patient_id', 'ref class|CPatient');
$categorie_id = CView::get('cat_id', 'ref class|CConsultationCategorie');

CView::checkin();

$categorie = new CConsultationCategorie();
$categorie->load($categorie_id);

$cerfa_entente_prealable = 0;

if ($categorie->seance) {
  $cerfa_entente_prealable = $categorie->isCerfaEntentePrealable($patient_id);
}

$data['nb_consult']              = $categorie->countRefConsultations($patient_id);
$data['cerfa_entente_prealable'] = $cerfa_entente_prealable;
$data['isCabinet'] = CAppUI::isCabinet() || CAppUI::isGroup();

CApp::json($data);

