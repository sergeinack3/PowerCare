<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$author_id = CView::get('author_id', 'ref class|CMediusers notNull');

CView::checkin();

$cache = new Cache('ExportDossierComplet', $author_id, Cache::INNER_DISTR);
$list_files = $cache->get() ?: [];

$smarty = new CSmartyDP();
$smarty->assign('list_files', $list_files);
$smarty->assign('count', count($list_files));
$smarty->display('vw_purge_dossiers_export_status');