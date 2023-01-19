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
use Ox\Mediboard\System\CExchangeSource;

CCanDo::check();

$type                 = CView::get('type', 'str');
$exchange_source_name = CView::get('exchange_source_name', 'str');
$dont_close_modal     = CView::get('dont_close_modal', 'bool default|0');

CView::checkin();

if ($type) {
    $type = explode('|', $type);
}

$exchange_source = CExchangeSource::get($exchange_source_name, $type, true, null, false);
if ($exchange_source->_class == 'CSourcePOP') {
    $exchange_source->loadRefMetaObject();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("source", $exchange_source);
$smarty->assign('dont_close_modal', $dont_close_modal);
$smarty->display("inc_config_exchange_source.tpl");
