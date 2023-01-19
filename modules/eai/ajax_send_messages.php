<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hprimxml\CEchangeHprim;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Send message
 */
CCanDo::checkRead();

$exchange_classes = CView::get("exchange_classes", 'str');
$receiver_id      = CView::get("receiver_id", 'num');
$group_id         = CView::get("group_id", "ref class|CGroups");
$count            = CView::get("count", 'num default|20');
$date_min         = CView::get('date_min', ['dateTime', 'default' => CMbDT::dateTime("-1 day")]);
$date_max         = CView::get('date_max', ['dateTime', 'default' => CMbDT::dateTime("+1 day")]);

CView::checkin();

$where = [];
if (!$limit = CAppUI::conf('eai max_files_to_process')) {
    return;
}

$exchange_classes = explode("|", $exchange_classes);

foreach ($exchange_classes as $_exchange_class) {
    // Récupération des receivers pour la classe correspondante
    $receiver = null;
    switch ($_exchange_class) {
        case CClassMap::getSN(CEchangeHprim::class):
            $receiver =  (new CInteropActorFactory())->receiver()->makeHprimXML();;
            break;

        case CClassMap::getSN(CExchangeHL7v2::class):
            $receiver =  (new CInteropActorFactory())->receiver()->makeHL7v2();
            break;

        default:
    }

    if (!$receiver || !$receiver->_ref_module) {
        continue;
    }

    // Récupération des receivers pour l'établissement et qui sont actifs
    $receiver->actif = 1;
    $receiver->role  = CAppUI::conf("instance_role");
    if ($group_id) {
        $receiver->group_id = $group_id;
    }
    $receivers       = $receiver->loadMatchingList();

    // Récupère que les receivers dont la source est joignable, sauf pour Doctolib
    $receivers_ok = [];
    /** @var CInteropReceiver $_receiver */
    foreach ($receivers as $_receiver) {
        /** @var CExchangeSource $_source */
        foreach ($_receiver->loadRefsExchangesSources() as $_source) {
            // On récupère que les receivers qui ont une source d'active
            if ($_source->_id && $_source->active) {
                // Et dont la source est joignable
                try {
                    if (CModule::getActive('doctolib') && ($receiver->type === CInteropActor::ACTOR_DOCTOLIB)) {
                        $receivers_ok[] = $_receiver;
                        continue 2;
                    }
                    $_source->getClient()->isReachableSource();
                    $receivers_ok[] = $_receiver;
                    continue 2;
                } catch (CMbException $e) {
                    CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
                }
            }
        }
    }
    if (count($receivers_ok) === 0) {
        continue;
    }

    // On divise le nombre de messages a envoyer en fonction du nombre de destinataires
    $limit_receiver = $limit / count($receivers_ok);
    $limit_receiver = (int)$limit_receiver;

    // Pour chaque receiver on charge les messages et on les envoie
    /** @var CInteropReceiver $_receiver */
    foreach ($receivers_ok as $_receiver) {
        /** @var CExchangeDataFormat $exchange */
        $exchange = new $_exchange_class();

        $where                            = [];
        $where['sender_id']               = "IS NULL";
        $where['receiver_id']             = "= '$_receiver->_id'";
        $where["date_production"]         = "BETWEEN '$date_min' AND '$date_max'";
        $where['message_valide']          = "= '1'";
        $where['acquittement_content_id'] = "IS NULL";
        $where['send_datetime']           = "IS NULL";
        $where['statut_acquittement']     = "IS NULL";
        $where[]                          = "master_idex_missing = '0' OR master_idex_missing IS NULL";
        $where[]                          = "acquittement_valide IS NULL OR acquittement_valide != '1'";

        $forceindex = [
            "date_production"
        ];

        $order         = $exchange->_spec->key . " ASC";
        $notifications = $exchange->loadList($where, $order, $limit_receiver, null, null, $forceindex);

        // Envoi des messages
        foreach ($notifications as $notification) {
            try {
                $notification->send();
            } catch (CMbException $e) {
                $e->stepAjax(UI_MSG_WARNING);

                $notification->send_datetime = "";
                $notification->store();

                // On passe au receiver suivant pour éviter d'envoyer des messages en discordance
                continue 2;
            }

            CAppUI::stepAjax(
                "CExchangeDataFormat-confirm-exchange sent",
                UI_MSG_OK,
                CAppUI::tr("$notification->_class")
            );
        }
    }
}
