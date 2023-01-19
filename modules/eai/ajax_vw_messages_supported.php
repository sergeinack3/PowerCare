<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Eai\CMessageSupported;

/**
 * Messages supported
 */
CCanDo::checkRead();

$actor_guid     = CView::get("actor_guid", "guid class|CInteropActor");
$exchange_class = CView::get("exchange_class", 'str');
CView::checkin();

/** @var CExchangeDataFormat $data_format */
$data_format  = new $exchange_class();
$messages     = $data_format->getMessagesSupported($actor_guid);
$all_messages = [];
foreach ($messages as $_family => $_messages_supported) {
    /** @var CInteropNorm $family */
    $family = new $_family();
    $events = $family->getEvenements();

    $categories = [];
    if (isset($family->_categories) && !empty($family->_categories)) {
        foreach ($family->_categories as $_category => $events_name) {
            foreach ($events_name as $_event_name) {
                /** @var CMessageSupported $_message_supported */
                foreach ($_messages_supported as $_message_supported) {
                    if (!$family->isMessageSupported($_message_supported, $_event_name, $_category)) {
                        continue;
                    }

                    $_category = $_message_supported->transaction ?: $_category;

                    $categories[$_category][] = $_message_supported;
                }
            }
        }
    } else {
        $categories["none"] = $_messages_supported;
    }

    // On reformate un peu le tableau des catégories
    $family->_categories        = $categories;
    $family->_versions_category = $family->getCategoryVersions();

    $domain = $family->domain ? $family->domain : $family->name;

    $all_messages[$domain][] = $family;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("data_format", $data_format);
$smarty->assign("messages", $messages);
$smarty->assign("all_messages", $all_messages);
$smarty->assign("actor_guid", $actor_guid);
$smarty->display("inc_messages_supported.tpl");
