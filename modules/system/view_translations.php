<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CTranslationOverwrite;

/**
 * Overwrite translation for the instance
 */
CCanDo::checkEdit();

CView::checkin();

//smarty
$smarty = new CSmartyDP();
$smarty->assign("available_languages", CAppUI::getAvailableLanguages());
$smarty->display("view_translations.tpl");