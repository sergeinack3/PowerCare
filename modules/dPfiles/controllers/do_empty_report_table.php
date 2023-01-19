<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;

CCanDo::checkAdmin();

$ds = CSQLDataSource::get('std');

$query = "TRUNCATE TABLE file_report";

if ($ds->exec($query)) {
  CAppUI::stepAjax('Table de reporting des fichiers vidée');
}
else {
  CAppUI::stepAjax('Erreur lors du vidage de la table', UI_MSG_WARNING);
}

CApp::rip();