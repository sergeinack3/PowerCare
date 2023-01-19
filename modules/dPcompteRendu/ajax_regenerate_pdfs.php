<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;

CCanDo::checkEdit();

$limit = CView::get('limit', 'num default|100');

CView::checkin();

CCompteRendu::regenerateSomePDF($limit);