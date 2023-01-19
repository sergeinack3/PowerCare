<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

/**
 * View exchanges
 */
CCanDo::checkRead();

$exchanges_data_format_classes = array();
$exchanges_transport_layer_classes = array();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchanges_data_format_classes", $exchanges_data_format_classes);
$smarty->assign("exchanges_transport_layer_classes", $exchanges_transport_layer_classes);
$smarty->display("vw_idx_exchanges.tpl");