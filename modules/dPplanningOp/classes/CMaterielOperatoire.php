<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Bcb\CBcbProduit;
use Ox\Mediboard\Besco\CBescoArticle;
use Ox\Mediboard\Dmi\CDM;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Stock\CProductOrderItemReception;
use Ox\Mediboard\Vidal\CVidalArticle;

/**
 * Gestion du matériel opératoire
 */
class CMaterielOperatoire extends CMbObject
{
    /** @var int Primary key */
    public $materiel_operatoire_id;

    // DB fields
    public $protocole_operatoire_id;
    public $operation_id;
    public $dm_id;
    public $code_cip;
    public $bdm;
    public $qte_prevue;
    public $status;
    public $status_user_id;
    public $status_datetime;
    /** @var bool */
    public $completude_panier;

    // References
    /** @var CProtocoleOperatoire */
    public $_ref_protocole_operatoire;

    /** @var COperation */
    public $_ref_operation;

    /** @var CDM */
    public $_ref_dm;

    /** @var CMedicamentArticle */
    public $_ref_produit;

    /** @var CConsommationMateriel[] */
    public $_ref_consommations;

    /** @var CProductOrderItemReception[] */
    public $_ref_available_lots;

    // Form fields
    public $_code_produit;
    public $_related_product;
    public $_qte_consommee;

    public static $_skip_invalidation;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec             = parent::getSpec();
        $spec->table      = "materiel_operatoire";
        $spec->key        = "materiel_operatoire_id";
        $spec->xor["elt"] = ["dm_id", "code_cip"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                            = parent::getProps();
        $props["protocole_operatoire_id"] = "ref class|CProtocoleOperatoire back|materiels_operatoires";
        $props["operation_id"]            = "ref class|COperation back|materiels_operatoires";
        $props["dm_id"]                   = "ref class|CDM back|materiels_operatoires";
        $props["code_cip"]                = "numchar length|7";
        $props["bdm"]                     = "str";
        $props["qte_prevue"]              = "float pos";
        $props["status"]                  = "enum list|ok|ko";
        $props["status_user_id"]          = "ref class|CMediusers back|materiel_op_status";
        $props["status_datetime"]         = "dateTime";
        $props["completude_panier"]       = "bool notNull default|1";
        $props["_related_product"]        = "str";

        return $props;
    }

    /**
     * Charge le protocole opératoire associé
     *
     * @return CProtocoleOperatoire|CStoredObject
     * @throws Exception
     */
    public function loadRefProtocoleOperatoire(): CProtocoleOperatoire
    {
        return $this->_ref_protocole_operatoire = $this->loadFwdRef("protocole_operatoire_id", true);
    }

    /**
     * Charge l'intervention associée
     * @return COperation|CStoredObject
     * @throws Exception
     */
    public function loadRefOperation(): COperation
    {
        return $this->_ref_operation = $this->loadFwdRef("operation_id", true);
    }

    /**
     * Charge le DM associé
     *
     * @return CDM|CStoredObject
     * @throws Exception
     */
    public function loadRefDM(): ?CDM
    {
        return $this->_ref_dm = $this->loadFwdRef("dm_id", true);
    }

    /**
     * Charge le produit de la banque de médicament associé
     *
     * @return CBcbProduit|CBescoArticle|CVidalArticle
     */
    public function loadRefProduit()
    {
        return $this->_ref_produit = $this->code_cip && $this->bdm ?
            CMedicamentArticle::get($this->code_cip, null, null, $this->bdm) : null;
    }

    /**
     * Charge le DM ou produit associé
     * @throws Exception
     */
    public function loadRelatedProduct()
    {
        $this->loadRefDM();
        $this->loadRefProduit();

        if ($this->_ref_dm && $this->_ref_dm->_id) {
            $this->_view         = $this->_ref_dm->nom;
            $this->_code_produit = $this->_ref_dm->loadRefProduct()->code;

            return $this->_ref_dm;
        } elseif ($this->_ref_produit) {
            $this->_view         = $this->_ref_produit->ucd_view;
            $this->_code_produit = $this->_ref_produit->code_cip;

            return $this->_ref_produit;
        }

        return null;
    }

    /**
     * Charge la consommation associée au matériel
     *
     * @return CConsommationMateriel[]
     * @throws Exception
     */
    public function loadRefsConsommations()
    {
        $this->_ref_consommations = $this->loadBackRefs("consommation", "datetime DESC");

        $this->_qte_consommee = array_sum(CMbArray::pluck($this->_ref_consommations, "qte_consommee"));

        return $this->_ref_consommations;
    }

    /**
     * @inheritDoc
     */
    public function store()
    {
        $this->completeField("protocole_operatoire_id", "status");

        if ($this->fieldModified("status")) {
            $this->status_user_id  = $this->status ? CMediusers::get()->_id : "";
            $this->status_datetime = $this->status ? "current" : "";
        }
        if ($this->dm_id && $this->loadRefDM()->type_usage === CDM::DM_STERILISABLE) {
            $this->completude_panier = 0;
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        // On force l'invalidation car un élément du protocole vient d'être modifié
        if (!$this->operation_id && !self::$_skip_invalidation) {
            $protocole_op                            = $this->loadRefProtocoleOperatoire();
            $protocole_op->_force_invalide_signature = true;
            $protocole_op->store();
        }

        return null;
    }

    /**
     * Chargement des matériels opératoires en fonction d'un contexte
     *
     * @param COperation|CProtocoleOperatoire $context               Contexte (intervention ou protocole)
     * @param bool                            $with_refs             Inclure le chargement des produits associés
     * @param bool                            $only_missing          Seulement les matériels manquants
     * @param bool                            $separate_sterilisable Séparer les matériels opératoires DM stérilisables
     *
     * @return CMaterielOperatoire[]
     * @throws Exception
     */
    public static function getList(
        CStoredObject &$context,
        bool $with_refs = false,
        bool $only_missing = false,
        bool $separate_sterilisable = false
    ) {
        $where = [];

        if ($context instanceof CProtocoleOperatoire) {
            $where["operation_id"] = "IS NULL";
        }

        $context->_refs_materiels_operatoires = $context->loadBackRefs(
            "materiels_operatoires",
            null,
            null,
            null,
            null,
            null,
            null,
            $where
        );

        if ($only_missing) {
            foreach ($context->_refs_materiels_operatoires as $_materiel_operatoire) {
                if ($_materiel_operatoire->status !== "ko") {
                    unset($context->_refs_materiels_operatoires[$_materiel_operatoire->_id]);
                }
            }
        }

        if (!$with_refs) {
            return $context->_refs_materiels_operatoires;
        }

        CStoredObject::massLoadFwdRef($context->_refs_materiels_operatoires, "dm_id");

        foreach ($context->_refs_materiels_operatoires as $_materiel_op) {
            $_materiel_op->loadRelatedProduct();
        }

        CMbArray::pluckSort($context->_refs_materiels_operatoires, SORT_ASC, "_view");

        foreach ($context->_refs_materiels_operatoires as $_materiel_op) {
            if ($_materiel_op->dm_id) {
                $context->_refs_materiels_operatoires_dm[$_materiel_op->_id] = $_materiel_op;
            } else {
                $context->_refs_materiels_operatoires_produit[$_materiel_op->_id] = $_materiel_op;
            }
        }

        foreach ($context->_refs_materiels_operatoires_dm as $_materiel_op) {
            $_materiel_op->_ref_dm->loadRefLocation();

            if ($separate_sterilisable && $_materiel_op->_ref_dm->type_usage === "sterilisable") {
                $context->_refs_materiels_operatoires_dm_sterilisables[$_materiel_op->_id] = $_materiel_op;
                unset($context->_refs_materiels_operatoires_dm[$_materiel_op->_id]);
            }
        }

        CMbArray::pluckSort($context->_refs_materiels_operatoires_dm, SORT_ASC, "_ref_dm", "_ref_location", "position");

        return $context->_refs_materiels_operatoires;
    }
}
