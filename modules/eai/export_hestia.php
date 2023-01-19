<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

$date_start = CView::get('date_start', 'str');
CView::checkin();

if (!$date_start) {
  $date_start = CMbDT::dateTime();
} else {
  $date_start = CMbDT::dateTime($date_start);
}

$sejour = new CSejour();
$group_id = CGroups::loadCurrent()->_id;

// Séjours futurs
$where = [];
$where['entree'] = " > '$date_start' ";
$where['group_id'] = " = '$group_id' ";
$sejours = $sejour->loadList($where);

// Séjours présents
$where = [];
$where['entree'] = " < '$date_start' ";
$where['sortie'] = " > '$date_start' ";
$where['group_id'] = " = '$group_id' ";
$sejours = array_merge($sejours, $sejour->loadList($where));


ob_end_clean();
header("Content-Type: text/plain;charset=" . CApp::$encoding);
header("Content-Disposition: attachment;filename=\"export_hestia.csv\"");

$fp  = fopen("php://output", "w");
$csv = new CCSVFile($fp);
$csv->delimiter = '|';

$titles = array(
  'NomUsuel',
  'NomNaissance',
  'Prenom',
  'IPP',
  'NDA',
  'DateHeureDebutDossier',
  'NumeroVenue',
  'DateHeureDebutVenue',
  'NumeroMouvement',
  'DateHeureDebutMouvement',
  'CodeUFHebergement',
  'Lit',
  'Genre',
  'DateNaissance'
);
$csv->writeLine($titles);

/** @var CSejour $_sejour */
foreach ($sejours as $_sejour) {
  $patient     = $_sejour->loadRefPatient();
  $affectation = $_sejour->loadRefCurrAffectation();

  if (!$affectation || !$affectation->_id) {
    $affectation = $_sejour->loadRefFirstAffectation();
  }

  $lit = $code_uf_hebergement = '';
  if ($affectation && $affectation->_id) {
    $code_uf_hebergement = $affectation->loadRefService()->code;

    $affectation->loadRefLit();
    $lit = $affectation->_ref_lit->nom;
  }

  $patient->loadIPP();
  $_sejour->loadNDA();

  $data['NomUsuel'] = $patient->nom;
  $data['NomNaissance'] = $patient->nom_jeune_fille;
  $data['Prenom'] = $patient->prenom;
  $data['IPP'] = $patient->_IPP;
  $data['NDA'] = $_sejour->_NDA;
  $data['DateHeureDebutDossier'] = CMbDT::format($_sejour->entree, '%Y%m%d%H%M%S');
  $data['NumeroVenue'] = $_sejour->_id;
  $data['DateHeureDebutVenue'] = CMbDT::format($_sejour->entree, '%Y%m%d%H%M%S');
  $data['NumeroMouvement'] = '1';
  $data['DateHeureDebutMouvement'] = CMbDT::format($_sejour->entree, '%Y%m%d%H%M%S');
  $data['CodeUFHebergement'] = $code_uf_hebergement;
  $data['Lit'] = $lit;
  $data['Genre'] = $patient->sexe;
  $data['DateNaissance'] = $patient->naissance;

  $csv->writeLine($data);
}

CApp::rip();
