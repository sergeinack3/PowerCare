<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Dmi\CDMSterilisation;
use Ox\Mediboard\Prescription\CAdministrationDM;

/**
 * Product Order Item Reception
 */
class CProductOrderItemReception extends CMbObject implements IProductRelated, IProductStockGroupRelated
{
    // DB Table key
    static $_load_lite = false;

    // DB Fields
    /** @var string[][] */
    private static $fields_etiq = [
        "NOM PRODUIT",
        "CODE PRODUIT",
        "CODE LOT",
        "DATE PEREMPTION",
        "CODE BARRE LOT",
    ];
    public         $order_item_reception_id;
    public         $order_item_id;
    public         $reception_id;
    public         $libelle;
    public         $quantity;
    public         $code;
    public         $serial;
    public         $lapsing_date;
    public         $date;
    public         $barcode_printed;
    public         $cancelled;
    /** @var bool */
    public $status;
    /** @var CProductOrderItem */
    public $_ref_order_item;
    /** @var CProductReception */
    public $_ref_reception;
    /** @var CProductOrderItemReception */
    public $_ref_dm_sterilisation;
    public $_cancel;
    public $_price;
    public $_price_ttc;
    public $_num_sterilisation;

    // #TEMP#
    public $_price_tva;
    public $_product_id;
    public $units_fixed;
    public $orig_quantity;

    /**
     * Reset the lot status
     *
     * @return void
     * @throws Exception
     */
    public static function resetLotStatus(int $prescription_id, int $operation_id): void
    {
        $where             = [];
        $administration_dm = new CAdministrationDM();
        $ds                = $administration_dm->getDS();

        $where["prescription_id"] = $ds->prepare("= ?", $prescription_id);
        $where["operation_id"]    = $ds->prepare("= ?", $operation_id);
        $dmis                     = $administration_dm->loadList($where);

        CStoredObject::massLoadFwdRef($dmis, "order_item_reception_id");

        foreach ($dmis as $_line_dmi) {
            $_line_dmi->loadRefsFwd();
            $lot         = $_line_dmi->loadRefProductOrderItemReception();
            $lot->status = '';

            if ($msg = $lot->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
        }
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        $this->completeField("reception_id");

        $is_new = !$this->_id;

        if ($is_new && $this->cancelled === null) {
            $this->cancelled = 0;
        }

        if ($is_new) {
            $this->loadRefOrderItem();
            $this->_ref_order_item->loadOrder();
        }

        if ($is_new && !$this->reception_id) {
            $order                 = $this->_ref_order_item->_ref_order;
            $reception             = new CProductReception;
            $reception->date       = CMbDT::dateTime();
            $reception->societe_id = $order->societe_id;
            $reception->group_id   = CProductStockGroup::getHostGroup();

            // Recherche de receptions ayant un numero de reception similaire pour gerer l'increment
            if ($order->order_number) {
                $where                = ["reference" => "LIKE '{$order->order_number}%'"];
                $number               = $reception->countList($where) + 1;
                $reception->reference = "{$order->order_number}-$number";
            }

            if ($msg = $reception->store()) {
                return $msg;
            }

            $this->reception_id = $reception->_id;
        }

        $stock = null;

        if ($is_new) {
            $this->_ref_order_item->loadRefsFwd();
            $this->_ref_order_item->_ref_reference->loadRefsFwd();
            $this->_ref_order_item->_ref_reference->_ref_product->loadRefStock();

            $product = &$this->_ref_order_item->_ref_reference->_ref_product;
            $product->updateFormFields();

            $stock = $product->loadRefStock();

            if ($stock->_id) {
                $stock->quantity += $this->quantity;
            } else {
                $qty                        = $this->quantity;
                $stock                      = new CProductStockGroup();
                $stock->product_id          = $product->_id;
                $stock->group_id            = CProductStockGroup::getHostGroup();
                $stock->quantity            = $qty;
                $stock->order_threshold_min = $qty;

                CAppUI::setMsg("Un nouveau stock a été créé", UI_MSG_OK);
                //CAppUI::setMsg("Un nouveau stock pour [%s] a été créé", UI_MSG_OK, $product->_view);
            }

            if ($msg = $stock->store()) {
                return $msg;
            }
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        // If the order is received, we set the flag
        if ($is_new) {
            if ($this->_ref_order_item->_ref_reference->societe_id) {
                CProductMovement::logTransaction(
                    $this,
                    $this->_ref_order_item->_ref_reference->loadRefSociete(),
                    $stock
                );
            }

            $order = $this->_ref_order_item->_ref_order;
            if (!$order->received) {
                $count_renewed  = $order->countRenewedItems();
                $count_received = $order->countReceivedItems() - (count($order->_ref_order_items) - $count_renewed);

                if ($count_renewed && ($count_received >= $count_renewed)) {
                    $order->received = 1;
                    $order->store();
                }
            }

            if ($this->_num_sterilisation) {
                $sterilisation = new CDMSterilisation();
                $sterilisation->lot_id = $this->_id;
                $sterilisation->num_sterilisation = $this->_num_sterilisation;
                if ($msg = $sterilisation->store()) {
                    return $msg;
                }
            }
        }

        return null;
    }

    /**
     * Load order item
     *
     * @return CProductOrderItem
     */
    function loadRefOrderItem()
    {
        return $this->_ref_order_item = $this->loadFwdRef("order_item_id", true);
    }

    /**
     * Getter to fields_etiq variale
     *
     * @return array
     * @throws Exception
     */
    public static function getFieldsEtiq()
    {
        return self::$fields_etiq;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'product_order_item_reception';
        $spec->key   = 'order_item_reception_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                       = parent::getProps();
        $props['order_item_id']      = 'ref notNull class|CProductOrderItem back|receptions';
        $props['reception_id']       = 'ref notNull class|CProductReception back|reception_items';
        $props["libelle"]            = "str";
        $props['quantity']           = 'num notNull';
        $props['code']               = 'str seekable';
        $props['serial']             = 'str maxLength|40';
        $props['lapsing_date']       = 'date mask|99/99/9999 format|$3-$2-$1';
        $props['date']               = 'dateTime notNull';
        $props['barcode_printed']    = 'bool';
        $props['cancelled']          = 'bool notNull default|0';
        $props['status']             = 'enum list|ok|ko|i';
        $props['_price']             = 'currency';
        $props['_price_ttc']         = 'currency';
        $props['_price_tva']         = 'currency';
        $props["_product_id"]        = "ref class|CProduct";
        $props['_num_sterilisation'] = 'str';
            // #TEMP#
        $props['units_fixed'] = 'bool show|0';
        $props['orig_quantity']      = 'num show|0';

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = $this->quantity;
        if ($this->code) {
            $this->_view .= " [$this->code]";
        }
    }

    /**
     * Compite price
     *
     * @return float
     */
    function computePrice()
    {
        $this->loadRefOrderItem();

        $this->_price     = $this->quantity * $this->_ref_order_item->unit_price;
        $this->_price_ttc = round($this->_price * (1 + $this->_ref_order_item->tva / 100), 2);
        $this->_price_tva = round(($this->_price * $this->_ref_order_item->tva / 100), 2);

        return $this->_price;
    }

    /**
     * @inheritdoc
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();

        if (self::$_load_lite) {
            return;
        }

        $this->loadRefOrderItem();
        $this->loadRefReception();
    }

    /**
     * Load reception
     *
     * @return CProductReception
     */
    function loadRefReception()
    {
        return $this->_ref_reception = $this->loadFwdRef("reception_id", true);
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();

        $this->_view = $this->loadRefReception()->_view;
    }

    /**
     * @inheritdoc
     */
    function delete()
    {
        $this->completeField("order_item_id", "quantity");

        $this->loadRefOrderItem();
        $item = $this->_ref_order_item;

        $item->loadReference();
        $reference = $item->_ref_reference;

        $reference->loadRefProduct();
        $product = $reference->_ref_product;

        if ($product->loadRefStock()) {
            $product->_ref_stock_group->quantity -= $this->quantity;
        }

        // If the order is already flagged as received,
        // we check if it will still be after deletion
        $item->loadOrder();
        $order = $item->_ref_order;

        if ($order->_id && $order->received) {
            $count_renewed  = $order->countRenewedItems();
            $count_received = $order->countReceivedItems() - (count($order->_ref_order_items) - $count_renewed);

            if ($count_received < $count_renewed) {
                $order->received = 0;
            }
        }

        if ($msg = parent::delete()) {
            return $msg;
        }

        // we store other objects only if deletion was ok !
        if ($product->_ref_stock_group && $product->_ref_stock_group->_id) {
            $product->_ref_stock_group->store();
        }

        if ($order && $order->_id) {
            $order->store();
        }

        return null;
    }

    /**
     * Get used quantity (for DMIs)
     *
     * @return int
     */
    function getUsedQuantity()
    {
        $query = "SELECT SUM(administration_dm.quantity) 
              FROM administration_dm
              WHERE administration_dm.order_item_reception_id = $this->_id";
        $ds    = $this->_spec->ds;
        $row   = $ds->fetchRow($ds->query($query));

        return intval(reset($row));
    }

    /**
     * @inheritdoc
     */
    function loadRelProduct()
    {
        return $this->loadRefOrderItem()->loadReference()->loadRefProduct();
    }

    /**
     * @inheritdoc
     */
    function loadRelProductStockGroup()
    {
        return $this->loadRefOrderItem()->getStock();
    }

    /**
     * @inheritdoc
     */
    function completeLabelFields(&$fields, $params)
    {
        /** @var CProduct $product */
        $product = $this->loadRefOrderItem()->loadReference()->loadRefProduct();

        $split_product_name = explode("\n", wordwrap($product->name, 30, "\n", true));
        $product_name       = $split_product_name[0];

        if (isset($split_product_name[1])) {
            $product_name .= "<br />" . $split_product_name[1];
        }

        $fields = array_merge(
            $fields,
            [
                "NOM PRODUIT"     => $product_name,
                "CODE PRODUIT"    => $product->code,
                "CODE LOT"        => $this->code,
                "DATE PEREMPTION" => CMbDT::dateToLocale($this->lapsing_date),
                "CODE BARRE LOT"  => "@BARCODE_MB" . str_pad($this->_id, 8, "0", STR_PAD_LEFT) . "@",
            ]
        );
    }
}
