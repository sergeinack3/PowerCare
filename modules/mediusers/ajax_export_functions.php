<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkAdmin();

CView::enforceSlave();

$group = CGroups::loadCurrent();

if (!$group->getPerm(PERM_READ)) {
  CAppUI::stepAjax('access-forbidden', UI_MSG_ERROR);
}

$header = array(
  'intitule', 'sous-titre', 'type', 'couleur', 'initiales', 'adresse', 'cp', 'ville', 'tel', 'fax', 'mail', 'siret', 'quotas', 'actif',
  'compta_partage', 'consult_partage', 'adm_auto', 'facturable', 'creation_sejours', 'ufs', 'ufs_secondaires'
);

$date      = CMbDT::date();
$file_name = "export-functions-{$group->text}-{$date}";

$filepath = rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp/$file_name";

$fp  = fopen($filepath, 'w+');
$csv = new CCSVFile($fp);
$csv->setColumnNames($header);
$csv->writeLine($header);

$function           = new CFunctions();
$function->group_id = $group->_id;

$functions = $function->loadMatchingListEsc();

/** @var CFunctions $_func */
foreach ($functions as $_func) {
  $ufs_codes = array();
  $ufs       = $_func->loadBackRefs("ufs");
  /** @var CAffectationUniteFonctionnelle $_link */
  foreach ($ufs as $_link) {
    $uf = $_link->loadRefUniteFonctionnelle();
    if ($uf->type == 'medicale') {
      $ufs_codes[] = $uf->code;
    }
  }

  $ufs_secondaires_codes = array();
  $ufs_sec              = $_func->loadBackRefs("ufs_secondaires");

  /** @var CAffectationUniteFonctionnelle $_link */
  foreach ($ufs_sec as $_link) {
    $uf = $_link->loadRefUniteFonctionnelle();
    if ($uf->type == 'medicale') {
      $ufs_secondaires_codes[] = $uf->code;
    }
  }

  $line = array(
    'intitule'         => $_func->text,
    'sous-titre'       => $_func->soustitre,
    'type'             => $_func->type,
    'couleur'          => $_func->color,
    'initiales'        => $_func->initials,
    'adresse'          => $_func->adresse,
    'cp'               => $_func->cp,
    'ville'            => $_func->ville,
    'tel'              => $_func->tel,
    'fax'              => $_func->fax,
    'mail'             => $_func->email,
    'siret'            => $_func->siret,
    'quotas'           => $_func->quotas,
    'actif'            => $_func->actif,
    'compta_partage'   => $_func->compta_partagee,
    'consult_partage'  => $_func->consults_events_partagees,
    'adm_auto'         => $_func->admission_auto,
    'facturable'       => $_func->facturable,
    'creation_sejours' => $_func->create_sejour_consult,
    'ufs'              => implode('|', $ufs_codes),
    'ufs_secondaires'  => implode('|', $ufs_secondaires_codes),
  );

  $csv->writeLine($line);
}

$csv->stream($file_name, true);

$csv->close();
unlink($filepath);
