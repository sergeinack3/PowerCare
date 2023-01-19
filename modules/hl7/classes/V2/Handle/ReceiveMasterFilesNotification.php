<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use DOMNode;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Pharmacie\CStockSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Stock\CProduct;
use Ox\Mediboard\Stock\CProductStockGroup;
use Ox\Mediboard\Stock\CProductStockService;
use Ox\Mediboard\Stock\CStockMouvement;

/**
 * Class ReceiveMasterFilesNotification
 * Master files notification, message XML HL7
 */
class ReceiveMasterFilesNotification extends CHL7v2MessageXML
{
    static $event_codes = ["M15"];

    /**
     * Get contents
     *
     * @return array
     */
    function getContentNodes()
    {
        $data = [];

        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        // Software Segment
        $this->queryNodes("SFT", null, $data, true);

        // User Authentication Credential
        $this->queryNode("UAC", null, $data, true);

        // Master File Identification
        $this->queryNode("MFI", null, $data, true);

        $MF_INV_ITEM   = $this->queryNodes("MFN_M15.MF_INV_ITEM", null, $varnull, true);
        $data["items"] = [];
        foreach ($MF_INV_ITEM as $_MF_INV_ITEM) {
            $tmp = [];
            // MFE - Master File Entry
            $this->queryNode("MFE", $_MF_INV_ITEM, $tmp, null);

            // IIM - Inventory Item Master
            $this->queryNode("IIM", $_MF_INV_ITEM, $tmp, null);

            $data["items"][] = $tmp;
        }

        return $data;
    }

    /**
     * Handle receive order message
     *
     * @param CHL7Acknowledgment $ack     Acknowledgment
     * @param CMbObject          $patient Person
     * @param array              $data    Data
     *
     * @return string|void
     */
    function handle(CHL7Acknowledgment $ack = null, CMbObject $patient = null, $data = [])
    {
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $sender         = $exchange_hl7v2->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        // Récupération des items
        foreach ($data['items'] as $_item) {
            // CProduct
            $product_code = $this->getProductItemCode($_item['IIM']);
            if (!$product_code) {
                return $exchange_hl7v2->setAckAR($ack, 'E7001', null, $patient);
            }

            $product       = new CProduct();
            $product->code = $product_code;
            $product->loadMatchingObjectEsc();

            if (!$product->_id) {
                return $exchange_hl7v2->setAckAR($ack, 'E7002', null, $patient);
            }

            $stock_cible = null;
            switch (CMbArray::get($sender->_configs, "handle_IIM_6")) {
                // CStockSejour
                case 'sejour':
                    $NDA_id = $this->getInventoryLocationCode($_item['IIM']);
                    if (!$NDA_id) {
                        return $exchange_hl7v2->setAckAR($ack, 'E7003', null, $patient);
                    }

                    $NDA = CIdSante400::getMatch('CSejour', $sender->_tag_sejour, $NDA_id);
                    if (!$NDA->_id) {
                        return $exchange_hl7v2->setAckAR($ack, 'E7008', null, $patient);
                    }

                    $article = CMedicamentArticle::get($product->code);

                    $stock_sejour = CStockSejour::getFromCIP($article->getId(), $NDA->object_id);
                    if (!$stock_sejour->_id) {
                        if ($msg = $stock_sejour->store()) {
                            return $exchange_hl7v2->setAckAR($ack, 'E7009', null, $patient);
                        }
                    }

                    $stock_cible = $stock_sejour;

                    break;

                // CProductStockService
                case 'service':
                    $service_item_code = $this->getInventoryLocationCode($_item['IIM']);
                    if (!$service_item_code) {
                        return $exchange_hl7v2->setAckAR($ack, 'E7003', null, $patient);
                    }

                    $service       = new CService();
                    $service->code = $service_item_code;
                    $service->loadMatchingObjectEsc();

                    if (!$service->_id) {
                        return $exchange_hl7v2->setAckAR($ack, 'E7004', null, $patient);
                    }

                    $stock_service = CProductStockService::getFromCode($product->code, $service->_id);
                    if (!$stock_service->_id) {
                        if ($msg = $stock_service->store()) {
                            return $exchange_hl7v2->setAckAR($ack, 'E7005', null, $patient);
                        }
                    }

                    $stock_cible = $stock_service;

                    break;

                default:
                    return $exchange_hl7v2->setAckAR($ack, 'E7007', null, $patient);
            }

            if (!$stock_cible || !$stock_cible->_id) {
                return $exchange_hl7v2->setAckAR($ack, 'E7010', null, $patient);
            }

            // CStockMouvement
            $stock_mouvement                   = new CStockMouvement();
            $stock_mouvement->type             = $this->getProcedureCode($_item['IIM']);
            $stock_mouvement->etat             = 'en_cours';
            $stock_mouvement->_increment_stock = false;

            $product_stock_group             = new CProductStockGroup();
            $product_stock_group->group_id   = $sender->group_id;
            $product_stock_group->product_id = $product->_id;
            $product_stock_group->loadMatchingObject();
            if ($stock_mouvement->type === 'delivrance') {
                if ($product_stock_group->_id) {
                    // CProductStockGroup
                    $stock_mouvement->setSource($product_stock_group);
                }

                // CSejour ou CService
                $stock_mouvement->setCible($stock_cible);
            } elseif (
                $stock_mouvement->type === 'administration' || $stock_mouvement->type === 'destruction' ||
                $stock_mouvement->type === 'disparition' || $stock_mouvement->type === 'apparition' ||
                $stock_mouvement->type === 'delivre_patient'
            ) {
                $stock_mouvement->setSource($stock_cible);
                $stock_mouvement->etat             = 'realise';
                $stock_mouvement->_increment_stock = true;
            } elseif ($stock_mouvement->type === 'retour_pharma') {
                // CSejour ou CService
                $stock_mouvement->setSource($stock_cible);
                // CProductStockGroup
                $stock_mouvement->setCible($product_stock_group);
                $stock_mouvement->etat             = 'realise';
                $stock_mouvement->_increment_stock = true;
            } elseif ($stock_mouvement->type === 'apport_service') {
                if ($stock_cible instanceof CStockSejour) {
                    $stock_mouvement->setCible($stock_cible);
                } else {
                    $stock_mouvement->setSource($stock_cible);
                }
            } elseif ($stock_mouvement->type === 'retour_service') {
                if ($stock_cible instanceof CStockSejour) {
                    $stock_mouvement->setSource($stock_cible);
                } else {
                    $stock_mouvement->setCible($stock_cible);
                }
            } elseif ($stock_mouvement->type === 'transfert_patient') {
                if ($this->getQuantity($_item['IIM'], false) > 0) {
                    $stock_mouvement->setCible($stock_cible);
                } else {
                    $stock_mouvement->setSource($stock_cible);
                }
            }

            $stock_mouvement->quantite = $this->getQuantity($_item['IIM'], true);
            $stock_mouvement->datetime = CMbDT::dateTime($this->getDatetime($_item['IIM']));
            if ($msg = $stock_mouvement->store()) {
                return $exchange_hl7v2->setAckAR($ack, 'E7006', $msg, $patient);
            }

            return $exchange_hl7v2->setAckAA($ack, 'I7000', null, $stock_mouvement);
        }
    }

    /**
     * Get product item code
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getProductItemCode(DOMNode $node)
    {
        return $this->queryTextNode("IIM.1/CWE.1", $node);
    }

    /**
     * Get service item code
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getInventoryLocationCode(DOMNode $node)
    {
        return $this->queryTextNode("IIM.6/CWE.1", $node);
    }

    /**
     * Get procedure code
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getProcedureCode(DOMNode $node)
    {
        return $this->queryTextNode("IIM.14", $node);
    }

    /**
     * Get service item code
     *
     * @param DOMNode $node     ORC node
     * @param bool    $absolute Absolute value
     *
     * @return string
     */
    function getQuantity(DOMNode $node, $absolute = true)
    {
        if (!$IIM_12 = $this->queryTextNode("IIM.12", $node)) {
            return null;
        }

        return $absolute ? abs($IIM_12) : $IIM_12;
    }

    /**
     * Get service item code
     *
     * @param DOMNode $node ORC node
     *
     * @return string
     */
    function getDatetime(DOMNode $node)
    {
        return $this->queryTextNode("IIM.7/TS.1", $node);
    }
}
