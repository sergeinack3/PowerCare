<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;

CCanDo::checkAdmin();

$delete_errors = "TRUNCATE ref_errors";
$delete_fields = "TRUNCATE ref_check_field";
$delete_tables = "TRUNCATE ref_check_table";

$ds = CSQLDataSource::get('std');

$ds->exec($delete_errors);
$ds->exec($delete_fields);
$ds->exec($delete_tables);

CAppUI::stepAjax('dPdeveloppement-ref_check-msg-Reset ok');

CApp::rip();