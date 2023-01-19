<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Mediboard\Files\CFile;

/**
 * OpenOffice status
 */
if (CFile::openofficeLaunched()) {
  CAppUI::stepAjax(CAppUI::tr("config-dPfiles-CFile.ooo_launched"), UI_MSG_OK);
}
else {
  CAppUI::stepAjax(CAppUI::tr("config-dPfiles-CFile.ooo_stopped"), UI_MSG_WARNING);
}
