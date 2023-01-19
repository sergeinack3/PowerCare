<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CAppUI;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$group = CGroups::loadCurrent();

CView::checkin();

$etab_ext = new CEtabExterne();
$ds  = CSQLDataSource::get('std');

$filename = "export-etab-externes";

$csv = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);

$line = [
  CAppUI::tr("CEtabExterne-finess"),
  CAppUI::tr("CEtabExterne-siret"),
  CAppUI::tr("CEtabExterne-ape"),
  CAppUI::tr("CEtabExterne-nom"),
  CAppUI::tr("CEtabExterne-raison_sociale"),
  CAppUI::tr("CEtabExterne-adresse"),
  CAppUI::tr("CEtabExterne-cp"),
  CAppUI::tr("CEtabExterne-ville"),
  CAppUI::tr("CEtabExterne-tel"),
  CAppUI::tr("CEtabExterne-fax"),
  CAppUI::tr("CEtabExterne-provenance"),
  CAppUI::tr("CEtabExterne-destination"),
  CAppUI::tr("CEtabExterne-priority"),

];
$csv->writeLine($line);
$csv->setColumnNames($line);

$etablissements = $etab_ext->loadList();
foreach ($etablissements as $_etab) {
  $line = [
    $_etab->finess,
    $_etab->siret,
    $_etab->ape,
    $_etab->nom,
    $_etab->raison_sociale,
    $_etab->adresse,
    $_etab->cp,
    $_etab->ville,
    $_etab->tel,
    $_etab->fax,
    $_etab->provenance,
    $_etab->destination,
    $_etab->priority
  ];
  $csv->writeLine($line);
}
$csv->stream($filename, true);
CApp::rip();