<?php

/**
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Interop\Eai\CEchangeXML;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CExchangeSourceAdvanced;

class CExchangeControllerLegacy extends CLegacyController
{
    /** @var array */
    private static $params = [];

    /**
     * @throws Exception
     */
    public function ajax_action_exchange()
    {
        CCanDo::checkRead();
        $action = CView::get("action", "str");
        $quiet  = CView::get("quiet", "bool default|1");

        // load from guids
        if ($exchange_guids = CView::get("exchange_guids", "str")) {
            if (!is_array($exchange_guids)) {
                $exchange_guids = [$exchange_guids];
            }
            $exchanges       = CMbArray::array_flatten(CStoredObject::loadFromGuids($exchange_guids));
            $total_exchanges = count($exchanges);
        } else {
            [$exchanges, $total_exchanges] = $this->loadExchanges(null, $action === "export", $action === 'delete');
            if ($action === "delete") {
                $action = "deleteAll";
            }
        }
        CView::checkin();


        switch ($action) {
            case 'deleteAll':
                $class    = CValue::get("exchange_class");
                $exchange = new $class();
                if ($msg = $exchange->deleteAll($exchanges)) {
                    CAppUI::stepAjax($msg, UI_MSG_WARNING);
                }
                CAppUI::stepAjax("CExchangeAny-msg-delete|pl", UI_MSG_OK, $total_exchanges);
                break;
            case 'delete':
            case 'send':
            case 'reprocess':
                $this->treatExchanges($action, $exchanges, $quiet);
                break;
            case 'export':
                $this->exportInCsv($exchanges);
                break;
            default:
                CAppUI::stepAjax("CExchangeDataFormat-msg-error action not supported", UI_MSG_ERROR, $action);
        }
    }

    /**
     * @param bool $export
     *
     * @return array
     * @throws Exception
     */
    private function loadExchanges(?int $limit = null, bool $export = false, bool $load_ids = false): array
    {
        $id_permanent        = CView::get("id_permanent", "num", true);
        $exchange_class      = CView::get("exchange_class", "str", true);
        $object_id           = CView::get("object_id", "num", true);
        $t                   = CView::get('types', "str", true);
        $statut_acquittement = CView::get("statut_acquittement", "str", true);
        $type                = CView::get("type", "str", true);
        $evenement           = CView::get("evenement", "str", true);
        $page                = CView::get('page', "num", true);
        $group_id            = CView::get("group_id", "ref class|CGroups default|" . CGroups::loadCurrent()->_id, true);
        $_date_min           = CView::get('_date_min', ["dateTime", "default" => CMbDT::dateTime("-7 day")], true);
        $_date_max           = CView::get('_date_max', ["dateTime", "default" => CMbDT::dateTime("+1 day")], true);
        $actor_guid          = CView::get("actor_guid", "guid class|CInteropActor", true);
        $keywords_msg        = CView::get("keywords_msg", "str", true);
        $keywords_ack        = CView::get("keywords_ack", "str", true);
        $order_col           = CView::get("order_col", "str", true);
        $order_way           = CView::get("order_way", "str", true);
        $receiver_id         = CView::get("receiver_id", "num", true);
        $sender_guid         = CView::get("sender_guid", "guid class|CInteropSender", true);
        CView::checkin();
        CView::enforceSlave();
        self::$params = [
            "exchange_class"      => $exchange_class,
            "object_id"           => $object_id,
            "t"                   => $t,
            "statut_acquittement" => $statut_acquittement,
            "type"                => $type,
            "evenement"           => $evenement,
            "page"                => $page,
            "group_id"            => $group_id,
            "_date_min"           => $_date_min,
            "_date_max"           => $_date_max,
            "actor_guid"          => $actor_guid,
            "keywords_msg"        => $keywords_msg,
            "keywords_ack"        => $keywords_ack,
            "order_col"           => $order_col,
            "order_way"           => $order_way,
            "receiver_id"         => $receiver_id,
            "sender_guid"         => $sender_guid,
        ];

        /** @var CExchangeDataFormat $exchange */
        $exchange = new $exchange_class();

        $where = [];
        if (isset($t["emetteur"])) {
            $where["sender_id"] = " IS NULL";
        }
        if (isset($t["destinataire"])) {
            $where["receiver_id"] = " IS NULL";
        }
        if ($_date_min && $_date_max) {
            $where['date_production'] = " BETWEEN '" . $_date_min . "' AND '" . $_date_max . "' ";
        }
        if ($group_id) {
            $where["group_id"] = " = '" . $group_id . "'";
        }
        if ($type) {
            $where["type"] = " = '" . $type . "'";
        }
        if ($evenement && $exchange instanceof CEchangeXML) {
            $where["sous_type"] = " = '" . $evenement . "'";
        }
        if ($evenement && $exchange instanceof CExchangeTabular) {
            $where["code"] = " = '" . $evenement . "'";
        }

        if (isset($t["message_invalide"])) {
            $where["message_valide"] = " = '0'";
        }
        if (isset($t["acquittement_invalide"])) {
            $where["statut_acquittement"] = " = 'AR'";
        }
        if (isset($t["no_date_echange"])) {
            $where["send_datetime"] = "IS NULL";
        }
        if (isset($t["master_idex_missing"])) {
            $where[] = "master_idex_missing = '1'";
        }
        if ($id_permanent) {
            $where["id_permanent"] = " = '$id_permanent'";
        }
        if ($object_id) {
            $where["object_id"] = " = '$object_id'";
        }
        $ljoin = null;
        if ($keywords_msg) {
            $content_exchange = $exchange->loadFwdRef("message_content_id");
            $table            = $content_exchange->_spec->table;
            $ljoin[$table]    = $exchange->_spec->table . ".message_content_id = $table.content_id";

            $where["$table.content"] = " LIKE '%$keywords_msg%'";
        }

        if ($keywords_ack) {
            $content_exchange = $exchange->loadFwdRef("acquittement_content_id");
            $table            = $content_exchange->_spec->table;
            $ljoin[$table]    = $exchange->_spec->table . ".acquittement_content_id = $table.content_id";

            $where["$table.content"] = " LIKE '%$keywords_ack%'";
        }

        if ($sender_guid) {
            [$sender_class, $sender_id] = explode('-', $sender_guid);

            $where["sender_class"] = " = '$sender_class'";
            $where["sender_id"]    = " = '$sender_id'";
        }
        if ($receiver_id) {
            $where["receiver_id"] = " = '$receiver_id'";
        }

        $group_id = $group_id ? $group_id : CGroups::loadCurrent()->_id;

        if ($actor_guid) {
            $actor = CMbObject::loadFromGuid($actor_guid);
            if ($actor instanceof CInteropSender) {
                $where["sender_class"] = " = '$actor->_class'";
                $where["sender_id"]    = " = '$actor->_id'";
            }
            if ($actor instanceof CInteropReceiver) {
                $where["receiver_id"] = " = '$actor->_id'";
            }
        }

        if (!$actor_guid && $sender_guid && !$receiver_id) {
            $where["group_id"]  = " = '$group_id'";
            $exchange->group_id = $group_id;
        }

        $exchange->loadRefGroups();

        $forceindex      = ["date_production"];
        $total_exchanges = $exchange->countList($where, null, $ljoin, $forceindex);
        $max_exchanges   = CAppUI::conf("eai nb_max_export_csv");
        if ($export && $total_exchanges > $max_exchanges) {
            CAppUI::displayAjaxMsg(
                "ExchangeDataFormat-action-Export CSV disabled",
                UI_MSG_WARNING,
                $total_exchanges,
                $max_exchanges
            );
            CApp::rip();
        }
        $order = "$order_col $order_way, {$exchange->_spec->key} DESC";

        if ($limit) {
            $limit = $page ? "$page, 25" : "$limit";
        }

        /** @var CExchangeHL7v2 [] $exchanges */
        if ($load_ids) {
            $exchanges = $exchange->loadIds($where, $order, null, null, $ljoin);
        } else {
            $exchanges = $exchange->loadList($where, $order, $limit, null, $ljoin, $forceindex);
        }

        return [$exchanges, $total_exchanges];
    }

    /**
     * @throws Exception
     */
    public function ajax_refresh_echanges_data_format_list(): void
    {
        [$exchanges, $total_exchanges] = $this->loadExchanges(25);

        foreach ($exchanges as $_exchange) {
            $_exchange->loadRefsBack();
            $_exchange->getObservations();
            $_exchange->loadRefsInteropActor();
            $_exchange->loadRefsNotes();
        }

        $this->renderSmarty(
            'inc_exchanges.tpl',
            [
                "exchange"             => new self::$params["exchange_class"](),
                "exchanges"            => $exchanges,
                "total_exchanges"      => $total_exchanges,
                "page"                 => self::$params["page"],
                "selected_types"       => self::$params["t"],
                "statut_acquittement"  => self::$params["statut_acquittement"],
                "type"                 => self::$params["type"],
                "evenement"            => self::$params["evenement"],
                "keywords_msg"         => self::$params["keywords_msg"],
                "keywords_ack"         => self::$params["keywords_ack"],
                "order_col"            => self::$params["order_col"],
                "order_way"            => self::$params["order_way"],
                "limit_request_export" => CAppUI::conf("eai nb_max_export_csv"),
            ]
        );
    }

    /**
     * @param CExchangeDataFormat[] $exchanges
     *
     * @throws Exception
     */
    private function treatExchanges(string $action, array $exchanges, bool $quiet = false)
    {
        $count_exchanges_ok = 0;
        $exceptions         = [];
        $exchanges_deleted  = [];
        $msg                = '';
        foreach ($exchanges as $exchange) {
            $exchange_guid = $exchange->_guid;
            try {
                switch ($action) {
                    case 'delete':
                        $exchange = $this->ajax_delete_exchange($exchange, $quiet);
                        $msg      = 'CExchangeAny-msg-delete|pl';
                        break;
                    case 'reprocess':
                        $exchange = $this->ajax_reprocessing_exchange($exchange, $quiet);
                        $msg      = 'CExchangeDataFormat-reprocessed|pl';
                        break;
                    case 'send':
                        $exchange = $this->ajax_send_message($exchange, $quiet);
                        $msg      = 'CExchangeDataFormat-confirm-exchange sent|pl';
                        break;
                    default:
                        CAppUI::stepAjax("CExchangeDataFormat-msg-error action not supported", UI_MSG_ERROR, $action);
                }

                if ($exchange->_id || ($action === "delete" && !$exchange->_id)) {
                    $count_exchanges_ok++;
                }
            } catch (CMbException $e) {
                $exceptions[$exchange->_guid] = $e;
            }

            if (!$exchange->_id && $action !== "delete") {
                $exchanges_deleted[] = $exchange_guid;
            }
        }

        if ($quiet) {
            CAppUI::stepAjax($msg, UI_MSG_OK, $count_exchanges_ok);

            if ($exchanges_deleted) {
                CAppUI::stepAjax('CExchangeDataFormat-confirm-exchange sent|pl', UI_MSG_ALERT, $count_exchanges_ok);
            }
            // todo que faire des exceptions ?
        }
    }

    /**
     * @param CExchangeDataFormat|null $exchange
     * @param bool                     $quiet
     *
     * @return CExchangeDataFormat
     * @throws CMbException
     * @throws Exception
     */
    public function ajax_delete_exchange(
        ?CExchangeDataFormat $exchange = null,
        bool $quiet = false
    ): CExchangeDataFormat {
        if (!$exchange && ($exchange_guid = CView::get("exchange_guid", "str"))) {
            CView::checkin();
            $exchange = CStoredObject::loadFromGuid($exchange_guid);
        }

        if ($msg = $exchange->delete()) {
            if (!$quiet) {
                CAppUI::stepAjax($msg, UI_MSG_ERROR);
            }

            if ($exchange_guid ?? false) {
                throw new Exception($msg);
            }
        }

        if (!$exchange->_id && !$quiet) {
            CAppUI::stepAjax("CExchangeAny-msg-delete", UI_MSG_OK);
        }

        return $exchange;
    }

    /**
     * @param CExchangeDataFormat|null $exchange
     * @param bool                     $quiet
     *
     * @return CExchangeDataFormat
     * @throws CMbException
     */
    public function ajax_reprocessing_exchange(
        ?CExchangeDataFormat $exchange = null,
        bool $quiet = false
    ): CExchangeDataFormat {
        if (!$exchange && ($exchange_guid = CView::get("exchange_guid", "str"))) {
            CView::checkin();
            $exchange = CStoredObject::loadFromGuid($exchange_guid);
        }

        try {
            $exchange->reprocessing();
        } catch (CMbException $e) {
            if (!$quiet) {
                $e->stepAjax(UI_MSG_ERROR);
            }

            if ($exchange_guid ?? false) {
                throw $e;
            }
        }

        if (!$exchange->_id && !$quiet) {
            CAppUI::stepAjax("CExchangeAny-msg-delete", UI_MSG_ALERT);
        }

        if (!$quiet) {
            CAppUI::stepAjax("CExchangeDataFormat-reprocessed");
        }

        return $exchange;
    }

    /**
     * @param CExchangeDataFormat|null $exchange
     * @param bool                     $quiet
     *
     * @return CExchangeDataFormat
     * @throws CMbException
     */
    public function ajax_send_message(?CExchangeDataFormat $exchange = null, bool $quiet = false): CExchangeDataFormat
    {
        if (!$exchange && ($exchange_guid = CView::get("exchange_guid", "str"))) {
            CView::checkin();
            $exchange = CStoredObject::loadFromGuid($exchange_guid);
        }

        try {
            $exchange->send();
        } catch (CMbException $e) {
            if (!$quiet) {
                $e->stepAjax(UI_MSG_ERROR);
            }

            // from parameters
            if ($exchange_guid ?? false) {
                throw $e;
            }
        }

        if (!$exchange->_id && !$quiet) {
            CAppUI::stepAjax("CExchangeAny-msg-delete", UI_MSG_ALERT);
        }

        if (!$quiet) {
            CAppUI::stepAjax("CExchangeDataFormat-confirm-exchange sent", UI_MSG_OK, CAppUI::tr("$exchange->_class"));
        }

        return $exchange;
    }

    /**
     * @param array $exchanges
     *
     * @throws Exception
     */
    private function exportInCsv(array $exchanges): void
    {
        ob_end_clean();
        header("Content-Type: text/plain;charset=" . CApp::$encoding);
        header("Content-Disposition: attachment;filename=\"export_stream_hl7.csv\"");

        $fp  = fopen("php://output", "w");
        $csv = new CCSVFile($fp);

        $titles = [
            CAppUI::tr("CInteropReceiver-libelle"),
            CAppUI::tr("Message-id"),
            CAppUI::tr("Movement-id"),
            CAppUI::tr("Message-type"),
            CAppUI::tr("Action"),
            CAppUI::tr("Initial-movement"),
            CAppUI::tr("Date-of-movement"),
            CAppUI::tr("CPatient-_IPP"),
            CAppUI::tr("NDA"),
            CAppUI::tr("UF-H"),
            CAppUI::tr("UF-M"),
            CAppUI::tr("UF-S"),
            CAppUI::tr("CChambre"),
            CAppUI::tr("CLit"),
            CAppUI::tr("CSejour-praticien_id-desc"),
            CAppUI::tr("CSejour-entree_prevue"),
            CAppUI::tr("CSejour-sortie_prevue"),
            CAppUI::tr("CSejour-entree_reelle"),
            CAppUI::tr("CSejour-sortie_reelle"),
        ];
        $csv->writeLine($titles);

        foreach ($exchanges as $_exchange) {
            $_exchange->loadRefsInteropActor();
            if ($_exchange->receiver_id) {
                /** @var CInteropReceiver $actor */
                $actor = $_exchange->_ref_receiver;
                $actor->loadConfigValues();
            } else {
                /** @var CInteropSender $actor */
                $actor = $_exchange->_ref_sender;
                $actor->getConfigs($_exchange);
            }

            $hl7Message = $_exchange->getMessage();

            /** @var CHL7v2MessageXML $xml */
            $xml = $hl7Message->toXML(null, false);

            $MSH = $xml->queryNode("//MSH");
            $PID = $xml->queryNodeByIndex("//PID");
            $PV1 = $xml->queryNodeByIndex("//PV1");
            $PV2 = $xml->queryNodeByIndex("//PV2");
            $ZBE = $xml->queryNode("//ZBE");

            $data = [
                "ACTOR_LIBELLE" => null,
                "ID_MSG"        => null,
                "ID_MOVEMENT"   => null,
                "TYPE_MSG"      => null,
                "ACTION"        => null,
                "MI"            => null,
                "DATE_MOVEMENT" => null,
                "IPP"           => null,
                "NDA"           => null,
                "UF-H"          => null,
                "UF-M"          => null,
                "UF-S"          => null,
                "CHAMBRE"       => null,
                "LIT"           => null,
                "PRAT_RESP"     => null,
                "ENTREE_PREVUE" => null,
                "SORTIE_PREVUE" => null,
                "ENTREE_REELLE" => null,
                "SORTIE_REELLE" => null,
            ];

            $data["ACTOR_LIBELLE"] = $actor->libelle;
            $name_config           = $_exchange->receiver_id ? "build_NDA" : "handle_NDA"; // ? receiver : sender
            // NDA
            if (CMbArray::get($actor->_configs, "$name_config") === "PID_18") {
                $data["NDA"] = $PID ? $this->escapeData($xml->queryTextNode("PID.18/CX.1", $PID)) : null;
            } else {
                $data["NDA"] = $PV1 ? $this->escapeData($xml->queryTextNode("PV1.19/CX.1", $PV1)) : null;
            }

            //IPP
            foreach ($xml->query("PID.3", $PID) as $_node) {
                $identifier_type_code = $xml->queryTextNode("CX.5", $_node);
                if ($identifier_type_code === "PI") {
                    $data["IPP"] = $this->escapeData($xml->queryTextNode("CX.1", $_node));
                }
            }

            $data["DATE_MOVEMENT"] = CMbDT::dateToLocale($xml->queryTextNode("PID.33", $PID));
            $data["TYPE_MSG"]      = $xml->queryTextNode("MSH.9/MSG.2", $MSH);
            $data["ID_MSG"]        = $this->escapeData($xml->queryTextNode("MSH.10", $MSH));

            if ($ZBE) {
                $data["ID_MOVEMENT"] = $this->escapeData($xml->queryTextNode("ZBE.1/EI.1", $ZBE));
                $data["ACTION"]      = $xml->queryTextNode("ZBE.4", $ZBE);
                $data["MI"]          = $xml->queryTextNode("ZBE.6", $ZBE);
                if ($ZBE_7 = $xml->queryNode("ZBE.7", $ZBE)) {
                    $data["UF-M"] = $this->escapeData($xml->queryTextNode("XON.10", $ZBE_7));
                }
                if ($ZBE_8 = $xml->queryNode("ZBE.8", $ZBE)) {
                    $data["UF-S"] = $this->escapeData($xml->queryTextNode("XON.10", $ZBE_8));
                }
            }

            if ($PV1) {
                if ($PV1_3 = $xml->queryNode("PV1.3", $PV1)) {
                    $data["LIT"]     = $this->escapeData($xml->queryTextNode("PL.3", $PV1_3));
                    $data["CHAMBRE"] = $this->escapeData($xml->queryTextNode("PL.2", $PV1_3));
                    $data["UF-H"]    = $this->escapeData($xml->queryTextNode("PL.1", $PV1_3));
                }
                if ($PV1_7 = $xml->query("PV1.7", $PV1)->item(0)) {
                    $firstname         = $xml->queryTextNode("XCN.2/FN.1", $PV1_7);
                    $data["PRAT_RESP"] = $firstname . " " . $xml->queryTextNode("XCN.3", $PV1_7);
                }
                $data["ENTREE_REELLE"] = CMbDT::dateToLocale($xml->queryTextNode("PV1.44/TS.1", $PV1));
                $data["SORTIE_REELLE"] = CMbDT::dateToLocale($xml->queryTextNode("PV1.45/TS.1", $PV1));
            }

            if ($PV2) {
                $data["ENTREE_PREVUE"] = CMbDT::dateToLocale($xml->queryTextNode("PV2.8/TS.1", $PV2));
                $data["SORTIE_PREVUE"] = CMbDT::dateToLocale($xml->queryTextNode("PV2.9/TS.1", $PV2));
            }
            $csv->writeLine($data);
        }

        $this->rip();
    }

    /**
     * @param string $data
     *
     * @return string|null
     */
    private function escapeData(?string $data): ?string
    {
        if (!is_string($data) || $data === "") {
            return null;
        }

        return "'" . $data . "'";
    }

    /**
     * @throws Exception
     */
    private function deleteExchanges($ids): void
    {
    }

    /**
     * @return void
     * @throws Exception
     */
    public function ajaxViewAllSourcesFilter(): void
    {
        CCanDo::checkRead();

        $source_class = CView::get("source_class", "str");
        $name         = CView::get("name", "str");
        $role         = CView::get("role", "str");
        $active       = CView::get("active", "bool default|0");
        $loggable     = CView::get("loggable", "bool default|0");
        $blocked      = CView::get("blocked", "num default|0");

        CView::checkin();

        /** @var CExchangeSource $exchange_source */
        $exchange_source = new $source_class();


        //si on veux afficher toute les sources sinon on applique les filtres
        $where = [];
        $ds    = $exchange_source->getDS();

        if ($name != "") {
            $where["name"] = $ds->prepareLike("%" . $name . "%");
        }

        if ($active != "") {
            $where["active"] = $ds->prepare("= ?", $active);
        }

        if ($loggable != "") {
            $where["loggable"] = $ds->prepare("= ?", $loggable);
        }

        if ($role != "") {
            $where["role"] = $ds->prepare("= ?", $role);
        }

        $sources = $exchange_source->loadList($where);

        if ($blocked != "") {
            foreach ($sources as $key => $source) {
                if (!$source instanceof CExchangeSourceAdvanced) {
                    continue;
                }

                if ($source->getBlockedStatus() != $blocked) {
                    unset($sources[$key]);
                }
            }
        }

        $smarty = new CSmartyDP();
        $smarty->assign("_sources", $sources);
        $smarty->assign("name", $source_class);
        $smarty->display("inc_vw_sources.tpl");
    }

    public function ajaxUnlockAdvancedSource(): void
    {
        CCanDo::check();

        // Check params
        $exchange_source_name = CView::get("exchange_source_name", "str");
        $exchange_source_class = CView::get("exchange_source_class", "str");

        CView::checkin();
        if ($exchange_source_name == null) {
            CAppUI::stepMessage(UI_MSG_ERROR, "CExchangeSource-no-source", $exchange_source_name);
        }

        /** @var CExchangeSourceAdvanced $exchange_source
         */
        $exchange_source = CExchangeSourceAdvanced::get($exchange_source_name, $exchange_source_class, false, null, false);
        if (!$exchange_source->_id) {
            CAppUI::stepMessage(UI_MSG_ERROR, "CExchangeSource-no-source", $exchange_source_name);
        }

        if($exchange_source instanceof CExchangeSourceAdvanced) {
            try {
                $exchange_source->unlockSource();
                CAppUI::stepMessage(E_USER_NOTICE, "CSourceSFTP-unlock-success", $exchange_source);
            } catch (CMbException $e) {
                $e->stepAjax();

                return;
            }
        }

        CApp::rip();
    }
}
