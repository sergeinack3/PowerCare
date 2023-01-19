<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\ViewSender\CViewSender;
use Ox\Mediboard\System\ViewSender\CViewSenderSource;

CCanDo::checkEdit();

$sender_source_id = CView::get("sender_source_id", "ref class|CViewSenderSource");

CView::checkin();

$sender_source = new CViewSenderSource();
$sender_source->load($sender_source_id);
$sender_source->loadRefsNotes();
$sender_source->loadRefSource();

$windows = strpos(PHP_OS, "WIN") !== false;

$zip_exist = CViewSender::isZipEnabled();

$smarty = new CSmartyDP();
$smarty->assign("sender_source", $sender_source);
$smarty->assign("zip_exist", $zip_exist);
$smarty->display("inc_form_view_sender_source.tpl");
