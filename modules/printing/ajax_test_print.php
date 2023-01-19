<?php
/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Printing\CSourcePrinter;

/**
 * Test print
 */
CCanDo::checkEdit();

$id    = CView::get("id", "num");
$class = CView::get("class", "enum list|CSourceLPR|CSourceSMB");

CView::checkin();

/** @var CSourcePrinter $source */
$source = new $class();
$source->load($id);

$file             = new CFile();
$file->_file_path = "modules/printing/samples/test_page.pdf";

$source->sendDocument($file);
