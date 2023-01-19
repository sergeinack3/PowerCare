<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CMbObject;

/**
 * Items des protocoles de gestes perop
 */
class CProtocoleGestePeropItem extends CMbObject
{
    /** @var int */
    public $protocole_geste_perop_item_id;

    // DB References
    /** @var int */
    public $protocole_geste_perop_id;
    /** @var int */
    public $geste_perop_precision_id;
    /** @var int */
    public $precision_valeur_id;
    /** @var int */
    public $object_id;
    /** @var string */
    public $object_class;
    /** @var int */
    public $rank;
    /** @var boolean */
    public $checked;

    /** @var CProtocoleGestePerop */
    public $_ref_protocole_geste_perop;
    /** @var CGestePerop|CAnesthPeropCategorie */
    public $_ref_context;
    /** @var CGestePeropPrecision */
    public $_ref_geste_perop_precision;
    /** @var CPrecisionValeur */
    public $_ref_precision_valeur;

    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'protocole_geste_perop_item';
        $spec->key   = 'protocole_geste_perop_item_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props                             = parent::getProps();
        $props["protocole_geste_perop_id"] = "ref class|CProtocoleGestePerop notNull back|protocole_geste_items";
        $props["geste_perop_precision_id"] = "ref class|CGestePeropPrecision back|protocole_geste_items";
        $props["precision_valeur_id"]      = "ref class|CPrecisionValeur back|protocole_geste_items";
        $props["object_class"]             = "enum list|CGestePerop|CAnesthPeropCategorie notNull";
        $props["object_id"]                = "ref meta|object_class back|protocole_geste_perop_items";
        $props["rank"]                     = "num min|1 show|0";
        $props["checked"]                  = "bool default|0";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $context     = $this->loadRefContext();
        $this->_view = $context->_view;
    }

    /**
     * Load the precision of the gesture perop
     *
     * @return CGestePeropPrecision
     * @throws Exception
     */
    public function loadRefGestePeropPrecision(): CGestePeropPrecision
    {
        return $this->_ref_geste_perop_precision = $this->loadFwdRef("geste_perop_precision_id", true);
    }

    /**
     * Load the precision value
     *
     * @return CPrecisionValeur
     * @throws Exception
     */
    public function loadRefPrecisionValeur(): CPrecisionValeur
    {
        return $this->_ref_precision_valeur = $this->loadFwdRef("precision_valeur_id", true);
    }

    /**
     * Load the CProtocoleGestePerop object
     */
    public function loadRefProtocoleGestePerop(): CProtocoleGestePerop
    {
        return $this->_ref_protocole_geste_perop = $this->loadFwdRef("protocole_geste_perop_id", true);
    }

    /**
     * Load main context CGestePerop or CAnesthPeropCategorie
     *
     * @return CGestePerop|CAnesthPeropCategorie
     *
     */
    public function loadRefContext()
    {
        /** @var CGestePerop|CAnesthPeropCategorie $context */
        $context = new $this->object_class();

        if (!$this->object_id) {
            return $this->_ref_context = $context;
        }

        return $this->_ref_context = $context->load($this->object_id);
    }
}
