<?php

/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

/**
 * Facture lie à un sejour
 */
class CFactureEtablissement extends CFacture
{

    // DB Table key
    public $facture_id;

    // DB Fields
    public $dialyse;
    public $temporaire;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'facture_etablissement';
        $spec->key   = 'facture_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                        = parent::getProps();
        $props["patient_id"]          .= " back|facture_patient_sejour";
        $props["dialyse"]             = "bool default|0";
        $props["temporaire"]          = "bool default|0";
        $props["extourne_id"]         = "ref class|CFactureEtablissement back|extourne_etab";
        $props["group_id"]            .= " back|group_fact_etab";
        $props["category_id"]         .= " back|facture_category_etab";
        $props["coeff_id"]            .= " back|coeff_fact_etab";
        $props["assurance_maladie"]   .= " back|fact_sejour_maladie";
        $props["assurance_accident"]  .= " back|fact_sejour_accident";
        $props['praticien_id']        .= " back|praticien_facture_etab";
        $props["bill_user_printed"]   .= " back|bill_user_printed_etab";
        $props["justif_user_printed"] .= " back|justif_user_printed_etab";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = sprintf("SE%08d", $this->_id);
        $this->_view .=" / $this->num_compta";
    }

    /**
     * Redefinition du store
     *
     * @return void|string
     **/
    function store()
    {
        $this->loadRefsReglements();
        $this->loadRefsRelances();
        // Standard store
        if ($msg = parent::store()) {
            return $msg;
        }
    }

    /**
     * Redefinition du delete
     *
     * @return void|string
     **/
    function delete()
    {
        $this->_ref_reglements        = [];
        $this->_ref_relances          = [];
        $this->_count["relance_fact"] = 0;
        $this->_count["reglements"]   = 0;
        $this->loadRefsReglements();
        $this->loadRefsRelances();
        // Standard delete
        if ($msg = parent::delete()) {
            return $msg;
        }
    }

    /**
     * Chargement des reglements de la facture
     *
     * @param bool $cache cache
     *
     * @return CReglement
     **/
    function loadRefsReglements($cache = 1)
    {
        $this->_ref_reglements = $this->loadBackRefs("reglements", 'date') ?: [];

        return parent::loadRefsReglements($cache);
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
     * Fonction permettant de partir d'un numero de reference de retrouver la facture correspondante
     *
     * @param string $num_reference le numero de reference
     *
     * @return CFactureEtablissement
     **/
    function findFacture($num_reference)
    {
        $facture                = new CFactureEtablissement();
        $facture->num_reference = $num_reference;
        $facture->loadMatchingObject();

        if (!$facture->_id) {
            $echeance                = new CEcheance();
            $echeance->object_class  = "CFactureEtablissement";
            $echeance->num_reference = $num_reference;
            $echeance->loadMatchingObject();
            if ($echeance->_id) {
                $facture = $echeance->loadTargetObject();
            }
        }

        return $facture;
    }

    /**
     * Relances emises pour la facture
     *
     * @return CRelance
     **/
    function loadRefsRelances()
    {
        $this->_ref_relances = $this->loadBackRefs("relance_fact", 'date');
        $this->isRelancable();

        return $this->_ref_relances;
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
