<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Cli\Console\CZipCodeImport;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

CCanDo::checkAdmin();

$country    = CView::post("country", 'enum list|' . implode('|', array_keys(CZipCodeImport::$countries)). ' notNull');

CView::checkin();

$source_path = "modules/dPpatients/INSEE/zipcode_{$country}.tar.gz";
$target_dir  = "tmp/insee";
$target_path = "tmp/insee/zipcode_{$country}.sql";

// Extract the SQL dump
if (null == ($nb_files = CMbPath::extract($source_path, $target_dir))) {
  CAppUI::stepAjax("Erreur, impossible d'extraire l'archive", UI_MSG_ERROR);
}

CAppUI::stepAjax("Extraction de $nb_files fichier(s)", UI_MSG_OK);

$ds = CSQLDataSource::get("INSEE");
if (null == ($line_count = $ds->queryDump($target_path, false))) {
  $msg = $ds->error();
  CAppUI::stepAjax("Erreur de requête SQL: $msg", UI_MSG_ERROR);
}

CAppUI::stepAjax("Import effectué avec succès de $line_count requêtes", UI_MSG_OK);