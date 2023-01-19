<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Files\CDocumentItem;

CCanDo::checkRead();

$docItem_guid = CView::get("docItem_guid", "guid class|CDocumentItem");
$receivers    = CView::get("receivers"   , "str");
CView::checkin();

/** @var CDocumentItem $docItem */
$docItem = CMbObject::loadFromGuid($docItem_guid);

