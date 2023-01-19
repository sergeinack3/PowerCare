<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFileIntegrity;

CCanDo::checkAdmin();

$step          = CView::get('step', 'num default|100 max|1000');
$limit         = CView::get('limit', 'num default|100 max|10000');
$date_start    = CView::get('date_start', 'str');
$date_end      = CView::get('date_end', 'str');
$fs_date_start = CView::get('fs_date_start', 'str');
$fs_date_end   = CView::get('fs_date_end', 'str');

CView::checkin();

// Don't put on slave because of rawStore
//CView::enforceSlave();

CApp::setTimeLimit(120);
CSQLDataSource::$log           = false;
CStoredObject::$useObjectCache = false;

$file_integrity = new CFileIntegrity($limit);
$file_integrity->check($step, $date_start, $date_end, $fs_date_start, $fs_date_end);
