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
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;

CCanDo::checkAdmin();

CApp::setTimeLimit(360);

$source = 'modules/lpp/base/lpp_chapters.tar.gz';
$target = 'tmp/lpp';

if (null === $files_number = CMbPath::extract($source, $target)) {
  CAppUI::stepAjax('Erreur, impossible d\'extraite l\'archive', UI_MSG_ERROR);
}

CAppUI::stepAjax('Extraction du fichier SQL', UI_MSG_OK);

$ds = CSQLDataSource::get('lpp');

$result = $ds->queryDump('tmp/lpp/lpp_chapters.sql');

if ($result === null) {
  $msg = $ds->error();
  CAppUI::stepAjax("Import des dchapitres de la LPP - erreur de requête SQL: $msg", UI_MSG_ERROR);
}

CAppUI::stepAjax('Import des chapitres de la LPP effectué avec succès', UI_MSG_OK);