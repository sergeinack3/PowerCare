<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$service_id = CView::get('service_id', 'ref class|CService notNull');
$date       = CView::get('date', ['date', 'default' => CMbDT::date('-1 DAY')]);

CView::checkin();

$tag_ipp = CPatient::getTagIPP();
$tag_nda = CSejour::getTagNDA();

$group = CGroups::loadCurrent();

$ds = CSQLDataSource::get('std');

$query = new CRequest();
$query->addSelect(
  [
    'p.nom as pnom',
    'p.prenom as pprenom',
    'p.naissance as naissance',
    'ipp.id400 as ipp',
    'nda.id400 as nda',
    'se.type as type',
    'sa.nom as salle',
    'CONCAT(ch.user_last_name, " ", ch.user_first_name) as chir',
    'CONCAT(ap.user_last_name, " ", ap.user_first_name) as anesth_salle',
    'CONCAT(ao.user_last_name, " ", ao.user_first_name) as anesth_op',
    'o.date as date',
    'o.libelle as libelle',
    'o.entree_bloc as ebloc',
    'o.remise_chir as remise_chir',
    'o.entree_salle as esalle',
    'o.installation_start as installation_start',
    'o.installation_end as installation_end',
    'o.induction_debut as induction_debut',
    'o.incision as incision',
    'o.debut_op as debut_op',
    'o.suture_fin as suture',
    'o.fin_op as fin_op',
    'o.induction_fin as induction_fin',
    'o.sortie_salle as sortie_salle',
    'o.cleaning_start as cleaning_start',
    'o.cleaning_end as cleaning_end',
    'o.entree_reveil as ereveil',
    'o.sortie_reveil_reel as sreveil',
    'o.anapath as ana',
    'o.labo_anapath as lana',
    'o.labo as labo',
    'o.validation_timing as valid',
    'o.rques as rques',
    'af.entree as euscpo',
    'af.sortie as suscpo'
  ]
);
$query->addTable('operations as o');

$query->addLJoin(
  [
    'sejour se ON se.sejour_id = o.sejour_id',
    'patients p ON p.patient_id = se.patient_id',
    'sallesbloc sa ON sa.salle_id = o.salle_id',
    'users ch ON ch.user_id = o.chir_id',
    'users ao ON ao.user_id = o.anesth_id',
    'plagesop ON plagesop.plageop_id = o.plageop_id',
    'users ap ON ap.user_id = plagesop.anesth_id',
    "id_sante400 ipp ON (ipp.object_id = p.patient_id AND ipp.object_class = 'CPatient' AND ipp.tag = '$tag_ipp')",
    "id_sante400 nda ON (nda.object_id = se.sejour_id AND nda.object_class = 'CSejour' AND nda.tag = '$tag_nda')",
    "affectation af ON (af.sejour_id = se.sejour_id AND af.service_id = {$service_id})"
  ]
);

$query->addWhere(
  [
    "o.date = '$date'",
    "se.group_id = $group->_id"
  ]
);

$query->addGroup('o.operation_id');

$sql = $query->makeSelect();
$rows = $ds->loadList($query->makeSelect());

$file = new CCSVFile();
$file->writeLine(
  [
    'Nom patient',
    'Prénom patient',
    'Date de naissance',
    'IPP',
    'NDA',
    'Type d\'hospitalisation',
    'Salle',
    'Chirurgien',
    'Anesthésiste (salle)',
    'Anesthésiste (opération)',
    'Date intervention',
    'Libellé',
    'Entrée bloc',
    'Remise au chirurgien',
    'Entrée salle',
    'Début installation',
    'Fin installation',
    'Début anesthésie',
    'Début intervention',
    'Incision',
    'Fin suture',
    'Fin intervention',
    'Sortie salle',
    'Début nettoyage',
    'Fin nettoyage',
    'Entrée salle de réveil',
    'Sortie salle de réveil',
    'Anapath',
    'Nom laboratoire anapath',
    'Laboratoire',
    'Timing validé',
    'Remarques',
    'Entrée USCPO',
    'Sortie USCPO'
  ]
);

foreach ($rows as $row) {
  $file->writeLine(
    [
      $row['pnom'],
      $row['pprenom'],
      $row['naissance'],
      $row['ipp'],
      $row['nda'],
      $row['type'],
      $row['salle'],
      $row['chir'],
      $row['anesth_salle'],
      $row['anesth_op'],
      $row['date'],
      $row['libelle'],
      $row['ebloc'],
      $row['remise_chir'],
      $row['esalle'],
      $row['installation_start'],
      $row['installation_end'],
      $row['induction_debut'],
      $row['debut_op'],
      $row['incision'],
      $row['fin_op'],
      $row['sortie_salle'],
      $row['cleaning_start'],
      $row['cleaning_end'],
      $row['suture'],
      $row['ereveil'],
      $row['sreveil'],
      $row['ana'],
      $row['lana'],
      $row['labo'],
      $row['valid'],
      $row['rques'],
      $row['euscpo'],
      $row['suscpo']
    ]
  );
}

$file->stream('Export_temps_operatoire_' . CMbDT::format($date, '%d-%m-%Y'));
CApp::rip();