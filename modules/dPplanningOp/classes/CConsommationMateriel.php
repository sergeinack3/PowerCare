<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Dmi\CDMSterilisation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Stock\CProductOrderItemReception;

/**
 * Consommation des matériels de bloc
 */
class CConsommationMateriel extends CMbObject
{
    /** @var int */
    public $consommation_materiel_id;

    // DB fields
    /** @var int */
    public $materiel_operatoire_id;

    /** @var string */
    public $datetime;

    /** @var int */
    public $user_id;

    /** @var int */
    public $qte_consommee;

    /** @var int */
    public $lot_id;

    /** @var int */
    public $dm_sterilisation_id;

    // References
    /** @var CMaterielOperatoire */
    public $_ref_materiel_operatoire;

    /** @var CMediusers */
    public $_ref_user;

    /** @var CProductOrderItemReception */
    public $_ref_lot;

    /** @var CDMSterilisation */
    public $_ref_dm_sterilisation;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "consommation_materiel";
        $spec->key   = "consommation_materiel_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                           = parent::getProps();
        $props["materiel_operatoire_id"] = "ref class|CMaterielOperatoire notNull back|consommation";
        $props["datetime"]               = "dateTime notNull";
        $props["user_id"]                = "ref class|CMediusers notNull back|consommations_materiel";
        $props["qte_consommee"]          = "num default|1";
        $props["lot_id"]                 = "ref class|CProductOrderItemReception back|consommations";
        $props['dm_sterilisation_id']    = 'ref class|CDMSterilisation back|consommation';
        return $props;
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        $this->completeField("materiel_operatoire_id", "qte_consommee");

        $new = !$this->_id;

        if (!$this->_id || $this->fieldModified("qte_consommee")) {
            $this->user_id  = CMediusers::get()->_id;
            $this->datetime = "current";
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        // Création d'une déstérilisation
        $msg = $new ? $this->createDesterilisation() : null;

        return $msg;
    }

    /**
     * Charge le matériel opératoire
     *
     * @return CMaterielOperatoire
     */
    public function loadRefMaterielOperatoire(): CMaterielOperatoire
    {
        return $this->_ref_materiel_operatoire = $this->loadFwdRef("materiel_operatoire_id", true);
    }

    /**
     * Charge l'utilisateur associé à la consommation
     *
     * @return CMediusers
     */
    public function loadRefUser(): CMediusers
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", true);
    }

    /**
     * Charge le lot associé à la consommation
     *
     * @return CProductOrderItemReception
     */
    public function loadRefLot(): CProductOrderItemReception
    {
        return $this->_ref_lot = $this->loadFwdRef("lot_id", true);
    }

    public function loadRefDMSterilisation(): CDMSterilisation
    {
        return $this->_ref_dm_sterilisation = $this->loadFwdRef('dm_sterilisation_id', true);
    }

    /**
     * Création d'une déstérilisation seulement pour les produits implantables
     */
    public function createDesterilisation(): ?string
    {
        $sterilisation = $this->loadRefDMSterilisation();

        if ($sterilisation->_id) {
            $sterilisation->date_desterilisation = "current";
            return $sterilisation->store();
        }

        return null;
    }
}
