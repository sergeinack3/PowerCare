<?php

/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

/**
 * Facture liée à une ou plusieurs consultations
 *
 */
class CFactureCabinet extends CFacture
{
    public const RESOURCE_TYPE = 'factureCabinet';

    // DB Table key
    public $facture_id;

    /**
     * @see parent::getSpec()
     **/
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'facture_cabinet';
        $spec->key   = 'facture_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                        = parent::getProps();
        $props["group_id"]            .= " back|group_fact_cab";
        $props["patient_id"]          .= " back|facture_patient_consult";
        $props["extourne_id"]         = "ref class|CFactureCabinet back|extourne_cab";
        $props["category_id"]         .= " back|facture_category_cab";
        $props["coeff_id"]            .= " back|coeff_fact_cab";
        $props["assurance_maladie"]   .= " back|fact_consult_maladie";
        $props["assurance_accident"]  .= " back|fact_consult_accident";
        $props["praticien_id"]        .= " back|praticien_facture_cabinet";
        $props["bill_user_printed"]   .= " back|bill_user_printed_cab";
        $props["justif_user_printed"] .= " back|justif_user_printed_cab";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     **/
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = sprintf("FA%08d", $this->_id);
        $this->_view .= " / $this->num_compta";
    }

    /**
     * Chargement des règlements de la facture
     *
     * @param bool $cache cache
     *
     * @return CReglement[]
     **/
    function loadRefsReglements($cache = true)
    {
        $this->_ref_reglements = $this->loadBackRefs("reglements", 'date') ?: [];

        return parent::loadRefsReglements($cache);
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->loadRefsConsultation();
        // A vérifier pour le == 0 s'il faut faire un traitement
        if ($this->statut_envoi !== 'non_envoye' && $this->_id) {
            foreach ($this->_ref_consults as $_consultation) {
                if ($this->statut_envoi == 'echec' && $_consultation->facture == 1) {
                    $_consultation->facture = 0;
                    $_consultation->store();
                } elseif ($this->statut_envoi == "envoye" && $_consultation->facture == 0) {
                    $_consultation->facture = 0;
                    $_consultation->store();
                }
            }
        }

        $this->loadRefsRelances();
        $this->loadRefsReglements();

        return parent::store();
    }

    /**
     * @see parent::delete()
     */
    function delete()
    {
        $this->_ref_reglements        = [];
        $this->_ref_relances          = [];
        $this->_count["relance_fact"] = 0;
        $this->_count["reglements"]   = 0;
        $this->loadRefsReglements();
        $this->loadRefsRelances();

        return parent::delete();
    }

    //Ne pas supprimer cette fonction!

    /**
     * loadRefPlageConsult
     *
     * @return void
     **/
    function loadRefPlageConsult()
    {
    }

    /**
     * Fonction permettant à partir d'un numéro de référence de retrouver la facture correspondante
     *
     * @param string $num_reference le numéro de référence
     *
     * @return CFactureCabinet
     **/
    function findFacture($num_reference)
    {
        $facture                = new CFactureCabinet();
        $facture->num_reference = $num_reference;
        $facture->loadMatchingObject();

        if (!$facture->_id) {
            $echeance                = new CEcheance();
            $echeance->object_class  = "CFactureCabinet";
            $echeance->num_reference = $num_reference;
            $echeance->loadMatchingObject();
            if ($echeance->_id) {
                $facture = $echeance->loadTargetObject();
            }
        }

        return $facture;
    }

    /**
     * Chargement des relances de la facture
     *
     * @return CRelance[]
     **/
    function loadRefsRelances()
    {
        $this->_ref_relances = $this->loadBackRefs("relance_fact", 'date');
        $this->isRelancable();

        return $this->_ref_relances;
    }

    /**
     * Chargement des échéances de la facture
     *
     * @return CEcheance[]
     **/
    function loadRefsEcheances()
    {
        $this->_ref_echeances = $this->loadBackRefs("echeances", "date");
        $this->loadEcheancesMontant();

        return $this->_ref_echeances;
    }

    /**
     * @see parent::fillTemplate(), used to be detected as context for the documents models
     */
    function fillTemplate(&$template)
    {
        parent::fillTemplate($template);
    }

    /**
     * Chargement des rejets de facture par les assurances
     *
     * @return CFactureRejet[]
     **/
    function loadRefsRejets()
    {
        return $this->_ref_rejets = $this->loadBackRefs("rejets");
    }

    /**
     * Chargement des liaisons de facture
     *
     * @return CFactureLiaison[]|null
     */
    function loadRefsLiaisons()
    {
        return $this->_ref_liaisons = $this->loadBackRefs("facture_liaison");
    }
}
