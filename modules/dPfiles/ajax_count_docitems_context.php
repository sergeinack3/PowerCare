<?php 
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;

CCanDo::checkEdit();

$context_class = CView::get("context_class", "str");
$context_id    = CView::get("context_id", "ref class|$context_class");
$type          = CView::get("type", "str");

CView::checkin();

$context = CMbObject::loadFromGuid("$context_class-$context_id");

echo $context->countContextDocItems($type);