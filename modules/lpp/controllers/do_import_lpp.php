<?php
/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\Database\CDBF;

CCanDo::checkAdmin();

if (CValue::post("get_version")) {
  $page          = "http://www.codage.ext.cnamts.fr/codif/tips/telecharge/index_tele.php";
  $page_contents = file_get_contents($page);
  $version       = null;
  $date          = null;

  if (preg_match("/LPP(\d{3,})\.zip/", $page_contents, $matches)) {
    $version = $matches[1];
  }

  if (preg_match("/Version du : (\d{2}\/\d{2}\/\d{4})/", $page_contents, $matches)) {
    $date = $matches[1];
  }

  CAppUI::stepAjax("Date: $date, version: $version");
  CApp::rip();
}


CApp::setTimeLimit(360);

$distPath   = "http://www.codage.ext.cnamts.fr/f_mediam/fo/tips/LPP.zip";
$targetDir  = "tmp/lpp";
$sourcePath = "$targetDir/LPP.zip";

CMbPath::forceDir($targetDir);

// Download the archive
file_put_contents($sourcePath, file_get_contents($distPath));

// Extract the data files
if (null == $nbFiles = CMbPath::extract($sourcePath, $targetDir)) {
  CAppUI::stepAjax("Erreur, impossible d'extraire l'archive", UI_MSG_ERROR);
}

CAppUI::stepAjax("Extraction de $nbFiles fichier(s)", UI_MSG_OK);

$ds = CSQLDataSource::get("lpp");

$tables = array(
  "fiche"  => "lpp_fiche_tot*.dbf",
  "comp"   => "lpp_comp_tot*.dbf",
  "incomp" => "lpp_incomp_tot*.dbf",
  "histo"  => "lpp_histo_tot*.dbf",
);

$db_types = array(
  "C" => "VARCHAR",
  "D" => "DATE",
  "N" => "NUMBER",
);

foreach ($tables as $table => $filename) {
  $files     = glob("$targetDir/$filename");
  $file      = reset($files);
  $dbf       = new CDBF($file);
  $num_rec   = $dbf->dbf_num_rec;
  $field_num = $dbf->dbf_num_field;

  $query = "DROP TABLE IF EXISTS $table";
  $ds->exec($query);

  CAppUI::stepAjax("Table <strong>$table</strong> supprimée", UI_MSG_OK);

  // Table creation
  $query = "CREATE TABLE $table (";

  $cols = array();
  foreach ($dbf->dbf_names as $i => $col) {
    switch ($col['type']) {
      case "C":
        $cols[] = "{$col['name']} VARCHAR({$col['len']})";
        break;
      case "D":
        $cols[] = "{$col['name']} DATE";
        break;
      case "N":
        $cols[] = "{$col['name']} FLOAT";
        break;
      default:
    }
  }

  $query .= implode(", ", $cols);
  $query .= ")/*! ENGINE=MyISAM */";

  $ds->exec($query);

  CAppUI::stepAjax("Table <strong>$table</strong> re-créee", UI_MSG_OK);

  // Table insertion
  $query_start = "INSERT INTO $table (";
  $query_start .= implode(", ", CMbArray::pluck($dbf->dbf_names, "name"));
  $query_start .= ") VALUES";

  for ($i = 0; $i < $num_rec; $i++) {
    $query  = $query_start;
    $values = array();

    if ($row = $dbf->getRow($i)) {
      foreach ($dbf->dbf_names as $j => $col) {
        switch ($col['type']) {
          case "C":
            $values[] = '"' . addslashes($row[$j]) . '"';
            break;

          case "N":
            $values[] = (($row[$j] === "") ? "NULL" : $row[$j]);
            break;

          case "D":
            $date = "NULL";
            if (preg_match("/(\d{4})(\d{2})(\d{2})/", $row[$j], $parts)) {
              $date = "\"$parts[1]-$parts[2]-$parts[3]\"";
            }
            $values[] = $date;
            break;
          default:
        }
      }
    }

    $query .= '(' . implode(", ", $values) . ')';
    $ds->exec($query);
  }

  CAppUI::stepAjax("$num_rec enregistrements ajoutés à la table <strong>$table</strong>", UI_MSG_OK);
}
