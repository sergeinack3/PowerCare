<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();

if (CFile::shrinkPDF(CAppUI::conf("root_dir") . "/modules/printing/samples/test_page.pdf")) {
  CAppUI::stepMessage(UI_MSG_OK, "Le fichier a été shrinké");
}
else {
  CAppUI::stepMessage(UI_MSG_ERROR, "Le fichier n'a pas été shrinké");
}