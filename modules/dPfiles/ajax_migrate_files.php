<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Files\CFile;

CCanDo::checkAdmin();
$count = CView::get('count', 'num notNull');
$auto  = CView::get('auto', 'bool');
CView::checkin();

if (CAppUI::conf('dPfiles CFile migration_limit') > 0) {
  CAppUI::stepAjax("La migration probabiliste est active, merci de mettre la limite à 0.", UI_MSG_WARNING);
}
else {
  $mutex = new CMbMutex('migrate_file');
  $mutex->acquire(60);
  $migrated_file_count = CFile::migrateFiles($count);
  $mutex->release();
  CAppUI::stepAjax("$migrated_file_count fichiers migrés");

  if ($auto && $migrated_file_count > 0) {
    CAppUI::callbackAjax('migrateFiles');
  }

  CApp::rip();
}
