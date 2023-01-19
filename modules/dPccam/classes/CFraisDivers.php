<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureItem;

/**
 * Frais divers
 */
class CFraisDivers extends CActe
{
    public $frais_divers_id;

    // DB fields
    public $type_id;
    public $coefficient;
    public $quantite;

    public $_montant;

    public $_ref_type;

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props["object_id"]    .= " back|frais_divers";
        $props["executant_id"] .= " back|frais_divers";
        $props["type_id"]      = "ref notNull class|CFraisDiversType autocomplete|code back|frais_divers";
        $props["coefficient"]  = "float notNull default|1";
        $props["quantite"]     = "num min|0";

        $props["_montant"] = "currency";

        return $props;
    }

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "frais_divers";
        $spec->key   = "frais_divers_id";

        return $spec;
    }

    /**
     * Chargement du type de frais
     *
     * @return CFraisDiversType
     */
    public function loadRefType(): CFraisDiversType
    {
        return $this->_ref_type = $this->loadFwdRef("type_id", true);
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->loadRefType();

        $this->_montant = $this->montant_base;

        // Vue codée
        $this->_shortview = $this->quantite > 1 ? "{$this->quantite}x " : "";
        $this->_shortview .= $this->_ref_type->_view;

        if ($this->coefficient != 1) {
            $this->_shortview .= $this->coefficient;
        }

        $this->_view = "Frais divers $this->_shortview";
        if ($this->object_class && $this->object_id) {
            $this->_view .= " de $this->object_class-$this->object_id";
        }
    }

    /**
     * Création d'un item de facture pour un frais divers
     *
     * @param CFacture $facture la facture
     *
     * @return string|null
     */
    public function creationItemsFacture(CFacture $facture): ?string
    {
        $this->loadRefType();
        $ligne                      = new CFactureItem();
        $ligne->libelle             = $this->_ref_type->libelle;
        $ligne->code                = $this->_ref_type->code;
        $ligne->type                = $this->_class;
        $ligne->object_id           = $facture->_id;
        $ligne->object_class        = $facture->_class;
        $ligne->date                = CMbDT::date($this->execution);
        $ligne->montant_base        = $this->montant_base;
        $ligne->montant_depassement = $this->montant_depassement;
        $ligne->quantite            = $this->quantite;
        $ligne->coeff               = $this->coefficient;
        $msg                        = $ligne->store();

        return $msg;
    }
}
