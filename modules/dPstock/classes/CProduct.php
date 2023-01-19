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
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Dispensation\Dispensation;
use Ox\Mediboard\Dmi\CDMSterilisation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Medicament\CMedicament;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Product
 */
class CProduct extends CMbObject
{
    /** @var int */
    public $product_id;

    // DB Fields
    /** @var string */
    public $name;
    /** @var string */
    public $description;

    // codage
    /** @var string */
    public $code;

    /** @var string */
    public $code_canonical;

    /** @var string */
    public $scc_code; // in the barcodes (http://www.morovia.com/education/symbology/scc-14.asp)

    /** @var string */
    public $bdm;

    /** @var int */
    public $category_id;

    /** @var int */
    public $societe_id;

    /** @var int */
    public $quantity;

    /** @var string */
    public $item_title;

    /** @var int */
    public $code_up_disp;

    /** @var int */
    public $unit_quantity;

    /** @var string */
    public $unit_title;

    /** @var int */
    public $code_up_adm;

    /** @var string */
    public $packaging;

    /** @var int */
    public $renewable;

    /** @var int */
    public $cancelled;

    /** @var int */
    public $equivalence_id;

    /** @var int */
    public $auto_dispensed;

    // classif
    /** @var string */
    public $classe_comptable;

    /** @var string */
    public $cladimed;

    /** @var CProductCategory */
    public $_ref_category;

    /** @var CSociete */
    public $_ref_societe;

    /** @var CProductStockGroup[] */
    public $_ref_stocks_group;

    /** @var CProductStockService[] */
    public $_ref_stocks_service;

    /** @var CProductReference[] */
    public $_ref_references;

    /** @var CProductReference */
    public $_ref_last_reference;

    /** @var CProductOrderItemReception[] */
    public $_ref_lots;

    /** @var CProductSelection[] */
    public $_ref_selections;

    /** @var CProductStockLocation[] */
    public $_ref_emplacements_group;

    // Undividable quantity
    /** @var int */
    public $_unit_quantity;

    /** @var string */
    public $_unit_title;

    /** @var int */
    public $_quantity; // The quantity view

    /** @var int */
    public $_consumption;

    /** @var int */
    public $_supply;

    /** @var int */
    public $_unique_usage;

    /** @var CProductOrderItem[] */
    public $_in_order;

    /** @var string */
    public $_classe_atc;

    /** @var int */
    public $_create_stock_quantity;

    /** @var float */
    public $_price_tva;

    /** @var CProductStockGroup This group's stock id */
    public $_ref_stock_group;
    /** @var CMedicamentArticle */
    public $_med_article;

    /** @var CProductOrderItemReception */
    public $_new_lot;

    /** @var CProductOrderItemReception[] */
    public $_lots = [];

    /** @var string */
    public const QR_PRODUCT = "01034009";

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec                  = parent::getSpec();
        $spec->table           = 'product';
        $spec->key             = 'product_id';
        $spec->uniques["code"] = ["code", "category_id", "bdm"];
        $spec->uniques["name"] = ["name", "category_id", "bdm"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                = parent::getProps();
        $props['name']        = 'str notNull seekable show|0';
        $props['description'] = 'text seekable';

        // codage
        $props['code']           = 'str maxLength|32 seekable protected';
        $props['code_canonical'] = 'str maxLength|32 seekable show|0';
        $props['scc_code']       = 'numchar length|10 seekable|equal protected'; // Manufacturer Code + Item Number

        $props["bdm"]            = "enum list|bcb|vidal|besco default|bcb";
        $props['category_id']    = 'ref notNull class|CProductCategory autocomplete|name back|products';
        $props['societe_id']     = 'ref class|CSociete seekable autocomplete|name back|products';
        $props['quantity']       = 'num notNull min|0 show|0';
        $props['item_title']     = 'str autocomplete show|0';
        $props['code_up_disp']   = 'num';
        $props['unit_quantity']  = 'float min|0 show|0';
        $props['unit_title']     = 'str autocomplete show|0';
        $props['code_up_adm']    = 'num';
        $props['packaging']      = 'str autocomplete';
        $props['renewable']      = 'enum list|0|1|2';
        $props['cancelled']      = 'bool default|0 show|0';
        $props['equivalence_id'] = 'ref class|CProductEquivalence back|products';
        $props['auto_dispensed'] = 'bool default|0';

        // classif
        $props['cladimed']         = 'str maxLength|7 autocomplete';
        $props['classe_comptable'] = 'str maxLength|9 autocomplete';

        $props['_unit_title']            = 'str';
        $props['_unique_usage']          = 'bool';
        $props['_unit_quantity']         = 'float min|0';
        $props['_quantity']              = 'str show|1';
        $props['_consumption']           = 'num show|1';
        $props['_create_stock_quantity'] = 'num min|0';
        $props['_price_tva']             = 'currency show|0';

        $props['_classe_atc'] = 'str';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->_view = $this->name;

        if (
            CModule::getActive("dispensation")
            && CAppUI::gconf("dispensation general use_dispentation_ucd")
            && CMedicament::getBase() === "besco"
        ) {
            $article = CMedicamentArticle::get($this->code);
            if ($article->loadRefProduit()->ucd_view) {
                $this->_view = $article->_ref_produit->ucd_view;
            }
        }

        if ($this->unit_quantity !== null && $this->unit_quantity == round($this->unit_quantity)) {
            // float to int (the comma is deleted)
            $this->unit_quantity = round($this->unit_quantity);
        }
        if ($this->unit_quantity === 0) {
            $this->unit_quantity = '';
        }

        $this->_quantity = '';
        if ($this->item_title && $this->quantity) {
            $this->_quantity .= "$this->quantity $this->item_title";
        }

        if ($this->item_title && $this->quantity) {
            $this->_unit_quantity = ($this->quantity ? $this->quantity : 1);
            $this->_unit_title    = $this->item_title;
        } else {
            $this->_unit_quantity = ($this->unit_quantity ? $this->unit_quantity : 1);
            $this->_unit_title    = $this->unit_title;
        }

        $this->_unique_usage = ($this->unit_quantity < 2 && !$this->renewable);
    }

    /**
     * Load references
     *
     * @param bool $cache Use object cache
     *
     * @return CProductReference[]
     */
    public function loadRefsReferences($cache = false): array
    {
        if ($cache && !empty($this->_ref_references)) {
            return $this->_ref_references;
        }

        return $this->_ref_references = $this->loadBackRefs('references', 'most_used_ref DESC');
    }

    /**
     * Load last reference
     *
     * @return CProductReference
     */
    public function loadRefLastReference(): CProductReference
    {
        return $this->_ref_last_reference = $this->loadLastBackRef('references', 'reference_id ASC');
    }

    /**
     * @inheritdoc
     */
    public function loadRefsBack(): void
    {
        $this->loadRefsReferences();
        $this->_ref_stocks_group = $this->loadBackRefs('stocks_group');

        $ljoin = [
            'service' => "service.service_id = product_stock_service.object_id AND product_stock_service.object_class = 'CService'",
        ];

        $this->_ref_stocks_service = $this->loadBackRefs('stocks_service', "service.nom", null, null, $ljoin);
        $this->_ref_selections     = $this->loadBackRefs('selections');
    }

    /**
     * @return CStoredObject[]|null
     * @throws Exception
     */
    public function loadRefsSelection(): array
    {
        return $this->_ref_selections = $this->loadBackRefs('selections');
    }

    /**
     * @return CStoredObject[]|null
     * @throws Exception
     */
    public function loadRefsStocksService(): array
    {
        $ljoin = [
            'service' => "service.service_id = product_stock_service.object_id AND product_stock_service.object_class = 'CService'",
        ];

        return $this->_ref_stocks_service = $this->loadBackRefs(
            'stocks_service',
            "service.nom",
            null,
            null,
            $ljoin,
            null,
            "stocks_product_service"
        );
    }

    /**
     * @return CStoredObject[]|null
     * @throws Exception
     */
    public function loadRefsStocksGroup(): array
    {
        return $this->_ref_stocks_group = $this->loadBackRefs('stocks_group');
    }

    /**
     * @inheritdoc
     */
    public function loadRefsFwd(): void
    {
        $this->loadRefCategory();
        $this->loadRefSociete();
    }

    /**
     * Load category
     *
     * @return CProductCategory
     */
    public function loadRefCategory(): CProductCategory
    {
        return $this->_ref_category = $this->loadFwdRef("category_id", true);
    }

    /**
     * Load manufacturer
     *
     * @return CSociete
     */
    public function loadRefSociete(): CSociete
    {
        return $this->_ref_societe = $this->loadFwdRef("societe_id", true);
    }

    /**
     * Loads the stock associated to the current group
     *
     * @param bool $cache Use object cache
     *
     * @return CProductStockGroup
     */
    public function loadRefStock(bool $cache = true): CProductStockGroup
    {
        if ($this->_ref_stock_group && $cache) {
            return $this->_ref_stock_group;
        }

        // Coneserver le loadMatchingObject car group_id et product_id sont
        // utilisés (au moins dans CProduitLivretTherapeutique::addToStocks)
        $this->completeField("product_id");
        $this->_ref_stock_group             = new CProductStockGroup();
        $this->_ref_stock_group->group_id   = CProductStockGroup::getHostGroup();
        $this->_ref_stock_group->product_id = $this->product_id;

        $this->_ref_stock_group->loadMatchingObject();

        return $this->_ref_stock_group;
    }

    /**
     * @inheritdoc
     */
    public function getPerm($permType)
    {
        if (!$this->_ref_category) {
            $this->loadRefsFwd();
        }

        return $this->_ref_category->getPerm($permType);
    }

    /**
     * Load lots
     *
     * @return CProductOrderItemReception[]
     */
    public function loadRefsLots(): array
    {
        $ljoin = [
            "product_order_item" => "product_order_item_reception.order_item_id = product_order_item.order_item_id",
            "product_reference"  => "product_order_item.reference_id = product_reference.reference_id",
            "product"            => "product_reference.product_id = product.product_id",
        ];

        $where = [
            "product.product_id" => " = '$this->_id'",
        ];

        $lot = new CProductOrderItemReception();

        return $this->_ref_lots = $lot->loadList($where, "date DESC", null, null, $ljoin);
    }

    /**
     * Charge les lots disponibles
     *
     * @param int $dm_id Référence vers un éventuel DM
     *
     * @return CProductOrderItemReception[]|null
     * @throws \Exception
     */
    public function loadRefsLotsAvailable()
    {
        $dmi_category_id = CAppUI::gconf("dmi CDM product_category_id");
        $group_id        = CGroups::loadCurrent()->_id;

        $where = [
            "product.product_id"                     => "= '$this->_id'",
            "product.category_id"                    => "= '$dmi_category_id'",
            "product.cancelled"                      => "= '0'",
            "dm.product_id"                          => "IS NOT NULL",
            "product_order_item_reception.code != '' AND product_order_item_reception.code IS NOT NULL",
            "product_order_item_reception.cancelled" => "= '0'",
            "dm_category.group_id"                   => "= '$group_id'",
        ];

        $ljoin = [
            "product_order_item" => "product_order_item_reception.order_item_id = product_order_item.order_item_id",
            "product_reference"  => "product_order_item.reference_id = product_reference.reference_id",
            "product"            => "product_reference.product_id = product.product_id",
            "dm"                 => "dm.product_id = product.product_id",
            "dm_category"        => "dm_category.category_id = dm.category_id",
        ];

        $reception = new CProductOrderItemReception();

        $lots_ids = $reception->loadColumn(
            'product_order_item_reception.order_item_reception_id',
            $where,
            $ljoin,
        );

        $where = [
            'dm_sterilisation.lot_id'                   => CSQLDataSource::prepareIn($lots_ids),
            'num_sterilisation'                         => 'IS NOT NULL',
            'sent_datetime'                             => 'IS NULL',
            'consommation_materiel.dm_sterilisation_id' => 'IS NULL',
        ];

        $ljoin = [
            'consommation_materiel' =>
                'consommation_materiel.dm_sterilisation_id = dm_sterilisation.dm_sterilisation_id',
        ];

        $sterilisations = (new CDMSterilisation())->loadList($where, null, null, null, $ljoin);

        $lots = CStoredObject::massLoadFwdRef($sterilisations, "lot_id");

        foreach ($sterilisations as $sterilisation) {
            $lots[$sterilisation->lot_id]->_ref_dm_sterilisation = $sterilisation;
        }

        CStoredObject::massLoadFwdRef($lots, "reception_id");
        /** @var CProductOrderItemReception $lot */
        foreach ($lots as $lot) {
            $lot->loadRefOrderItem()->loadReference();
        }

        return $this->_lots = $lots;
    }

    /**
     * @inheritdoc
     */
    public function loadView(): void
    {
        parent::loadView();
        $this->getConsumption("-3 MONTHS");
    }

    /**
     * Computes this product's consumption between two dates
     *
     * @param string $since        [optional] Min date
     * @param string $date_max     [optional] Max date
     * @param int    $service_id   Service ID
     * @param bool   $include_loss Include "lost" products
     *
     * @return float
     */
    public function getConsumption($since = "-1 MONTH", $date_max = null, $service_id = null, $include_loss = true)
    {
        if (CModule::getActive("dispensation")) {
            return Dispensation::getConsumption($this, $since, $date_max, $service_id, $include_loss);
        }

        return $this->_consumption = 0;
    }

    /**
     * Computes this product's consumption between two dates
     *
     * @param CProduct[] $products     Products list
     * @param string     $since        [optional] Start offset
     * @param string     $date_max     [optional] Max date
     * @param CService[] $services     Services
     * @param bool       $include_loss Include lost items
     *
     * @return float[]
     */
    public static function getConsumptionMultipleProducts(
        $products,
        $since = "-1 MONTH",
        $date_max = null,
        $services = null,
        $include_loss = true,
        $detail = false
    ) {
        if (CModule::getActive("dispensation")) {
            return Dispensation::getConsumptionMultipleProducts(
                $products,
                $since,
                $date_max,
                $services,
                $include_loss,
                $detail
            );
        }

        return [];
    }

    /**
     * Computes this product's supply between two dates
     *
     * @param string $since    [optional]
     * @param string $date_max [optional]
     *
     * @return float
     */
    public function getSupply($since = "-1 MONTH", $date_max = null)
    {
        $where = [
            "product.product_id" => "= '{$this->_id}'",
            "product_order_item_reception.date >= '" . CMbDT::date($since) . "'",
        ];

        if ($date_max) {
            $where[] = "product_order_item_reception.date < '" . CMbDT::date($date_max) . "'";
        }

        $ljoin = [
            "product_order_item" => "product_order_item.order_item_id = product_order_item_reception.order_item_id",
            "product_reference"  => "product_reference.reference_id = product_order_item.reference_id",
            "product"            => "product.product_id = product_reference.product_id",
        ];

        $sql = new CRequest();
        $sql->addTable("product_order_item_reception");
        $sql->addSelect("SUM(product_order_item_reception.quantity)");
        $sql->addLJoin($ljoin);
        $sql->addWhere($where);

        return $this->_supply = $this->_spec->ds->loadResult($sql->makeSelect());
    }

    /**
     * Get supply stats
     *
     * @param CProduct[] $products List of products
     * @param string     $since    Date start offset
     * @param string     $date_max Max date
     *
     * @return array
     */
    public static function getSupplyMultiple($products, $since = "-1 MONTH", $date_max = null)
    {
        $ds = CSQLDataSource::get("std");

        $where = [
            "product.product_id" => $ds->prepareIn(CMbArray::pluck($products, "_id")),
            "product_order_item_reception.date >= '" . CMbDT::date($since) . "'",
        ];

        if ($date_max) {
            $where[] = "product_order_item_reception.date < '" . CMbDT::date($date_max) . "'";
        }

        $ljoin = [
            "product_order_item" => "product_order_item.order_item_id = product_order_item_reception.order_item_id",
            "product_reference"  => "product_reference.reference_id = product_order_item.reference_id",
            "product"            => "product.product_id = product_reference.product_id",
        ];

        $sql = new CRequest();
        $sql->addTable("product_order_item_reception");
        $sql->addSelect("product.product_id, SUM(product_order_item_reception.quantity) AS sum");
        $sql->addLJoin($ljoin);
        $sql->addGroup("product.product_id");
        $sql->addWhere($where);

        return $ds->loadHashList($sql->makeSelect());
    }

    /**
     * Computes the weighted average price (PMP)
     *
     * @param string $since    [optional]
     * @param string $date_max [optional]
     * @param bool   $ttc      Include taxes
     *
     * @return float
     */
    public function getWAP($since = "-1 MONTH", $date_max = null, $ttc = false)
    {
        $qty = $this->getSupply($since, $date_max);

        if (!$qty) {
            return null;
        }

        $where = [
            "product.product_id" => "= '{$this->_id}'",
            "product_order_item_reception.date >= '" . CMbDT::date($since) . "'",
        ];

        if ($date_max) {
            $where[] = "product_order_item_reception.date < '" . CMbDT::date($date_max) . "'";
        }

        $ljoin = [
            "product_order_item" => "product_order_item.order_item_id = product_order_item_reception.order_item_id",
            "product_reference"  => "product_reference.reference_id = product_order_item.reference_id",
            "product"            => "product.product_id = product_reference.product_id",
        ];

        $sql = new CRequest();
        $sql->addTable("product_order_item_reception");

        $select = "SUM(product_order_item_reception.quantity * product_order_item.unit_price)";
        if ($ttc) {
            $ttc_select = "product_order_item.unit_price + (product_order_item.unit_price * (product_order_item.tva / 100))";
            $select     = "SUM(product_order_item_reception.quantity * ($ttc_select))";
        }
        $sql->addSelect($select);
        $sql->addLJoin($ljoin);
        $sql->addWhere($where);

        $total = $this->_spec->ds->loadResult($sql->makeSelect());

        return $total / $qty;
    }

    /**
     * @inheritdoc
     */
    public function store(): ?string
    {
        $this->completeField("code", 'quantity', 'unit_quantity');

        if (!$this->bdm) {
            $this->bdm = CMedicament::getBase();
        }

        if (!$this->quantity) {
            $this->quantity = 1;
        }

        if ($this->unit_quantity == 0) {
            $this->unit_quantity = '';
        }

        if ($this->code !== null && (!$this->_id || $this->fieldModified("code"))) {
            $this->code_canonical = preg_replace("/[^0-9a-z]/i", "", $this->code);
        }

        $cc = trim($this->classe_comptable, "0\n\r\t ");
        if (preg_match('/^\d+$/', $cc)) {
            $this->classe_comptable = str_pad($cc, 9, "0", STR_PAD_RIGHT);
        } else {
            $this->classe_comptable = "";
        }

        if ($this->fieldModified("cancelled", 1)) {
            $references = $this->loadRefsReferences();
            foreach ($references as $_ref) {
                $_ref->cancelled = 1;
                $_ref->store();
            }
        }

        $create_stock_quantity = $this->_create_stock_quantity;

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($create_stock_quantity) {
            $stock                      = $this->loadRefStock();
            $stock->quantity            = $create_stock_quantity;
            $stock->order_threshold_min = $stock->quantity;

            $group              = CGroups::loadCurrent();
            $stock->location_id = CProductStockLocation::getDefaultLocation($group, $this)->_id;

            if ($msg = $stock->store()) {
                CAppUI::setMsg($msg, UI_MSG_WARNING);
            }

            $this->_create_stock_quantity = null;
        }

        return null;
    }

    /**
     * Get or count items in pending orders
     *
     * @param bool $count Count instead of load
     *
     * @return CProductOrderItem[]|int[]
     */
    public function getPendingOrderItems($count = true)
    {
        $leftjoin                      = [];
        $leftjoin['product_order']     = 'product_order.order_id = product_order_item.order_id';
        $leftjoin['product_reference'] = 'product_reference.reference_id = product_order_item.reference_id';
        $leftjoin['product']           = 'product.product_id = product_reference.product_id';

        $where = [
            "product.product_id"         => "= '$this->_id'",
            "product_order.cancelled"    => '= 0', // order not cancelled
            "product_order.deleted"      => '= 0', // order not deleted
            "product_order.date_ordered" => 'IS NOT NULL', // ordered
            "product_order.received"     => "= '0'", // ordered
            "product_order_item.renewal" => "= '1'", // renewal line
        ];

        /** @var CProductOrderItem[] $list */

        $item = new CProductOrderItem();
        if ($count) {
            $list = $item->countList($where, null, $leftjoin);
        } else {
            $list = $item->loadList($where, "date_ordered ASC", null, "product_order_item.order_item_id", $leftjoin);
        }

        if (is_array($list)) {
            foreach ($list as $_id => $_item) {
                if ($_item->isReceived()) {
                    unset($list[$_id]);
                }
            }
        }

        $this->_in_order = $list;

        if ($list) {
            foreach ($this->_in_order as $_item) {
                $_item->loadOrder();
            }
        }

        return $this->_in_order;
    }

    /**
     * Fill a flow struct
     *
     * @param array      $array    The flow struct to fill
     * @param CProduct[] $products Products
     * @param int        $n        N*$unit
     * @param string     $start    Start date
     * @param string     $unit     Time unit
     * @param CService[] $services Services
     *
     * @return void
     */
    private static function fillFlow(&$array, $products, $n, $start, $unit, $services)
    {
        foreach ($services as $_key => $_service) {
            $array["out"]["total"][$_key] = [0, 0];
        }

        $d = &$array["out"];

        // Y init
        for ($i = 0; $i < 12; $i++) {
            $from     = CMbDT::date("+$i $unit", $start);
            $d[$from] = [];
        }
        $d["total"] = [
            "total" => [0, 0],
        ];

        for ($i = 0; $i < $n; $i++) {
            $from = CMbDT::date("+$i $unit", $start);
            $to   = CMbDT::date("+1 $unit", $from);

            // X init
            foreach ($services as $_key => $_service) {
                $d[$from][$_key] = [0, 0];
                if (!isset($d["total"][$_key])) {
                    $d["total"][$_key] = [0, 0];
                }
            }
            $d[$from]["total"] = [0, 0];

            $all_counts = self::getConsumptionMultipleProducts($products, $from, $to, $services, false);

            $by_product = [];
            foreach ($all_counts as $_data) {
                $by_product[$_data["product_id"]][$_data["service_id"]] = $_data["sum"];
            }

            /** @var CProduct $_product */

            foreach ($products as $_product) {
                $counts = CValue::read($by_product, $_product->_id, []);

                $coeff = 1;
                $refs  = $_product->loadRefsReferences(true);
                $ref   = reset($refs);
                if ($ref) {
                    $coeff = $ref->price;
                }

                foreach ($services as $_key => $_service) {
                    $_count = CValue::read($counts, $_key, 0);
                    $_price = $_count * $coeff;

                    $d[$from][$_key][0] += $_count;
                    $d[$from][$_key][1] += $_price;

                    $d[$from]["total"][0] += $_count;
                    $d[$from]["total"][1] += $_price;

                    @$d["total"][$_key][0] += $_count;
                    @$d["total"][$_key][1] += $_price;

                    @$d["total"]["total"][0] += $_count;
                    @$d["total"]["total"][1] += $_price;
                }
            }
        }

        $d = array_map_recursive([CProduct::class, "round2"], $d);

        // Put the total at the end
        $total = $d["total"];
        unset($d["total"]);
        $d["total"] = $total;

        $total = $d["total"]["total"];
        unset($d["total"]["total"]);
        $d["total"]["total"] = $total;

        $d = CMbArray::transpose($d);
    }

    /**
     * Round to 2 digits
     *
     * @param float $val Value to round
     *
     * @return float
     */
    public static function round2($val)
    {
        return round($val, 2);
    }

    /**
     * Build stock flow graph
     *
     * @param array      $flow     A flow struct
     * @param string     $title    Graph title
     * @param CService[] $services Services
     *
     * @return array
     */
    public static function getFlowGraph($flow, $title, $services)
    {
        $options = CFlotrGraph::merge(
            "lines",
            [
                "title"   => $title,
                "legend"  => [
                    "show" => true,
                ],
                "xaxis"   => [
                    "ticks" => [],
                ],
                "yaxis"   => [
                    "min"   => 0,
                    "title" => "Valeur (euro)" // FIXME le symbole ne euro passe pas
                ],
                "markers" => [
                    "show" => false,
                ],
            ]
        );

        $graph = [
            "data"    => [],
            "options" => $options,
        ];

        foreach ($flow["out"] as $_service_id => $_data) {
            if ($_service_id === "total") {
                continue;
            }

            $data = [
                "data"  => [],
                "label" => $services[$_service_id]->_view,
            ];

            if (empty($graph["options"]["xaxis"]["ticks"])) {
                foreach ($_data as $_date => $_values) {
                    if ($_date === "total") {
                        continue;
                    }
                    $graph["options"]["xaxis"]["ticks"][] = [count($graph["options"]["xaxis"]["ticks"]), $_date];
                }
            }

            foreach ($_data as $_date => $_values) {
                if ($_date === "total") {
                    continue;
                }
                $data["data"][] = [count($data["data"]), $_values[1]];
            }
            $graph["data"][] = $data;
        }

        return $graph;
    }

    /**
     * Compute stock balance
     *
     * @param CProduct[] $products Products
     * @param CService[] $services Services
     * @param int        $year     Year
     * @param int        $month    Month
     *
     * @return array
     */
    public static function computeBalance(array $products, array $services, $year, $month = null)
    {
        $flows = [];

        // YEAR //////////
        $year_flows = [
            "in"  => [],
            "out" => [],
        ];
        $start      = CMbDT::date(null, "$year-01-01");
        self::fillFlow($year_flows, $products, 12, $start, "MONTH", $services);

        $flows["year"] = [
            $year_flows,
            "%b",
            "Bilan annuel",
            "graph" => self::getFlowGraph($year_flows, "Bilan annuel", $services),
        ];

        // MONTH //////////
        if ($month) {
            $month_flows = [
                "in"  => [],
                "out" => [],
            ];
            $start       = CMbDT::date(null, "$year-$month-01");
            self::fillFlow(
                $month_flows,
                $products,
                CMbDT::transform("+1 MONTH -1 DAY", $start, "%d"),
                $start,
                "DAY",
                $services
            );

            $flows["month"] = [
                $month_flows,
                "%d",
                "Bilan mensuel",
                "graph" => self::getFlowGraph($month_flows, "Bilan mensuel", $services),
            ];
        }

        // Balance des stocks ////////////////
        $balance = [
            "in"   => $flows["year"][0]["in"],
            "out"  => [],
            "diff" => [],
        ];

        $start = CMbDT::date(null, "$year-01-01");
        for ($i = 0; $i < 12; $i++) {
            $from = CMbDT::date("+$i MONTH", $start);
            $to   = CMbDT::date("+1 MONTH", $from);

            $balance["in"][$from]  = [0, 0];
            $balance["out"][$from] = [0, 0];

            $supply_multiple = self::getSupplyMultiple($products, $from, $to);
            $consum_multiple = self::getConsumptionMultipleProducts($products, $from, $to, null, false);

            /** @var CProduct $_product */
            foreach ($products as $_product) {
                $supply = CValue::read($supply_multiple, $_product->_id, 0);
                //$supply = $_product->getSupply($from, $to);

                $consum = CValue::read($consum_multiple, $_product->_id, 0);
                //$consum = $_product->getConsumption($from, $to, null, false);

                $coeff = 1;
                $refs  = $_product->loadRefsReferences(true);
                $ref   = reset($refs);
                if ($ref) {
                    $coeff = $ref->price;
                }

                $balance["in"][$from][0] += $supply;
                $balance["in"][$from][1] += $supply * $coeff;

                $balance["out"][$from][0] += $consum;
                $balance["out"][$from][1] += $consum * $coeff;
            }
        }

        $cumul       = 0;
        $cumul_price = 0;
        foreach ($balance["in"] as $_date => $_balance) {
            $diff       = $balance["in"][$_date][0] - $balance["out"][$_date][0];
            $diff_price = $balance["in"][$_date][1] - $balance["out"][$_date][1];

            $balance["diff"][$_date][0] = $diff + $cumul;
            $balance["diff"][$_date][1] = $diff_price + $cumul_price;

            $cumul       += $diff;
            $cumul_price += $diff_price;
        }

        $balance = array_map_recursive([CProduct::class, "round2"], $balance);

        $options = CFlotrGraph::merge(
            "bars",
            [
                "title"  => "Rotation des stocks",
                "legend" => [
                    "show" => true,
                ],
                "xaxis"  => [
                    "ticks" => [],
                ],
                "yaxis"  => [
                    "min"   => null,
                    "title" => "Valeur (euro)" // FIXME le symbole euro ne passe pas
                ],
                "y2axis" => [
                    "min" => null,
                ],
            ]
        );

        $graph = [
            "data"    => [],
            "options" => $options,
        ];

        $params = [
            "in"   => ["label" => "Entrée", "color" => "#4DA74D"],
            "out"  => ["label" => "Sortie", "color" => "#CB4B4B"],
            "diff" => ["label" => "Cumul", "color" => "#00A8F0"],
        ];

        foreach ($balance as $_type => $_data) {
            $data = [
                "data"  => [],
                "label" => $params[$_type]["label"],
                "color" => $params[$_type]["color"],
            ];

            if ($_type === "diff") {
                $data["lines"]["show"]  = true;
                $data["bars"]["show"]   = false;
                $data["points"]["show"] = true;
                $data["mouse"]["track"] = true;
                //$data["yaxis"] = 2;
            }

            if (empty($graph["options"]["xaxis"]["ticks"])) {
                foreach ($_data as $_date => $_values) {
                    if ($_date === "total") {
                        continue;
                    }
                    $graph["options"]["xaxis"]["ticks"][] = [count($graph["options"]["xaxis"]["ticks"]), $_date];
                }
            }

            foreach ($_data as $_date => $_values) {
                if ($_date === "total") {
                    continue;
                }
                $v              = ($_type === "out" ? -$_values[1] : $_values[1]);
                $data["data"][] = [count($data["data"]), $v];
            }
            $graph["data"][] = $data;
        }

        $balance["graph"] = $graph;

        return [
            $flows,
            $balance, // required to use list()
            "flows"   => $flows,
            "balance" => $balance,
        ];
    }

    public static function makeBalanceCSV($list_products, $services, $year, $month, $day)
    {
        $from = sprintf("%04d-%02d-%02d", $year, $month ?: 1, $day ?: 1);
        if ($day) {
            $to = CMbDT::date("+1 DAY", $from);
        } elseif ($month) {
            $to = CMbDT::date("+1 MONTH", $from);
        } else {
            $to = CMbDT::date("+1 YEAR", $from);
        }

        $consumation = CProduct::getConsumptionMultipleProducts($list_products, $from, $to, $services, false, true);

        $csv     = new CCSVFile();
        $columns = [
            "Produit",
            "Code",
            "Date",
            "Quantité",
            "Valeur",
            "Service",
            "Responsable séjour",
            "Préparateur",
            "Validateur",
            "Type",
            "Commentaire",
        ];

        $csv->writeLine($columns);

        $users = [];

        $sum = 0;

        foreach ($consumation as $_consum) {
            $_prat = null;
            if ($_consum["sejour_id"]) {
                $sejour = new CSejour();
                $sejour->load($_consum["sejour_id"]);
                $_prat = $sejour->loadRefPraticien();
            }

            $product = $list_products[$_consum["product_id"]];
            if (!isset($product->_ref_reference_price)) {
                $refs = $product->loadRefsReferences();

                if (count($refs)) {
                    $ref                           = reset($refs);
                    $product->_ref_reference_price = $ref->price;
                } else {
                    $product->_ref_reference_price = 0;
                }
            }

            $prep_id = $_consum["preparateur_id"];
            $prep    = $prep_id ? (isset($users[$prep_id]) ? $users[$prep_id] : $users[$prep_id] = CMediusers::get(
                $prep_id
            )->_view) : '';

            $vali_id = $_consum["validateur_id"];
            $vali    = $vali_id ? (isset($users[$vali_id]) ? $users[$vali_id] : $users[$vali_id] = CMediusers::get(
                $vali_id
            )->_view) : '';

            $_line = [
                $product->_view,
                $product->code,
                $_consum["date_delivery"],
                $_consum["quantity"],
                $product->_ref_reference_price * $_consum["quantity"],
                $services[$_consum["service_id"]],
                $_prat,
                $prep,
                $vali,
                CAppUI::tr("CProductDelivery.type.{$_consum['type']}"),
                $_consum["comments"],
            ];

            $sum += $product->_ref_reference_price * $_consum["quantity"];

            $csv->writeLine($_line);
        }

        return $csv;
    }

    /**
     * Charge l'atc dans la pharmacie
     *
     * @return string
     */
    public function loadMedicamentATC(): ?string
    {
        return $this->_classe_atc = $this->loadMedicamentArticle()->_ref_ATC_5_code;
    }

    /**
     * Chargement de l'article
     *
     * @return CMedicamentArticle
     */
    public function loadMedicamentArticle()
    {
        return $this->_med_article = CMedicamentArticle::get(
            $this->code,
            $this->code_up_adm,
            $this->code_up_disp,
            $this->bdm
        );
    }

    /**
     * Récupère ou charge les produits en stock
     *
     * @param array  $cache_produits_stock Cache de produits en stock
     * @param int    $_code_cip            Code cip
     * @param string $bdm                  BDM
     *
     * @return array
     */
    public static function getCacheProduitsStock($cache_produits_stock, $_code_cip, $bdm)
    {
        if (!$bdm) {
            $bdm = CMedicamentProduit::getBase();
        }

        // Chargement produit stock
        if (!isset($cache_produits_stock[$_code_cip])) {
            $_product_stock       = new CProduct();
            $_product_stock->code = $_code_cip;
            $_product_stock->bdm  = $bdm;
            $_product_stock->loadMatchingObject("quantity DESC");
            if ($_product_stock->_id) {
                $_product_stock->loadRefStock();
            } else {
                $_product_stock->_ref_stock_group = new CProductStockGroup();
            }
            $cache_produits_stock[$_code_cip] = $_product_stock;
        } else {
            $_product_stock = $cache_produits_stock[$_code_cip];
        }

        return [$cache_produits_stock, $_product_stock];
    }

    /**
     * Récupère l'ensemble des emplacements des stock du group
     *
     * @param array $codes_cip Codes cip
     *
     * @return CProductStockLocation[]
     */
    public function loadRefEmplacementGroup(array$codes_cip): array
    {
        $this->completeField("product_id");
        $ljoin                                 = [];
        $ljoin["product_stock_group"]          = "product_stock_group.location_id = product_stock_location.stock_location_id";
        $ljoin["product"]                      = "product.product_id  = product_stock_group.product_id";
        $where                                 = [];
        $where["product_stock_group.group_id"] = " = '" . CProductStockGroup::getHostGroup() . "'";
        $where["product.code"]                 = CSQLDataSource::prepareIn($codes_cip);

        $emplacement  = new CProductStockLocation();
        $emplacements = $emplacement->loadList(
            $where,
            "name",
            null,
            "product_stock_location.stock_location_id",
            $ljoin
        );

        return $this->_ref_emplacements_group = $emplacements;
    }

    /**
     * Get Stock from product code
     *
     * @param array  $codes_cip  Codes
     * @param int    $service_id Service ID
     * @param string $bdm        BDM
     *
     * @return array
     */
    public static function getsFromCIS($codes_cip, $service_id, $bdm = null)
    {
        if (!$bdm) {
            $bdm = CMedicamentProduit::getBase();
        }
        if (!count($codes_cip)) {
            return [];
        }

        $stocks = [];
        $types  = ["groups" => CGroups::loadCurrent()->_id];
        foreach ($codes_cip as $_code_cip) {
            $stocks[$_code_cip] = [];
        }
        if ($service_id) {
            $types["service_id"]            = $service_id;
            $stocks["CService-$service_id"] = [];
        }

        foreach ($types as $_key_type => $id_type) {
            $is_service            = $_key_type == "service_id";
            $where                 = [];
            $where['product.bdm']  = "= '$bdm'";
            $where['product.code'] = CSQLDataSource::prepareIn($codes_cip);
            $name_table            = $is_service ? "product_stock_service" : "product_stock_group";
            if ($is_service) {
                $where["$name_table.object_class"] = "= 'CService'";
                $where["$name_table.object_id"]    = "= '$id_type'";
            } else {
                $where["$name_table.group_id"] = "= '$id_type'";
            }
            $ljoin            = [];
            $ljoin["product"] = "$name_table.product_id = product.product_id";
            $stock            = $is_service ? new CProductStockService() : new CProductStockGroup();
            if (!CAppUI::gconf("dPstock " . $stock->_class . " infinite_quantity")) {
                $where["$name_table.quantity"] = " > 0";
            }
            $stocks_type = $stock->loadList($where, "$name_table.quantity DESC", null, null, $ljoin);
            foreach ($stocks_type as $_stock) {
                $_stock->loadRefLocation();
                $stocks[$_stock->loadRefProduct()->code][$_stock->_guid] = $_stock;
                if ($is_service) {
                    $stocks["CService-$id_type"][$_stock->_guid] = $_stock;
                }
            }
        }

        return $stocks;
    }
}
