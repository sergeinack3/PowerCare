<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CDailySalleMiner;
use Ox\Mediboard\System\CDataMinerWorker;

CCanDo::checkEdit();

CView::checkin();

CDataMinerWorker::mine(CDailySalleMiner::class);