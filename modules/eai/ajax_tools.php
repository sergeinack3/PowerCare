<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hprimxml\CEchangeHprim;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * View tools EAI
 */
CCanDo::checkAdmin();

$count          = CView::get("count", "num default|20");
$continue       = CView::get("continue", "bool");
$error_code     = CView::get("error_code", "str");
$exchange_class = CView::get("exchange_class", "str");
$group_id       = CView::get("group_id", "ref class|CGroups default|" . CGroups::loadCurrent()->_id);
$tool           = CView::get("tool", "str");
$date_min       = CView::get("date_min", ["dateTime", "default" => CMbDT::dateTime('-7 day')], true);
$date_max       = CView::get("date_max", ["dateTime", "default" => CMbDT::dateTime('+1 day')], true);
CView::checkin();

$exchange            = new $exchange_class();
$exchange->group_id  = $group_id;
$exchange->_date_min = $date_min;
$exchange->_date_max = $date_max;

$ljoin = null;
$where = [];
if ($error_code && $exchange instanceof CEchangeHprim) {
    $where["error_codes"] = " REGEXP '($error_code)'";
} elseif ($error_code) {
    $content_exchange        = $exchange->loadFwdRef("acquittement_content_id");
    $table                   = $content_exchange->_spec->table;
    $ljoin[$table]           = $exchange->_spec->table . ".acquittement_content_id = $table.content_id";
    $where["$table.content"] = " LIKE '%$error_code%'";
}

$where["date_production"] = "BETWEEN '$date_min' AND '$date_max'";
$where["group_id"]        = "= '$group_id'";
$where["reprocess"]       = "< '" . CAppUI::conf("eai max_reprocess_retries") . "'";

$forceindex = [
    "date_production"
];

$total_exchanges = $exchange->countList($where, null, $ljoin, $forceindex);
if ($total_exchanges == 0) {
    CAppUI::stepAjax("CEAI-tools-exchanges-no_corresponding_exchange", UI_MSG_ERROR);
}

CAppUI::stepAjax("CEAI-tools-exchanges-corresponding_exchanges", UI_MSG_OK, $total_exchanges);

$order     = "send_datetime ASC, date_production ASC";
$exchanges = $exchange->loadList($where, $order, "0, $count", null, $ljoin, $forceindex);

// Création du template
$smarty = new CSmartyDP();

switch ($tool) {
    case "reprocessing":
        foreach ($exchanges as $_exchange) {
            try {
                $_exchange->reprocessing();
            } catch (CMbException $e) {
                $e->stepAjax(UI_MSG_WARNING);
            }

            if (!$_exchange->_id) {
                CAppUI::stepAjax("CExchangeAny-msg-delete", UI_MSG_ALERT);
            }

            CAppUI::stepAjax("CExchangeDataFormat-reprocessed");
        }

        break;

    case "detect_collision":
        $collisions = [];

        foreach ($exchanges as $_exchange) {
            if ($_exchange instanceof CExchangeHL7v2) {
                $hl7_message = new CHL7v2Message;
                $hl7_message->parse($_exchange->_message);

                $xml = $hl7_message->toXML(null, false);

                $PV1 = $xml->queryNode("PV1");
                $PV2 = $xml->queryNode("PV2");

                $sejour = new CSejour();
                $sejour->load($_exchange->object_id);

                $sejour_hl7                = new CSejour;
                $sejour_hl7->entree_prevue = $xml->queryTextNode("PV2.8/TS.1", $PV2);
                $sejour_hl7->entree_reelle = $xml->queryTextNode("PV1.44/TS.1", $PV1);
                $sejour_hl7->sortie_prevue = $xml->queryTextNode("PV2.9/TS.1", $PV2);
                $sejour_hl7->sortie_reelle = $xml->queryTextNode("PV1.45/TS.1", $PV1);

                $collisions[] = [
                    "hl7" => $sejour_hl7,
                    "mb"  => $sejour,
                ];
            }
        }

        $smarty->assign("collisions", $collisions);
        $smarty->display("inc_detect_collisions.tpl");

        break;

    default:
}

CAppUI::js("next$tool()");

