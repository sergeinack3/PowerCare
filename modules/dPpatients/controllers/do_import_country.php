<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;

CCanDo::checkAdmin();

$source_path = "modules/dPpatients/INSEE/countries.tar.gz";
$target_dir  = "tmp/insee";
$target_path = "tmp/insee/countries.sql";

// Extract the SQL dump
if (null == ($nb_files = CMbPath::extract($source_path, $target_dir))) {
  CAppUI::stepAjax("Erreur, impossible d'extraire l'archive", UI_MSG_ERROR);
}

CAppUI::stepAjax("Extraction de $nb_files fichier(s)", UI_MSG_OK);

$ds = CSQLDataSource::get("INSEE");
if (null == ($line_count = $ds->queryDump($target_path, true))) {
  $msg = $ds->error();
  CAppUI::stepAjax("Erreur de requête SQL: $msg", UI_MSG_ERROR);
}

CAppUI::stepAjax("Import effectué avec succès de $line_count requêtes", UI_MSG_OK);