<?php

/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\Module\CModule;
use Ox\Core\CRequest;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CFraisDivers;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Fse\CFSE;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Facture générique
 */
class CFacture extends CMbObject implements IPatientRelated
{
    public const FIELDSET_DUE  = 'due';
    public const FIELDSET_STEP = 'step';

    public const STATUS_EXTOURNEE   = 'extournee';
    public const STATUS_HATCHING    = 'hatching';
    public const STATUS_REJETS      = 'rejets';
    public const STATUS_NON_CLOTURE = 'non_cloture';
    public const STATUS_REGLEE      = 'reglee';
    public const STATUS_COTEE       = 'cotee';
    public const STATUS_NONCOTEE    = 'noncotee';
    public const STATUS_NONE        = 'none';

    public const STATUSES = [
        self::STATUS_NON_CLOTURE,
        self::STATUS_REGLEE,
        self::STATUS_COTEE,
        self::STATUS_NONCOTEE,
        self::STATUS_REJETS,
        self::STATUS_EXTOURNEE,
        self::STATUS_HATCHING,
        self::STATUS_NONE,
    ];

    private const KEY_CACHE_NUM_COMPTA = 'num_compta';

    /** @var bool  */
    public static $load_consults_light = false;

    // DB Fields
    public $group_id;
    public $patient_id;
    public $praticien_id;
    public $coeff_id;
    public $category_id;
    public $numero;
    public $num_compta;
    public $remise;
    public $ouverture;
    public $cloture;
    public $montant_total;
    public $du_patient;
    public $du_tiers;
    public $du_tva;
    public $taux_tva;
    public $type_facture;
    public $patient_date_reglement;
    public $tiers_date_reglement;
    public $npq;
    public $cession_creance;
    public $assurance_maladie;
    public $assurance_accident;
    public $rques_assurance_maladie;
    public $rques_assurance_accident;
    public $send_assur_base;
    public $send_assur_compl;
    public $ref_accident;
    public $statut_pro;
    public $num_reference;
    public $definitive;
    public $date_cas;
    public $request_date;
    public $remarque;
    public $bill_date_printed;
    public $bill_user_printed;
    public $justif_date_printed;
    public $justif_user_printed;
    public $msg_error_xml;
    public $rcc;
    public $no_relance;

    // Statuts
    public $annule;
    public $extourne;
    public $extourne_id;
    public $regle;
    public $statut_envoi;

    static $statuts_maladie = ["sans_emploi", "etudiant", "non_travailleur", "independant", "retraite", "enfant"];
    static $_file_name      = "InvoiceRequest.xml";

    // Form fields
    public $_consult_id;
    public $_consult;
    public $_sejour_id;
    public $_evt_id;
    public $_total;
    public $_duplicate;
    public $_echeance;
    public $_assurance_patient;
    public $_assurance_patient_view;
    public $_type_rbt;

    /** @var array Liste des statuts de la facture */
    public $_statut;
    /** @var string Liste des statuts de la facture, en chaine de caractères */
    public $_statut_view;
    /** @var string Statut principal de la facutre */
    public $_main_statut;

    public $_coeff      = 1;
    public $_montant_sans_remise;
    public $_montant_avec_remise;
    public $_secteur1   = 0.0;
    public $_secteur2   = 0.0;
    public $_secteur3   = 0.0;
    public $_montant_dh = 0.0;
    public $_no_round   = false;

    public $_du_restant;
    public $_du_restant_patient;
    public $_du_restant_tiers;
    public $_reglements_total;
    public $_reglements_total_patient;
    public $_reglements_total_tiers;
    public $_montant_factures        = [];
    public $_num_bvr                 = [];
    public $_montant_factures_caisse = [];
    public $_is_relancable;
    public $_montant_retrocession;
    public $_retrocessions           = [];
    public $_montant_avoir;
    public $_montant_echeance;
    public $_montant_total_echeance;
    public $_interest_echeance;
    public $_is_ambu                 = 0;
    public $_is_urg                  = 0;

    public $_bill_prat_id;
    public $_host_config;//Configuration de la fonction ou de l'établissement
    public $_creating_lignes;

    // Object References
    /** @var CCorrespondantPatient */
    public $_ref_assurance_accident;
    /** @var CCorrespondantPatient */
    public $_ref_assurance_maladie;
    /** @var CMediusers */
    public $_ref_chir;
    /** @var CConsultation */
    public $_ref_last_consult;
    /** @var CConsultation */
    public $_ref_first_consult;
    /** @var CPatient */
    public $_ref_patient;
    /** @var CMediusers */
    public $_ref_praticien;
    /** @var CSejour */
    public $_ref_first_sejour;
    /** @var CSejour */
    public $_ref_last_sejour;
    /** @var CRelance */
    public $_ref_last_relance;
    /** @var CFactureCoeff */
    public $_ref_coeff;

    // Object Collections
    /** @var CConsultation[] */
    public $_ref_consults = [];
    /** @var CFactureItem[] */
    public $_ref_items = [];
    /** @var CReglement[] */
    public $_ref_reglements = [];
    /** @var CReglement[] */
    public $_ref_reglements_patient = [];
    /** @var CReglement[] */
    public $_ref_reglements_tiers = [];
    /** @var array */
    public $_new_reglement_patient = ["emetteur" => "patient", "mode" => "", "montant" => null];
    /** @var array */
    public $_new_reglement_tiers = ["emetteur" => "tiers", "mode" => "virement", "montant" => null];
    /** @var CSejour[] */
    public $_ref_sejours = [];
    /** @var CEvenementPatient[] */
    public $_ref_evts = [];
    /** @var CEvenementPatient */
    public $_ref_last_evt;
    /** @var CEvenementPatient */
    public $_ref_first_evt;
    /** @var CRelance[] */
    public $_ref_relances = [];
    /** @var CActeNGAP[] */
    public $_ref_actes_ngap = [];
    /** @var CActeCCAM[] */
    public $_ref_actes_ccam = [];
    /** @var CFraisDivers[] */
    public $_ref_actes_divers = [];
    /** @var CDebiteur[] */
    public $_ref_debiteurs = [];
    /** @var CEcheance[] */
    public $_ref_echeances = [];
    /** @var CGroups[] */
    public $_ref_group;
    /** @var CFactureCoeff[] */
    public $_ref_coefficients = [];
    /** @var CFactureCategory */
    public $_ref_category;
    /** @var CFactureRejet[] */
    public $_ref_rejets = [];
    /** @var CFSE */
    public $_current_fse;
    /** @var int */
    public $_current_fse_number;
    /** @var  CFacture */
    public $_ref_extourne;
    /** @var  CFacture */
    public $_ref_new_facture;
    /** @var  CFactureLiaison */
    public $_ref_liaisons;
    /** @var  CFactureAvoir[] */
    public $_ref_avoirs = [];

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $show_field_ch          = 0;
        $props["group_id"]      = "ref notNull class|CGroups";
        $props["patient_id"]    = "ref class|CPatient purgeable seekable notNull show|1";
        $props["praticien_id"]  = "ref class|CMediusers";
        $props["coeff_id"]      = "ref class|CFactureCoeff";
        $props["category_id"]   = "ref class|CFactureCategory autocomplete|libelle";
        $props["numero"]        = "num notNull min|1 default|1";
        $props["num_compta"]    = "num min|1";
        $props["ouverture"]     = "dateTime notNull fieldset|" . self::FIELDSET_STEP;
        $props["cloture"]       = "date";
        $props["montant_total"] = "currency default|0 decimals|2";
        $props["du_patient"]    = "currency notNull default|0 decimals|2 fieldset|" . self::FIELDSET_DUE;
        $props["du_tiers"]      = "currency notNull default|0 decimals|2";
        $props["du_tva"]        = "currency default|0 decimals|2 show|0";
        $props["taux_tva"]      = "float default|0 show|0";
        $props["remise"]        = "currency notNull default|0 decimals|2 show|$show_field_ch";

        $props["type_facture"]             = "enum notNull list|maladie|accident|esthetique default|maladie show|$show_field_ch";
        $props["patient_date_reglement"]   = "date";
        $props["tiers_date_reglement"]     = "date";
        $props["npq"]                      = "bool default|0 show|$show_field_ch";
        $props["cession_creance"]          = "bool default|0 show|$show_field_ch";
        $props["assurance_maladie"]        = "ref class|CCorrespondantPatient show|$show_field_ch";
        $props["assurance_accident"]       = "ref class|CCorrespondantPatient show|$show_field_ch";
        $props["rques_assurance_maladie"]  = "text helped show|$show_field_ch";
        $props["rques_assurance_accident"] = "text helped show|$show_field_ch";
        $props["send_assur_base"]          = "bool default|0 show|$show_field_ch";
        $props["send_assur_compl"]         = "bool default|0 show|$show_field_ch";
        $props["ref_accident"]             = "text show|$show_field_ch";
        $props["statut_pro"]               = "enum list|chomeur|etudiant|non_travailleur|independant|invalide|militaire|retraite|salarie_fr|salarie_sw|sans_emploi|enfant|enceinte|prive show|$show_field_ch";
        $props["num_reference"]            = "str minLength|16 maxLength|27 show|$show_field_ch";
        $props["definitive"]               = "bool default|0 show|$show_field_ch";
        $props["date_cas"]                 = "dateTime show|$show_field_ch";
        $props["request_date"]             = "dateTime show|$show_field_ch";
        $props["remarque"]                 = "text show|$show_field_ch";
        $props["bill_date_printed"]        = "dateTime show|$show_field_ch";
        $props["bill_user_printed"]        = "ref class|CMediusers show|$show_field_ch";
        $props["justif_date_printed"]      = "dateTime show|$show_field_ch";
        $props["justif_user_printed"]      = "ref class|CMediusers show|$show_field_ch";
        $props["msg_error_xml"]            = "text show|$show_field_ch";
        $props["rcc"]                      = "str maxLength|25 show|$show_field_ch";
        $props["no_relance"]               = "bool default|0 show|$show_field_ch";

        $props["annule"]       = "bool default|0";
        $props["extourne"]     = "bool default|0 show|$show_field_ch";
        $props["regle"]        = "bool default|0";
        $props["statut_envoi"] = "enum notNull list|echec|non_envoye|envoye default|non_envoye show|$show_field_ch";

        $props["_du_restant"]               = "currency";
        $props["_du_restant_patient"]       = "currency fieldset|" . self::FIELDSET_DUE;
        $props["_du_restant_tiers"]         = "currency";
        $props["_reglements_total"]         = "currency";
        $props["_reglements_total_patient"] = "currency";
        $props["_reglements_total_tiers"]   = "currency";
        $props["_montant_sans_remise"]      = "currency";
        $props["_montant_avec_remise"]      = "currency";
        $props["_montant_avoir"]            = "currency";
        $props["_montant_echeance"]         = "currency";
        $props["_montant_total_echeance"]   = "currency";
        $props["_interest_echeance"]        = "pct";
        $props["_secteur1"]                 = "currency";
        $props["_secteur2"]                 = "currency";
        $props["_secteur3"]                 = "currency";
        $props["_montant_dh"]               = "currency";
        $props["_total"]                    = "currency";
        $props["_montant_retrocession"]     = "currency";
        $props["_type_rbt"]                 = "str";
        $props["_statut"]                   = "enum";
        $props["_statut_view"]              = "str";
        $props["_main_statut"]              = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function loadAllDocs($params = [])
    {
        $this->mapDocs($this, $params);
    }

    /**
     * Récupération de la liste des relances de la facture
     *
     * @return CRelance[]
     */
    function loadRefsRelances()
    {
        return [];
    }

    /**
     * Chargement des rejets de facture par les assurances
     *
     * @return CFactureRejet[]
     **/
    function loadRefsRejets()
    {
        return $this->_ref_rejets;
    }

    /**
     * Duplication de la facture
     *
     * @return void|string
     **/
    function duplicate()
    {
        /** @var CFacture $new */
        $new = new static;
        $new->cloneFrom($this);

        $this->annule              = 0;
        $this->definitive          = 0;
        $this->extourne            = 0;
        $this->num_compta          = null;
        $this->regle               = 0;
        $this->statut_envoi        = "non_envoye";
        $this->bill_date_printed   = null;
        $this->bill_user_printed   = null;
        $this->justif_date_printed = null;
        $this->justif_user_printed = null;

        if ($msg = $new->store()) {
            return $msg;
        }

        $this->loadRefsLiaisons();
        foreach ($this->_ref_liaisons as $_liaison) {
            $new_liaison = new CFactureLiaison();
            $new_liaison->duplicate($_liaison, $new->_id);
            $new_liaison->store();
        }
    }

    /**
     * Redéfinition du store
     *
     * @return void|string
     **/
    function store()
    {
        $this->completeField(
            "num_compta",
            "numero",
            "group_id",
            "praticien_id",
            "cloture",
            "du_patient",
            "du_tiers",
            "patient_date_reglement",
            "tiers_date_reglement"
        );
        if (!$this->group_id) {
            $this->group_id = CGroups::loadCurrent()->_id;
        }

        if ($this->_id && $this->_duplicate) {
            $this->_duplicate = null;
            if ($msg = $this->duplicate()) {
                return $msg;
            }
            $this->annule     = 1;
            $this->definitive = 1;
            $this->extourne   = 1;
        }

        if (!$this->cloture && $this->fieldModified("cloture") && count($this->_ref_reglements)) {
            return "Vous ne pouvez pas décloturer une facture ayant des règlements";
        }

        if (!$this->cloture && $this->fieldModified("cloture") && count($this->_ref_relances)) {
            return "Vous ne pouvez pas décloturer une facture ayant des relances";
        }

        $create_lignes = false;
        if (CAppUI::gconf("dPfacturation " . $this->_class . " use_auto_cloture")) {
            if (!$this->_id || (!$this->annule && $this->fieldModified("annule", "0"))) {
                $this->cloture = CMbDT::date();
                $create_lignes = true;
            } elseif ($this->annule && $this->fieldModified("annule")) {
                //Dans le cas de la cloture automatique, il faut supprimer les items sur les factures temporairement extournées
                $this->deleteLignesFacture();
            }
        }

        //Si on cloture la facture création des lignes de la facture
        //Si on décloture on les supprime
        if ($this->cloture && $this->fieldModified("cloture") && !$this->_old->cloture && !$this->countBackRefs("items")) {
            $create_lignes = true;
        } elseif (!$this->cloture && $this->fieldModified("cloture")) {
            //Suppression des tous les items de la facture
            $this->deleteLignesFacture();
            $this->statut_envoi = "non_envoye";
        }

        // Etat des règlement à propager sur les consultations
        if ($this->fieldModified("patient_date_reglement") || $this->fieldModified("tiers_date_reglement")) {
            if ($this->isRelancable() && $this->_ref_last_relance->_id) {
                $this->_ref_last_relance->etat = $this->patient_date_reglement ? "regle" : "emise";
                $this->_ref_last_relance->store();
            }

            $this->regle = $this->cloture
                && (!$this->du_patient || $this->du_patient == 0 || $this->patient_date_reglement)
                && (!$this->du_tiers || $this->du_tiers == 0 || $this->tiers_date_reglement);
        }

        $_object_id    = null;
        $_object_class = null;
        //Lors de la validation de la cotation d'une consultation
        if ($this->_consult_id || $this->_consult) {
            $consult = $this->_consult;
            if (!$consult) {
                $consult = new CConsultation();
                $consult->load($this->_consult_id);
                $consult->loadRefPlageConsult();
            } else {
                $consult->completeField("patient_id", "sejour_id");
            }

            // Si la facture existe déjà on la met à jour
            $where               = [];
            $ljoin               = [];
            $plage               = $consult->_ref_plageconsult;
            $where["patient_id"] = "= '$consult->patient_id'";
            $where["numero"]     = " = '" . ($this->numero ? $this->numero : 1) . "'";
            $table                    = $consult->sejour_id ? "facture_etablissement" : "facture_cabinet";
            $ljoin["facture_liaison"] = "facture_liaison.facture_id = $table.facture_id";
            if ($consult->sejour_id && CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab")) {
                $where["facture_liaison.object_id"]    = " = '$consult->sejour_id'";
                $where["facture_liaison.object_class"] = " = 'CSejour'";
            } else {
                $where["facture_liaison.object_id"]    = " = '$this->_consult_id'";
                $where["facture_liaison.object_class"] = " = 'CConsultation'";
            }
            $where["facture_liaison.facture_class"] = " = '$this->_class'";
            $factures = $this->loadList($where, null, null, null, $ljoin);

            $_liaison_exist = $this->_id;
            if (!$this->_id || !isset($factures[$this->_id])) {
                $_du_patient       = $this->du_patient;
                $_du_tiers         = $this->du_tiers;
                $category_id       = $this->category_id;
                $_liaison_exist    = $this->loadObject($where, "facture_id DESC", "facture_id", $ljoin);
                $this->du_patient  = $_du_patient;
                $this->du_tiers    = $_du_tiers;
                $this->category_id = $category_id;
            }
            //Si la facture existe déjà
            if ($_liaison_exist) {
                $ligne                = new CFactureLiaison();
                $ligne->facture_id    = $this->_id;
                $ligne->facture_class = $this->_class;
                $ligne->object_id     = $this->_consult_id;
                $ligne->object_class  = 'CConsultation';
                if (!$ligne->loadMatchingObject("facture_liaison_id ASC")) {
                    $ligne->store();
                }
                $this->enableFacture();
            } else {
                // Sinon on la crée
                $this->ouverture    = CMbDT::dateTime();
                $this->patient_id   = $consult->patient_id;
                $this->praticien_id = ($plage->pour_compte_id ? $plage->pour_compte_id : $plage->chir_id);
                $this->type_facture = $consult->pec_at == 'arret' ? "accident" : "maladie";
                if (!$this->_id && CAppUI::gconf("dPfacturation $this->_class use_auto_cloture")) {
                    $this->cloture = CMbDT::date();
                    $create_lignes = true;
                }
            }
            $this->_creating_lignes = $create_lignes = $create_lignes ||
                (CAppUI::gconf("dPfacturation $this->_class use_auto_cloture") && $this->_consult->fieldModified(
                        "valide"
                    ) && $this->_consult->valide && !$this->_creating_lignes);
            $_object_id             = $this->_consult_id;
            $_object_class          = "CConsultation";
        }

        //Lors de la création d'une facture de séjour
        if ($this->_sejour_id) {
            $_object_id    = $this->_sejour_id;
            $_object_class = "CSejour";
        } elseif ($this->_evt_id) {
            $_object_id    = $this->_evt_id;
            $_object_class = "CEvenementPatient";
        }

        $this->completeField("assurance_maladie", "assurance_accident", "type_facture");
        if ($this->type_facture == "maladie" && $this->assurance_accident) {
            $this->assurance_accident = "";
        }
        if ($this->type_facture == "accident" && $this->assurance_maladie) {
            $this->assurance_maladie = "";
        }

        $this->loadRefAssurance();
        if ($this->fieldModified("assurance_maladie") && $this->assurance_maladie && $this->_ref_assurance_maladie->type_pec) {
            $this->send_assur_base = $this->_ref_assurance_maladie->type_pec == 'TG' ? 1 : 0;
        }
        if ($this->fieldModified("assurance_accident") && $this->assurance_accident && $this->_ref_assurance_accident->type_pec) {
            $this->send_assur_compl = $this->_ref_assurance_accident->type_pec == 'TG' ? 1 : 0;
        }

        $this->checkNumCompta();

        // Standard store
        if ($msg = parent::store()) {
            return $msg;
        }

        if ($_object_id) {
            $ligne                = new CFactureLiaison();
            $ligne->facture_id    = $this->_id;
            $ligne->facture_class = $this->_class;
            $ligne->object_id     = $_object_id;
            $ligne->object_class  = $_object_class;
            if (!$ligne->loadMatchingObject()) {
                $ligne->store();
            }
        }

        if ($create_lignes) {
            $this->creationLignesFacture();
            $this->montant_total = $this->_montant_avec_remise;
            $this->store();
        }
    }

    /**
     * Redéfinition du delete
     *
     * @return void|string
     **/
    function delete()
    {
        if (count($this->_ref_reglements)) {
            return "Vous ne pouvez pas supprimer une facture ayant des règlements";
        }

        if (count($this->_ref_relances)) {
            return "Vous ne pouvez pas supprimer une facture ayant des relances";
        }

        if (CModule::getActive("dPfacturation")) {
            $where                 = [];
            $where["object_id"]    = " = '$this->_id'";
            $where["object_class"] = " = '$this->_class'";
            $item                  = new CFactureItem();
            $items                 = $item->loadList($where);
            foreach ($items as $_item) {
                if ($msg = $_item->delete()) {
                    return $msg;
                }
            }

            $where                  = [];
            $where["facture_id"]    = " = '$this->_id'";
            $where["facture_class"] = " = '$this->_class'";
            $where[]                = "object_class = 'CSejour' OR object_class = 'CConsultation' OR object_class = 'CEvenementPatient'";

            $liaison  = new CFactureLiaison();
            $liaisons = $liaison->loadList($where);
            foreach ($liaisons as $lien) {
                if ($msg = $lien->delete()) {
                    return $msg;
                }
            }
        }

        //Suppression des fichiers
        $this->loadRefsFiles();
        foreach ($this->_ref_files as $_file) {
            if ($msg = $_file->delete()) {
                return $msg;
            }
        }

        // Standard delete
        if ($msg = parent::delete()) {
            return $msg;
        }
    }

    /**
     * Calcul du prochain numéro comptable
     *
     * @return void
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws CouldNotGetCache
     * @throws \Exception
     */
    public function checkNumCompta(): void
    {
        if ($this->num_compta || !$this->praticien_id || !$this->group_id) {
            return;
        }

        /* @var CFactureCabinet|CFactureEtablissement $fact_prat */
        $fact_prat                = new static();
        $where                    = [];
        $field_num_fact           = ($this->_class == "CFactureCabinet") ? "praticien_id" : "group_id";
        $where["$field_num_fact"] = " = '" . $this->$field_num_fact . "'";

        if (!$this->_id) {
            $cache = Cache::getCache(Cache::DISTR);

            $key = implode('-', [$this->_class, self::KEY_CACHE_NUM_COMPTA, $field_num_fact . $this->$field_num_fact]);
            $ttl = 60 * 60 * 24;

            $num_compta = $cache->get($key);

            if ($num_compta) {
                $num_compta++;

                $this->num_compta = $num_compta;

                $cache->set($key, $num_compta, $ttl);

                return;
            }

            $where["num_compta"] = " IS NOT NULL";
            $fact_prat->loadObject($where, "num_compta DESC", null, null, $field_num_fact);

            if ($fact_prat->_id) {
                $this->num_compta = $fact_prat->num_compta + 1;

                $cache->set($key, $this->num_compta, $ttl);

                return;
            }

            unset($where["num_compta"]);
            $nb_fact_prat     = $fact_prat->countList($where);
            $this->num_compta = $nb_fact_prat + 1;

            $cache->set($key, $this->num_compta, $ttl);
        } else {
            $where[]             = "facture_id <> '$this->_id' && facture_id < '$this->_id'";
            $where["num_compta"] = " IS NULL";
            $where["ouverture"]  = " <= '$this->ouverture 23:59:59'";
            $nb_fact_prat        = $fact_prat->countList($where);
            $this->num_compta    = $nb_fact_prat + 1;
        }
    }

    /**
     * Calcul du prochain numéro comptable pour un ensemble de factures
     *
     * @param array                                 $factures
     * @param CFactureCabinet|CFactureEtablissement $facture_type
     */
    static function massCheckNumCompta($factures, $facture_type)
    {
        // Retrait des factures ayants un num_compta
        foreach ($factures as $_facture) {
            if ($_facture->num_compta) {
                unset($factures[$_facture->_id]);
            }
        }
        if (count($factures) === 0) {
            return;
        }

        // Préparation de la récupération des numéros
        /* @var CFactureCabinet|CFactureEtablissement $facture_type */
        $facture    = new $facture_type;
        $name_table = $facture->getSpec()->table;
        $request    = new CRequest();
        $ds         = $facture->getDS();
        $request->addSelect(
            [
                "f1.facture_id as 'facture_id'",
                "COUNT(DISTINCT f2.facture_id)+1 as 'count'",
            ]
        );
        $request->addTable(
            [
                "$name_table f1",
                "$name_table f2",
            ]
        );
        $request->addWhere(
            [
                "f1.praticien_id" => "= f2.praticien_id",
                "f1.facture_id > f2.facture_id",
                "f1.facture_id IN (" . implode(",", CMbArray::pluck($factures, "facture_id")) . ")",
                "f1.ouverture >= f2.ouverture",
            ]
        );
        $request->addGroup("f1.facture_id, f1.praticien_id");
        $resultats = $ds->loadList($request->makeSelect());

        foreach ($resultats as $_result) {
            $facture_id = $_result["facture_id"];
            $ds->exec("UPDATE `$name_table` SET num_compta = '" . $_result["count"] . "' WHERE facture_id = '$facture_id';");
        }
    }

    /**
     * Annulation de la facture d'une consultation
     *
     * @return void|string
     **/
    function cancelFacture(CFacturable $object)
    {
        if ($this->annule) {
            return;
        }
        if ($this->numero && $this->numero > 1) {
            $this->delete();

            return;
        }
        if (count($this->loadRefsLiaisons()) > 1) {
            //Suppression de la liaison à l'objet si plusieurs liens sont présents
            $liaison = new CFactureLiaison();
            $liaison->setObject($object);
            $liaison->facture_id    = $this->_id;
            $liaison->facture_class = $this->_class;
            $liaison->loadMatchingObject();
            if ($msg = $liaison->delete()) {
                return $msg;
            }
            //Réouverture de la facture afin que le total soit recalculé et les lignes de celle-ci modifiée
            $this->cloture = null;
            if ($msg = $this->store()) {
                return $msg;
            }
            //Fermeture automatique de celle-ci selon la configuration
            if (CAppUI::gconf("dPfacturation $this->_class use_auto_cloture")) {
                $this->cloture = CMbDT::date();
                if ($msg = $this->store()) {
                    return $msg;
                }
            }
        } else {
            //Annulation de la facture s'il ne reste qu'un seul lien
            $this->annule = "1";
            if ($msg = $this->store()) {
                return $msg;
            }
        }
    }

    /**
     * Mise à jour des montant secteur 1, 2 et totaux, utilisés pour la compta
     *
     * @return void
     **/
    function updateMontants()
    {
        $this->_secteur1   = 0;
        $this->_secteur2   = 0;
        $this->_secteur3   = 0;
        $this->_montant_dh = 0;
        $this->du_tva      = 0;
        if (!count($this->_ref_items)) {
            $this->loadRefsItems();
        }
        if (!$this->_bill_prat_id) {
            $this->_bill_prat_id = $this->praticien_id;
        }
        if (count($this->_ref_sejours) != 0 || count($this->_ref_consults) != 0 || count($this->_ref_evts) != 0) {
            if (!count($this->_ref_items)) {
                $this->du_patient = 0;
                $this->du_tiers   = 0;
                if (count($this->_ref_sejours) && $this instanceof CFactureEtablissement) {
                    foreach ($this->_ref_sejours as $sejour) {
                        foreach ($sejour->_ref_operations as $op) {
                            foreach ($op->_ref_actes as $acte) {
                                if (!$this->_bill_prat_id || $acte->executant_id == $this->_bill_prat_id) {
                                    $this->_secteur1 += $acte->montant_base;
                                    $this->_secteur2 += $acte->montant_depassement;
                                }
                            }
                        }
                        if ($sejour->_ref_consultations) {
                            foreach ($sejour->_ref_consultations as $_consult) {
                                if (!$this->_bill_prat_id || $_consult->_praticien_id == $this->_bill_prat_id) {
                                    $this->updateMontantForConsult($_consult);
                                }
                            }
                        }
                        foreach ($sejour->_ref_actes as $acte) {
                            if (!$this->_bill_prat_id || $acte->executant_id == $this->_bill_prat_id) {
                                $this->_secteur1 += $acte->montant_base;
                                $this->_secteur2 += $acte->montant_depassement;
                            }
                        }
                        $this->du_patient += $this->_secteur1;
                        $this->du_tiers   += $this->_secteur2;
                    }
                }
                if (count($this->_ref_consults)) {
                    foreach ($this->_ref_consults as $_consult) {
                        $this->_secteur1 += $_consult->secteur1;
                        $this->_secteur2 += $_consult->secteur2;
                        $this->_secteur3 += $_consult->secteur3;
                        $this->updateMontantForConsult($_consult, true);
                    }
                }
                if (count($this->_ref_evts)) {
                    foreach ($this->_ref_evts as $_evt) {
                        foreach ($_evt->_ref_actes as $acte) {
                            $this->_secteur1 += $acte->montant_base;
                            $this->_secteur2 += $acte->montant_depassement;
                        }
                        $this->du_patient += $this->_secteur2;
                        $this->du_tiers   += $this->_secteur1;
                    }
                }
                $this->_secteur1 *= $this->_coeff;
                $this->_secteur2 *= $this->_coeff;
            } else {
                foreach ($this->_ref_items as $item) {
                    $this->_secteur1 += $item->_montant_total_base;
                    $this->_secteur2 += $item->_montant_total_depassement;
                }

                if (!CAppUI::gconf("dPccam codage use_cotation_ccam")) {
                    $this->du_patient = $this->_secteur1;
                    $this->du_tiers   = $this->_secteur2;
                } elseif (count($this->_ref_consults)) {
                    foreach ($this->_ref_consults as $_consult) {
                        if ($_consult->secteur3) {
                            $this->_secteur3 += $_consult->secteur3;
                            $this->du_tva += $_consult->du_tva;
                        }
                    }
                }
            }

            if (count($this->_ref_consults) && !CAppUI::gconf("dPccam codage use_cotation_ccam")) {
                foreach ($this->_ref_consults as $_consult) {
                    if ($_consult->secteur2) {
                        $this->_montant_dh += $_consult->secteur2;
                    }
                }
            }
        }
    }

    /**
     * Mise à jour des montants en fonction de chaque consultation
     *
     * @return void
     **/
    function updateMontantForConsult($_consult, $modif_du_patient = false)
    {
        $_consult->getType();
        $urg                    = $_consult->sejour_id && $_consult->_ref_sejour->_ref_rpu && $_consult->_ref_sejour->_ref_rpu->_id ? true : false;
        $secteur1_change        = $modif_du_patient ? "du_patient" : "_secteur1";
        $secteur2_change        = $modif_du_patient ? "du_tiers" : "_secteur2";
        $this->$secteur1_change += $urg ? $_consult->du_patient + $_consult->du_tiers : $_consult->du_patient;
        $this->$secteur2_change += $urg ? 0 : $_consult->du_tiers;
        $this->du_tva           += $_consult->du_tva;
        $_consult->loadRefsFraisDivers(null);
        if ($_consult->secteur3 && is_countable($_consult->_ref_frais_divers) && count($_consult->_ref_frais_divers)) {
            $this->_secteur1 -= ($this->numero > 1) ? $_consult->_somme : ($_consult->secteur3 + $_consult->du_tva);
            if ($modif_du_patient) {
                $this->du_patient -= ($this->numero > 1) ? $_consult->_somme : ($_consult->secteur3 + $_consult->du_tva);
            }
            $montant_frais = 0;
            foreach ($_consult->loadRefsFraisDivers($this->numero) as $_frais) {
                $montant_frais += $_frais->montant_base;
            }
            $tva          = round($montant_frais * $_consult->taux_tva / 100, 2);
            $this->du_tva += $tva;
            if ($_consult->taux_tva) {
                $this->taux_tva = $_consult->taux_tva;
            }
            $this->du_tva    += $tva;
            $this->_secteur1 += $montant_frais + $tva;
            if ($modif_du_patient) {
                $this->du_patient += $montant_frais + $tva;
            }
        }
    }

    /**
     * Chargement du patient concerné par la facture
     *
     * @param bool $cache cache
     *
     * @return CPatient
     **/
    function loadRefPatient($cache = 1)
    {
        if (!$this->_ref_patient) {
            $this->_ref_patient = $this->loadFwdRef("patient_id", $cache);
            $this->_ref_patient->loadRefsCorrespondantsPatient("date_debut DESC, date_fin DESC");
        }

        return $this->_ref_patient;
    }

    /**
     * Chargement du patient concerné par la facture
     *
     * @return CPatient
     **/
    function loadRelPatient()
    {
        return $this->loadRefPatient();
    }

    /**
     * Chargement du praticien de la facture
     *
     * @return CMediusers
     **/
    function loadRefPraticien()
    {
        if (!$this->_ref_praticien) {
            $this->_ref_praticien = $this->loadFwdRef("praticien_id", true);
        }
        $this->_host_config = "CFunctions-" . $this->_ref_praticien->function_id;

        return $this->_ref_praticien;
    }

    /**
     * Chargement du coefficient
     *
     * @return CFactureCoeff
     **/
    function loadRefCoeff()
    {
        return $this->_ref_coeff = $this->loadFwdRef("coeff_id", true);
    }

    /**
     * Chargement de la catégorie de facturation
     *
     * @return CFactureCategory
     **/
    function loadRefCategory()
    {
        return $this->_ref_category = $this->loadFwdRef("category_id", true);
    }

    /**
     * Chargement du coefficient
     *
     * @return CFactureCoeff[]
     **/
    function loadCoefficients()
    {
        $coeff               = new CFactureCoeff();
        $coeff->praticien_id = $this->praticien_id;
        $coeff->group_id     = CGroups::loadCurrent()->_id;

        return $this->_ref_coefficients = $coeff->loadMatchingList(null, null, "facture_coeff_id");
    }

    /**
     * Chargement des règlements de la facture
     *
     * @return $this->_ref_reglements
     **/
    function loadRefsReglements()
    {
        $this->_montant_sans_remise = 0;
        $this->_montant_avec_remise = 0;

        $this->loadRefsRelances();
        if ($this->_ref_last_relance && $this->_ref_last_relance->_id && $this->cloture && $this->cloture > $this->_ref_last_relance->date) {
            $this->_montant_sans_remise = $this->_ref_last_relance->du_patient + $this->_ref_last_relance->du_tiers;
            $this->_montant_avec_remise = $this->_montant_sans_remise - $this->remise;
        }

        if (!$this->_montant_sans_remise) {
            $this->_montant_sans_remise = $this->du_patient + $this->du_tiers;
            if ($this->_montant_dh) {
                $this->_montant_sans_remise += $this->_montant_dh;
            }
            $this->_montant_avec_remise = $this->_montant_sans_remise - $this->remise;
        }

        $this->_du_restant_patient = $this->du_patient;
        $this->_du_restant_tiers   = $this->du_tiers;

        // Calcul des dus
        $this->_reglements_total_patient = 0.00;
        $this->_reglements_total_tiers   = 0.00;
        $this->_ref_reglements_patient   = [];
        $this->_ref_reglements_tiers     = [];
        $this->_du_restant               = $this->_montant_avec_remise;
        foreach ($this->_ref_reglements as $_reglement) {
            $_reglement->loadRefBanque();
            $_reglement->loadRefDebiteur();
            $this->_du_restant -= $_reglement->montant;
            if ($_reglement->emetteur == "patient") {
                $this->_ref_reglements_patient[] = $_reglement;
                $this->_du_restant_patient       -= $_reglement->montant;
                $this->_reglements_total_patient += $_reglement->montant;
            } else {
                $this->_ref_reglements_tiers[] = $_reglement;
                $this->_du_restant_tiers       -= $_reglement->montant;
                $this->_reglements_total_tiers += $_reglement->montant;
            }
        }

        $this->loadRefsAvoirs();
        $this->_du_restant_patient  -= $this->_montant_avoir;
        $this->_du_restant          -= $this->_montant_avoir;
        $this->_du_restant_patient  = CFacture::roundValue($this->_du_restant_patient, $this->_no_round);
        $this->_du_restant          = CFacture::roundValue($this->_du_restant, $this->_no_round);
        $this->_reglements_total    = CFacture::roundValue($this->_reglements_total_patient + $this->_reglements_total_tiers, $this->_no_round);
        $this->_montant_avec_remise = CFacture::roundValue($this->_montant_avec_remise, $this->_no_round);

        $this->loadDebiteurs();

        return $this->_ref_reglements;
    }

    /**
     * Chargement des avoirs liés à la facture
     *
     * @return \Ox\Core\CStoredObject[]|CFactureAvoir|null
     * @throws \Exception
     */
    function loadRefsAvoirs()
    {
        if ($this->_ref_avoirs) {
            return $this->_ref_avoirs;
        }
        $this->_ref_avoirs    = $this->loadBackRefs("avoirs") ?: [];
        $this->_montant_avoir = 0;
        foreach ($this->_ref_avoirs as $_avoir) {
            $this->_montant_avoir += $_avoir->montant;
        }

        return $this->_ref_avoirs;
    }

    /**
     * Dans la cas de la cotation d'acte Tarmed un facture comporte un coefficient (entre 0 et 1)
     *
     * @return void
     **/
    function loadRefCoeffFacture()
    {
        $this->_coeff = 1;
    }

    /**
     * Chargement de l'assurance de la facture si elle a été choisie
     *
     * @return object
     **/
    function loadRefAssurance()
    {
        $this->_ref_assurance_maladie  = $this->loadFwdRef("assurance_maladie", true);
        $this->_ref_assurance_accident = $this->loadFwdRef("assurance_accident", true);

        return $this->_ref_assurance_maladie;
    }

    /**
     * Chargement des séjours et des consultations de la facture
     *
     * @return void
     **/
    function loadRefsObjects()
    {
        $this->loadRefsConsultation();
        $this->loadRefsSejour();
        $this->loadRefsEvenements();
        $this->loadRefCoeffFacture();

        $this->updateMontants();
    }

    /**
     * Chargement de toutes les consultations de la facture
     *
     * @return object
     **/
    function loadRefsConsultation()
    {
        if (is_countable($this->_ref_consults) && count($this->_ref_consults)) {
            return $this->_ref_consults;
        }

        $consult = new CConsultation();
        if ($this->_id && CModule::getActive("dPfacturation")) {
            $ljoin                                  = [];
            $ljoin["facture_liaison"]               = "facture_liaison.object_id = consultation.consultation_id";
            $where                                  = [];
            $where["facture_liaison.facture_id"]    = " = '$this->_id'";
            $where["facture_liaison.facture_class"] = " = '$this->_class'";
            $where["facture_liaison.object_class"]  = " = 'CConsultation'";
            $this->_ref_consults                    = $consult->loadList($where, null, null, "consultation.consultation_id", $ljoin);
        } elseif ($this->_consult_id) {
            $consult->consultation_id = $this->_consult_id;
            $this->_ref_consults      = $consult->loadMatchingList();
        }

        if (self::$load_consults_light) {
            return $this->_ref_consults;
        }

        $active_fse = CModule::getActive("fse");
        if ($active_fse) {
            $fse = CFseFactory::createFSE();
        }
        if (is_countable($this->_ref_consults) && count($this->_ref_consults) > 0) {
            // Chargement des actes de consultations
            foreach ($this->_ref_consults as $_consult) {
                if (!$_consult->sejour_id) {
                    $this->_is_ambu = 1;
                }
                if ($_consult->sejour_id && $_consult->loadRefSejour()->loadRefRPU()->_id) {
                    $this->_is_urg = 1;
                }
                $_consult->loadRefPlageConsult();
                $_consult->loadRefsActes($this->numero, 1);
                $_consult->loadExtCodesCCAM();
                $this->rangeActes($_consult);
                if ($active_fse && $fse) {
                    $fse->loadIdsFSE($_consult);
                }
                if ($_consult->_current_fse) {
                    $this->_current_fse = $_consult->_current_fse;

                    if ($this->_current_fse->_class == 'CJfseInvoice') {
                        $this->_current_fse_number = $this->_current_fse->invoice_number;
                    } elseif ($this->_current_fse->_class == 'CPyxvitalFSE') {
                        $this->_current_fse_number = $this->_current_fse->facture_numero;
                    } else {
                        $this->_current_fse_number = $this->_current_fse->numero;
                    }
                }
            }
            $consults = $this->_ref_consults;
            usort(
                $consults,
                function ($a, $b) {
                    return $a->_date < $b->_date ? -1 : 1;
                }
            );
            $this->_ref_last_consult  = end($consults);
            $this->_ref_first_consult = reset($consults);
        } else {
            $this->_ref_last_consult  = new CConsultation();
            $this->_ref_first_consult = new CConsultation();
        }

        return $this->_ref_consults;
    }

    /**
     * Chargement de tous les séjours de la facture
     *
     * @return object
     **/
    function loadRefsSejour()
    {
        if (count($this->_ref_sejours)) {
            return $this->_ref_sejours;
        }
        if (CModule::getActive("dPfacturation")) {
            $ljoin                                  = [];
            $ljoin["facture_liaison"]               = "facture_liaison.object_id = sejour.sejour_id";
            $where                                  = [];
            $where["facture_liaison.facture_id"]    = " = '$this->_id'";
            $where["facture_liaison.facture_class"] = " = '$this->_class'";
            $where["facture_liaison.object_class"]  = " = 'CSejour'";

            $sejour             = new CSejour();
            $this->_ref_sejours = $sejour->loadList($where, "sejour_id", null, "sejour_id", $ljoin);

            // Chargement des actes de séjour
            foreach ($this->_ref_sejours as $sejour) {
                /** @var CSejour $sejour */
                if ($sejour->type == "ambu") {
                    $this->_is_ambu = 1;
                }
                foreach ($sejour->loadRefsOperations() as $op) {
                    $op->loadRefsActes($this->numero, 1);
                    $this->rangeActes($op);
                }
                $sejour->loadRefsActes($this->numero, 1);
                $this->rangeActes($sejour);
                foreach ($sejour->loadRefsConsultations() as $_consult) {
                    if (isset($this->_ref_consults[$_consult->_id])) {
                        unset($this->_ref_consults[$_consult->_id]);
                    }
                    $_consult->loadRefsActes($this->numero, 1);
                    //$this->rangeActes($_consult);
                }
            }
        }
        if (count($this->_ref_sejours) > 0) {
            $this->_ref_last_sejour  = end($this->_ref_sejours);
            $this->_ref_first_sejour = reset($this->_ref_sejours);
            $this->_ref_last_sejour->loadRefLastOperation();
            $this->_ref_last_sejour->_ref_last_operation->loadRefAnesth();
        } else {
            $this->_ref_last_sejour  = new CSejour();
            $this->_ref_first_sejour = new CSejour();
        }

        return $this->_ref_sejours;
    }

    /**
     * Chargement de tous les évènements de la facture
     *
     * @return object
     **/
    function loadRefsEvenements()
    {
        if (count($this->_ref_evts)) {
            return $this->_ref_evts;
        }
        if (CModule::getActive("dPfacturation")) {
            $ljoin                                  = [];
            $ljoin["facture_liaison"]               = "facture_liaison.object_id = evenement_patient.evenement_patient_id";
            $where                                  = [];
            $where["facture_liaison.facture_id"]    = " = '$this->_id'";
            $where["facture_liaison.facture_class"] = " = '$this->_class'";
            $where["facture_liaison.object_class"]  = " = 'CEvenementPatient'";

            $evt             = new CEvenementPatient();
            $this->_ref_evts = $evt->loadList($where, "evenement_patient_id", null, "evenement_patient_id", $ljoin);
            // Chargement des actes de séjour
            foreach ($this->_ref_evts as $_evt) {
                /** @var CEvenementPatient $_evt */
                $_evt->loadRefsActes($this->numero, 1);
                $this->rangeActes($_evt);
            }
        }
        if (count($this->_ref_evts) > 0) {
            $this->_ref_last_evt  = end($this->_ref_evts);
            $this->_ref_first_evt = reset($this->_ref_evts);
        } else {
            $this->_ref_last_evt  = new CEvenementPatient();
            $this->_ref_first_evt = new CEvenementPatient();
        }

        return $this->_ref_evts;
    }

    /**
     * Chargement des items de la facture
     *
     * @return CFactureItem[]
     **/
    function loadRefsItems()
    {
        if (count($this->_ref_items)) {
            return $this->_ref_items;
        }
        $this->_ref_items = $this->loadBackRefs("items", 'date ASC, code ASC');
        if (count($this->_ref_items)) {
            $this->_ref_actes_ngap   = [];
            $this->_ref_actes_ccam   = [];
            $this->_ref_actes_divers = [];
            $this->rangeActes($this, false);
        }

        return $this->_ref_items;
    }

    /**
     * Ligne de report pour calculer un numéro de BVR pour la facture
     *
     * @param string $report l'élément à reporter
     *
     * @return string
     **/
    function ligneReport($report)
    {
        $etalon      = ('09468271350946827135');
        $lignereport = substr($etalon, $report, 10);

        return $lignereport;
    }

    /**
     * Création du numéro de contrôle du BVR à l'aide d'un modulo 10
     *
     * @param string $noatraiter le début du numéro de BVR pour obtenir le numéro de controle
     *
     * @return string
     **/
    function getNoControle($noatraiter)
    {
        if (!$noatraiter) {
            $noatraiter = $this->du_patient + $this->du_tiers;
        }
        $noatraiter = str_replace(' ', '', $noatraiter);
        $noatraiter = str_replace('-', '', $noatraiter);
        $noatraiter = str_replace('.', '', $noatraiter);
        $report     = 0;
        $cpt        = strlen($noatraiter);
        for ($i = 0; $i < $cpt; $i++) {
            $report = substr($this->lignereport($report), substr($noatraiter, $i, 1), 1);
        }
        $report = (10 - $report) % 10;

        return $report;
    }

    /**
     * Chargement des différents numéros de BVR de la facture
     *
     * @param bool $executant_id Praticien à prendre en compte
     *
     * @return void
     **/
    function loadTotaux($executant_id = null)
    {
        $this->_ref_items = [];
        $this->loadRefsItems();
        if ($this->cloture && count($this->_ref_items)) {
        } else {
            if (count($this->_ref_evts)) {
                foreach ($this->_ref_evts as $_evt) {
                    $this->loadTotauxObject($_evt);
                }
            }
            if (count($this->_ref_consults)) {
                foreach ($this->_ref_consults as $consult) {
                    $this->loadTotauxObject($consult);
                }
            }
            if (count($this->_ref_sejours)) {
                foreach ($this->_ref_sejours as $sejour) {
                    foreach ($sejour->_ref_operations as $op) {
                        $this->loadTotauxObject($op);
                    }
                    $this->loadTotauxObject($sejour);
                }
            }
        }
    }

    /**
     * Calcul des totaux à partir d'un objet
     *
     * @param object $object objet référence
     *
     * @return void
     **/
    function loadTotauxObject($object) {}

    /**
     * Fonction de création des lignes(items) de la facture lorsqu'elle est cloturée
     *
     * @param object  $object objet référence
     * @param boolean $val    item
     *
     * @return void
     **/
    function rangeActes($object, $val = true)
    {
        $objets = $val ? $object->_ref_actes : $object->_ref_items;
        $type   = $val ? "_class" : "type";
        if (count($objets)) {
            foreach ($objets as $acte) {
                switch ($acte->$type) {
                   case "CActeNGAP" :
                        $this->_ref_actes_ngap[] = $acte;
                        break;
                    case "CActeCCAM" :
                        /** @var CActeCCAM $acte */
                        if ($type == "_class") {
                            $acte->loadRefCodeCCAM();
                        }
                        $this->_ref_actes_ccam[] = $acte;
                        break;
                    case "CFraisDivers" :
                        $this->_ref_actes_divers[] = $acte;
                        break;
                }
            }
        }
    }

    /**
     * Fonction de suppression des lignes(items) de la facture
     *
     * @return void
     **/
    function deleteLignesFacture()
    {
        $this->loadRefsItems();
        foreach ($this->_ref_items as $item) {
            /** @var CFactureItem $item */
            $item->delete();
        }
    }

    /**
     * Fonction de création des lignes(items) de la facture lorsqu'elle est cloturée
     *
     * @return void
     **/
    function creationLignesFacture()
    {
        $this->loadRefCoeffFacture();
        $this->loadRefsObjects();
        foreach ($this->_ref_evts as $_evt) {
            $_evt->loadRefsActes($this->numero, 1);
            foreach ($_evt->_ref_actes as $acte) {
                $acte->creationItemsFacture($this);
            }
        }
        foreach ($this->_ref_consults as $consult) {
            $consult->loadRefsActes($this->numero, 1);
            foreach ($consult->_ref_actes as $acte) {
                $acte->creationItemsFacture($this);
            }
        }

        foreach ($this->_ref_sejours as $sejour) {
            foreach ($sejour->_ref_operations as $op) {
                $op->loadRefPlageOp();
                foreach ($op->_ref_actes as $acte) {
                    $acte->creationItemsFacture($this);
                }
            }
            foreach ($sejour->_ref_actes as $acte) {
                $acte->creationItemsFacture($this);
            }
        }
    }

    /**
     * Fonction permettant de savoir si la facture doit être relancée
     *
     * @return boolean
     **/
    function isRelancable()
    {
        $this->_is_relancable = false;

        //Pas besoin de relancer/Pas relançable
        if ($this->annule || $this->extourne || $this->no_relance || !CAppUI::gconf("dPfacturation CRelance use_relances")) {
            return $this->_is_relancable;
        }

        $date              = CMbDT::date();
        $nb_first_relance  = CAppUI::gconf("dPfacturation CRelance nb_days_first_relance");
        $nb_second_relance = CAppUI::gconf("dPfacturation CRelance nb_days_second_relance");
        $nb_third_relance  = CAppUI::gconf("dPfacturation CRelance nb_days_third_relance");

        $this->_ref_last_relance = count($this->_ref_relances) == 0 ? new CRelance() : end($this->_ref_relances);
        if ($this->_ref_last_relance->statut == "inactive") {
            return $this->_is_relancable;
        }

        if (($this->_du_restant_patient > 0 || $this->_du_restant_tiers > 0) && $this->cloture && !$this->annule) {
            $first   = !count($this->_ref_relances) && CMbDT::daysRelative($this->cloture, $date) >= $nb_first_relance;
            $seconde = count($this->_ref_relances) == 1 && CMbDT::daysRelative($this->_ref_last_relance->date, $date) >= $nb_second_relance;
            $third   = count($this->_ref_relances) == 2 && CMbDT::daysRelative($this->_ref_last_relance->date, $date) >= $nb_third_relance;

            if (CAppUI::gconf("dPfacturation CReglement use_echeancier")) {
                $this->loadRefsEcheances();
                $num_echeance = 0;
                foreach ($this->_ref_echeances as $echeance) {
                    $num_echeance += 1;
                    switch ($num_echeance) {
                        case 1 :
                            $first = $first && CMbDT::daysRelative($echeance->date, $date) >= $nb_first_relance;
                            break;
                        case 2 :
                            $seconde = $seconde && CMbDT::daysRelative($echeance->date, $date) >= $nb_second_relance;
                            break;
                        case 3 :
                            $third = $third && CMbDT::daysRelative($echeance->date, $date) >= $nb_third_relance;
                            break;
                    }
                }
            }

            if ($first || $seconde || $third) {
                $this->_is_relancable = true;
            }
        }

        if (!count($this->_ref_relances)) {
            $this->_echeance = CMbDT::date("+$nb_first_relance DAYS", $this->cloture);
        } else {
            $nb_jours        = count($this->_ref_relances) == 1 ? $nb_second_relance : $nb_third_relance;
            $this->_echeance = CMbDT::date("+$nb_jours DAYS", $this->_ref_last_relance->date);
        }

        return $this->_is_relancable;
    }

    /**
     * Calcul du montant de la retrocession pour la facture
     *
     * @return boolean
     **/
    function updateMontantRetrocession()
    {
        $this->_montant_retrocession = 0;
        $this->loadRefPraticien();
        $this->loadRefsItems();
        $retrocessions = $this->_ref_praticien->loadRefsRetrocessions();
        $add_anesth    = true;
        $use_pm        = false;
        foreach ($this->_ref_items as $item) {
            foreach ($retrocessions as $retro) {
                if ($retro->use_pm && $retro->code_class == $item->type && $retro->code == $item->code && $retro->active) {
                    $use_pm = true;
                }
            }
        }
        foreach ($this->_ref_items as $item) {
            $modif = false;
            if (!(!$add_anesth && strstr($item->code, "28."))) {
                foreach ($retrocessions as $retro) {
                    /** @var CRetrocession $retro */
                    if ($retro->code_class == $item->type && $retro->code == $item->code && $retro->active) {
                        $modif   = true;
                        $montant = $item->quantite * $retro->updateMontant();
                        $this->_montant_retrocession       += $montant;
                        $this->_retrocessions[$item->code] = [$item->_montant_facture, $montant];
                    }
                }
            }
        }
        if ($this->_montant_retrocession && $this->annule) {
            $this->_retrocessions["extourne"] = [0, -$this->_montant_retrocession];
            $this->_montant_retrocession      = 0.00;
        }

        return $this->_montant_retrocession;
    }

    /**
     * Clonage des éléments de la facture
     *
     * @param CModelObject $object la facture
     *
     * @return void
     */
    function cloneFrom(CModelObject $object)
    {
        if (!in_array($object->_class, ['CFactureCabinet', 'CFactureEtablissement'])) {
            return;
        }

        /* @var CFacture $facture */
        $facture = new $object->_class;
        $facture->load($object->_id);
        $this->patient_id               = $facture->patient_id;
        $this->praticien_id             = $facture->praticien_id;
        $this->remise                   = $facture->remise;
        $this->ouverture                = $facture->ouverture;
        $this->du_patient               = $facture->du_patient;
        $this->du_tiers                 = $facture->du_tiers;
        $this->type_facture             = $facture->type_facture;
        $this->npq                      = $facture->npq;
        $this->cession_creance          = $facture->cession_creance;
        $this->assurance_maladie        = $facture->assurance_maladie;
        $this->assurance_accident       = $facture->assurance_accident;
        $this->rques_assurance_maladie  = $facture->rques_assurance_maladie;
        $this->rques_assurance_accident = $facture->rques_assurance_accident;
        $this->send_assur_base          = $facture->send_assur_base;
        $this->send_assur_compl         = $facture->send_assur_compl;
        $this->statut_envoi             = $facture->statut_envoi;
        $this->ref_accident             = $facture->ref_accident;
        $this->statut_pro               = $facture->statut_pro;
        $this->num_reference            = $facture->num_reference;
    }

    /**
     * Clonage des éléments de la facture
     *
     * @return CDebiteur[]|void
     */
    function loadDebiteurs()
    {
        if (!CAppUI::gconf("dPfacturation CReglement use_debiteur")) {
            return null;
        }
        $debiteur  = new CDebiteur();
        $debiteurs = $debiteur->loadList(null, "numero");

        return $this->_ref_debiteurs = $debiteurs;
    }

    /**
     * @inheritdoc
     */
    function fillTemplate(&$template)
    {
        $this->loadRefPatient()->fillLimitedTemplate($template);
        $this->loadRefPraticien()->fillTemplate($template);
        $this->fillLimitedTemplate($template);
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template)
    {
        $this->updateFormFields();

        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $facture_section = CAppUI::tr('CFacture');

        $template->addDateProperty("$facture_section - " . CAppUI::tr('CFactureCabinet-ouverture-desc'), $this->ouverture);
        $template->addProperty("$facture_section - " . CAppUI::tr('CFactureCabinet-du_patient'), $this->du_patient);
        $template->addProperty("$facture_section - " . CAppUI::tr('CFactureCabinet-du_tiers'), $this->du_tiers);
        $template->addProperty("$facture_section - " . CAppUI::tr('CFactureEtablissement-taux_tva'), $this->taux_tva);
        $template->addProperty("$facture_section - " . CAppUI::tr('CFactureEtablissement-du_tva'), $this->du_tva);
        $template->addProperty("$facture_section - " . CAppUI::tr('CFactureCabinet-_montant_avec_remise'), $this->_montant_avec_remise);
        $template->addProperty("$facture_section - " . CAppUI::tr('CFactureCabinet-num_facture'), $this->getNumeroFacture());

        $this->loadRefsReglements();
        if (CAppUI::conf("ref_pays") == 1) {
            $template->addProperty("$facture_section - " . CAppUI::tr('CFactureEtablissement-_secteur1-court'), $this->_secteur1);
            $template->addProperty("$facture_section - " . CAppUI::tr('CFactureEtablissement-_secteur2-court'), $this->_secteur2);
        }
        // Règlements
        $reglements_subItem = CAppUI::tr('CReglement|pl');
        $template->addProperty(
            "$facture_section - $reglements_subItem - " . CAppUI::tr('CFacture-Number of payment|pl'),
            count($this->_ref_reglements)
        );
        $template->addProperty(
            "$facture_section - $reglements_subItem - " . CAppUI::tr('CFactureCabinet-_du_restant_patient'),
            $this->_du_restant_patient
        );
        $template->addProperty(
            "$facture_section - $reglements_subItem - " . CAppUI::tr('CFactureCabinet-_du_restant_tiers'),
            $this->_du_restant_tiers
        );
        $template->addDateProperty(
            "$facture_section - $reglements_subItem - " . CAppUI::tr('CFacture-Patient discharge date'),
            $this->patient_date_reglement
        );
        $template->addDateProperty(
            "$facture_section - $reglements_subItem - " . CAppUI::tr('CFacture-Date acquittal third'),
            $this->tiers_date_reglement
        );
        $template->addProperty(
            "$facture_section - $reglements_subItem - " . CAppUI::tr('CFacture-total_regle_patient'),
            $this->_reglements_total_patient
        );
        $template->addProperty(
            "$facture_section - $reglements_subItem - " . CAppUI::tr('CFacture-total_regle_tiers'),
            $this->_reglements_total_tiers
        );

        //Relances
        if (CAppUI::gconf("dPfacturation CRelance use_relances")) {
            $last_relance_subItem = CAppUI::tr('CRelance-Last relance');
            $this->loadRefsRelances();
            $template->addProperty("$facture_section - " . CAppUI::tr('CFacture-Number of reminder|pl'), count($this->_ref_relances));
            $template->addProperty(
                "$facture_section - $last_relance_subItem - " . CAppUI::tr('CFactureCabinet-numero'),
                $this->_ref_last_relance->numero
            );
            $template->addDateProperty("$facture_section - $last_relance_subItem - " . CAppUI::tr('common-Date'), $this->_ref_last_relance->date);
            $template->addProperty(
                "$facture_section - $last_relance_subItem - " . CAppUI::tr('State'),
                CAppUI::tr("CRelance.etat." . $this->_ref_last_relance->etat)
            );
            $template->addProperty(
                "$facture_section - $last_relance_subItem - " . CAppUI::tr('CReglement-montant'),
                $this->_ref_last_relance->_montant
            );
            $template->addProperty(
                "$facture_section - $last_relance_subItem - " . CAppUI::tr('common-Status'),
                CAppUI::tr("CRelance.statut." . $this->_ref_last_relance->statut)
            );
            $template->addProperty("$facture_section - $last_relance_subItem - " . CAppUI::tr('CEcheance'), $this->_echeance);
        }

        //Rétrocessions
        if (CAppUI::gconf("dPfacturation CRetrocession use_retrocessions")) {
            $retrocession_subItem = CAppUI::tr('CFacture-Retrocession|pl');
            $this->updateMontantRetrocession();
            $template->addProperty(
                "$facture_section - $retrocession_subItem - " . CAppUI::tr('CRetrocession-Number of retrocession|pl'),
                count($this->_retrocessions)
            );
            $template->addProperty(
                "$facture_section - $retrocession_subItem - " . CAppUI::tr('CRetrocession-_montant_total'),
                $this->_montant_retrocession
            );
        }

        //Actes de la facture
        $actes = [];
        $this->loadRefsObjects();
        $types_actes = ["_ref_actes_ngap", "_ref_actes_ccam", "_ref_actes_divers"];
        $conf_date   = CAppUI::conf("date");
        foreach ($types_actes as $_type_acte) {
            foreach ($this->$_type_acte as $_item) {
                $date_formate = CMbDT::format(($_item instanceof CFactureItem) ? $_item->date : $_item->execution, $conf_date);
                $type_item    = ($_item instanceof CFactureItem) ? $_item->type : $_item->_class;
                if ($_item instanceof CActeCCAM) {
                    $actes["{$date_formate}-{$type_item}-$_item->code_acte"] = "1 x {$_item->code_acte} ($date_formate)";
                } else {
                    $actes["{$date_formate}-{$type_item}-$_item->code"] = "{$_item->quantite} x {$_item->code} ($date_formate)";
                }
            }
        }
        ksort($actes);
        $template->addListProperty("$facture_section - " . CAppUI::tr('CCodable-actes'), $actes);

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }


    function loadNumAdherent($num)
    {
        $adherent_first = str_replace(' ', '-', $num);
        $adherent       = explode('-', $adherent_first);
        $num_adherent   = 0;
        if (count($adherent) == 1) {
            $num_adherent = $adherent[0];
        } elseif (count($adherent) >= 2) {
            $nbcolonnes     = 8 - strlen($adherent[0]);
            $adherent_first = $adherent[0] . "-" . $adherent[1];
            $num_adherent   = $adherent[0] . sprintf("%0" . $nbcolonnes . "s", $adherent[1]);
        }

        $cle_adherent    = $this->getNoControle($num_adherent);
        $numero_adherent = $adherent_first . "-$cle_adherent";

        return [
            "compte" => $numero_adherent,
            "bvr"    => $num_adherent . $cle_adherent,
        ];
    }

    /**
     * Construit le numero de la facture
     *
     * @return string
     */
    function getNumeroFacture(): string
    {
        return sprintf("FA%08d", $this->_id) . " / $this->num_compta";
    }

    /**
     * Load associated Group
     *
     * @return CGroups
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Gestion de l'arrondi de la facture en fonction du pays
     *
     * @return float
     */
    static function roundValue($value, $option = false)
    {
        return sprintf("%.2f", $value);
    }

    function loadRefAssurancePatient()
    {
        $this->loadRefAssurance();
        // TP uniquement pour accident
        // TP/TG/TS      pour maladie
        $view            = "_longview";
        $this->_type_rbt = "TG";
        if ($this->assurance_maladie &&
            !$this->send_assur_base &&
            $this->_ref_assurance_maladie->type_pec != "TG" &&
            $this->type_facture == "maladie") {
            $this->_assurance_patient = $this->_ref_assurance_maladie;
            $this->_type_rbt          = $this->_ref_assurance_maladie->type_pec;
        } elseif ($this->assurance_accident && !$this->send_assur_compl && $this->type_facture == "accident") {
            if (!$this->_host_config) {
                $this->loadRefPraticien();
            }

            $this->_type_rbt = "TP";
            $this->_assurance_patient = $this->_ref_assurance_accident;
        } else {
            $this->_assurance_patient = $this->_ref_patient;
            $view                     = "_view";
        }
        $this->_assurance_patient_view = $this->_assurance_patient->$view;
        $this->_type_rbt               = $this->_type_rbt == "" ? "TG" : $this->_type_rbt;
        $this->_type_rbt               = $this->_type_rbt == "TS" ? "TG avec cession" : $this->_type_rbt;
    }

    /**
     * Chargement des différents états de la facture, stockage :
     *  - _statut: Liste des statuts de la facture (tableau)
     *  - _statut_view : Liste des statuts de la facture (chaine de caractères, traduite)
     *  - _main_statut : Statut principal, utilisable à l'affichage
     */
    function loadStatut()
    {
        $this->loadRefsRejets();
        $this->loadRefsItems();
        $this->_statut = [];
        if ($this->extourne) {
            $this->_statut[]    = self::STATUS_EXTOURNEE;
            $this->_main_statut = self::STATUS_EXTOURNEE;
        } elseif ($this->annule) {
            $this->_statut[]    = self::STATUS_HATCHING;
            $this->_main_statut = self::STATUS_HATCHING;
        }
        if (count($this->_ref_rejets)) {
            $this->_statut[]    = self::STATUS_REJETS;
            $this->_main_statut = $this->_main_statut ?: self::STATUS_REJETS;
        }
        if (!$this->cloture) {
            $this->_statut[]    = self::STATUS_NON_CLOTURE;
            $this->_main_statut = $this->_main_statut ?: self::STATUS_NON_CLOTURE;
        }
        if ($this->regle) {
            $this->_statut[]    = self::STATUS_REGLEE;
            $this->_main_statut = $this->_main_statut ?: self::STATUS_REGLEE;
        }
        if ($this->cloture && count($this->_ref_items)) {
            $this->_statut[]    = self::STATUS_COTEE;
            $this->_main_statut = $this->_main_statut ?: self::STATUS_COTEE;
        } elseif ($this->cloture) {
            $this->_statut[]    = self::STATUS_NONCOTEE;
            $this->_main_statut = $this->_main_statut ?: self::STATUS_NONCOTEE;
        }
        if ($this->_ref_last_relance && $this->_ref_last_relance->_id) {
            switch ($this->_ref_last_relance->numero) {
                case "1" :
                    $jours = CAppUI::gconf("dPfacturation CRelance nb_days_first_relance");
                    break;
                case "2" :
                    $jours = CAppUI::gconf("dPfacturation CRelance nb_days_second_relance");
                    break;
                default:
                    $jours = CAppUI::gconf("dPfacturation CRelance nb_days_third_relance");
            }

            $str_relance = CAppUI::tr("CRelance-no");
            $str_relance .= $this->_ref_last_relance->numero . " - " . $jours;
            $str_relance .= " " . CAppUI::tr(intval($jours) > 1 ? "Days" : "Day");

            $this->_main_statut = $this->_main_statut ?: $str_relance;
        }

        $this->_statut_view = "";
        foreach ($this->_statut as $_statut_key => $_statut) {
            $this->_statut_view .= ($this->_statut_view !== "" ? ", " : "") . CAppUI::tr($_statut);
        }

        if (isset($str_relance)) {
            $this->_statut_view .= ($this->_statut_view !== "" ? ", " : "") . $str_relance;
        }
    }

    /**
     * Chargement du fichier XML sauvegardé
     *
     * @return void
     */
    function loadFileXML()
    {
        $this->loadNamedFile(CFacture::$_file_name);
    }

    /**
     * Chargement de la facture extournée
     *
     * @return CFacture
     **/
    function loadRefExtourne()
    {
        return $this->_ref_extourne = $this->loadFwdRef("extourne_id", true);
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        return !$prat_ids || in_array($this->praticien_id, $prat_ids);
    }

    /**
     * Active une facture
     * Pour le cas d'une facture liée a des consultations et/ou événements patient, un check est opéré sur chaque élément.
     *
     * @return bool
     */
    public function enableFacture()
    {
        $this->loadRefsConsultation();
        $this->loadRefsSejour();
        $this->loadRefsEvenements();
        foreach ([$this->_ref_consults, $this->_ref_evts] as $_items) {
            foreach ($_items as $_item) {
                // Cas de l'enregistrement d'une consultation
                if ($_item instanceof CConsultation && ($_item->_id === $this->_consult_id)) {
                    continue;
                }
                // Cas de l'enregistrement d'un evenement patient
                if ($_item instanceof CEvenementPatient && ($_item->_id === $this->_evt_id)) {
                    continue;
                }
                if (!$_item->valide) {
                    return $this->annule = 1;
                }
            }
        }

        return $this->annule = 0;
    }

    /**
     * Détecte si la facture est en Tiers Soldant
     *
     * @return bool
     */
    public function isTiersSoldant()
    {
        $this->loadRefAssurance();

        return (($this->type_facture === "maladie" && $this->_ref_assurance_maladie->_id
                && $this->_ref_assurance_maladie->type_pec === "TS") || ($this->type_facture === "accident"
                && $this->_ref_assurance_accident->_id && $this->_ref_assurance_accident->type_pec === "TS"));
    }

    /**
     * Retourne le premier reglement de la facture
     *
     * @param bool $get_reglement_object Permet de retourner uniquement le montant, ou le CReglement
     *
     * @return int|CReglement
     */
    public function getFirstReglement($get_reglement_object = false)
    {
        $reglements = $this->loadRefsReglements();

        if (!$reglements || count($reglements) === 0) {
            return $get_reglement_object ? new CReglement() : 0;
        }
        CMbArray::pluckSort($reglements, SORT_ASC, "date");
        $reglement = reset($reglements);

        return $get_reglement_object ? $reglement : $reglement->montant;
    }

    /**
     * Retourne la date début et la date de fin du traitement associé à la facture
     *
     * @return array (Date de début, Date de fin)
     */
    public function getTraitementPeriode()
    {
        $this->loadRefsSejour();
        $this->loadRefsConsultation();
        $this->loadRefsEvenements();
        $first = $last = null;

        // Recuperation des dates de consultation
        if ($this->_ref_first_consult && $this->_ref_first_consult->_id) {
            $first = $this->_ref_first_consult->_date;
        }
        if ($this->_ref_last_consult && $this->_ref_last_consult->_id) {
            $last = $this->_ref_last_consult->_date;
        }

        // Recuperation des dates d'evenement patient
        if ($this->_ref_first_evt && $this->_ref_first_evt->_id) {
            $first_evt_date = $this->_ref_first_evt->date;
            $first          = (!$first || ($first_evt_date && $first_evt_date < $first)) ? $first_evt_date : $first;
        }
        if ($this->_ref_last_evt && $this->_ref_last_evt->_id) {
            $last_evt_date = $this->_ref_last_evt->date;
            $last          = (!$last || ($last_evt_date && $last_evt_date > $last)) ? $last_evt_date : $last;
        }

        // Recuperation des dates de sejour
        if ($this->_ref_first_sejour && $this->_ref_first_sejour->_id) {
            $first_sejour_date = $this->_ref_first_sejour->entree;
            $first             = (!$first || ($first_sejour_date && $first_sejour_date < $first)) ? $first_sejour_date : $first;
        }
        if ($this->_ref_last_sejour && $this->_ref_last_sejour->_id) {
            $last_sejour_date = $this->_ref_last_sejour->sortie;
            $last             = (!$last || ($last_sejour_date && $last_sejour_date > $last)) ? $last_sejour_date : $last;
        }

        return [$first, $last];
    }

    /**
     * Création d'un facture
     *
     * @param CEvenementPatient|CSejour|CConsultation $object Objet auquel rattacher la facture
     *
     * @return null|String
     * @throws \Exception
     */
    static public function save($object)
    {
        $facture = $object->loadRefFacture(true);

        // Préparation de la facture en fonction l'objet
        if ($object instanceof CSejour) {
            if (!$facture->_id) {
                $facture->ouverture = CMbDT::dateTime();
            }
            if (CAppUI::gconf("dPfacturation CFactureEtablissement use_temporary_bill")) {
                $facture->temporaire = 1;
            }
            $facture->group_id        = $object->group_id;
            $facture->patient_id      = $object->patient_id;
            $facture->praticien_id    = $object->_bill_prat_id ? $object->_bill_prat_id : $object->praticien_id;
            $facture->type_facture    = $object->_type_sejour;
            $facture->dialyse         = $object->_dialyse;
            $facture->cession_creance = $object->_cession_creance;
            $facture->statut_pro      = $object->_statut_pro;

            $type_assurance              = $facture->type_facture != "accident" ? "assurance_maladie" : "assurance_accident";
            $rq_type_assurance           = "rques_$type_assurance";
            $facture->$type_assurance    = $object->_assurance_maladie;
            $facture->$rq_type_assurance = $object->_rques_assurance_maladie;
        } elseif ($object instanceof CConsultation) {
            $facture->group_id     = CGroups::loadCurrent()->_id;
            $plage                 = $object->_ref_plageconsult;
            $facture->praticien_id = $plage->pour_compte_id ? $plage->pour_compte_id : $plage->chir_id;
            $facture->_consult_id  = $object->_id;
            $facture->du_patient   = $object->du_patient;
            $facture->du_tiers     = $object->du_tiers;
            $facture->du_tva       = $object->du_tva;
            $facture->taux_tva     = $object->taux_tva;
            $facture->category_id  = $object->_category_facturation;
            $facture->loadRefsConsultation();
            $facture->_consult = $object;
            if (isset($facture->_ref_consults[$object->_id])) {
                $facture->_ref_consults[$object->_id]->du_patient = $object->du_patient;
                $facture->_ref_consults[$object->_id]->du_tiers   = $object->du_tiers;
            }

            $object->loadRefsFraisDivers(2);
            if (is_countable($object->_ref_frais_divers) && count($object->_ref_frais_divers) && $object->secteur3) {
                $facture->du_patient = $object->du_patient - $object->du_tva - $object->secteur3;
                $facture->du_tva     = 0;
                $facture->taux_tva   = $object->taux_tva;
            }
        } elseif ($object instanceof CEvenementPatient) {
            $object->loadRefPatient();
            $group_id = CGroups::loadCurrent()->_id;
            if (!$facture->_id ||
                $facture->patient_id !== $object->_ref_patient->_id ||
                $facture->praticien_id !== $object->praticien_id ||
                $facture->cloture) {
                $where = [
                    "group_id"     => "= '" . $group_id . "'",
                    "patient_id"   => "= '" . $object->_ref_patient->_id . "'",
                    "praticien_id" => "= '" . $object->praticien_id . "'",
                    "cloture"      => " IS NULL",
                ];
                $facture->loadObject($where, "facture_id DESC");
            }
            $facture->praticien_id = $object->praticien_id;
            $facture->_evt_id      = $object->_id;
            if (!$facture->_id) {
                $facture->ouverture  = CMbDT::dateTime();
                $facture->group_id   = CGroups::loadCurrent()->_id;
                $facture->patient_id = $object->_ref_patient->_id;
            } else {
                $facture->enableFacture();
            }
        }

        $msg = $facture->store();

        // Pour le cas du séjour, on génére la liaison
        if (!$msg && $object instanceof CSejour && is_array($object->_ref_factures) && count($object->_ref_factures) === 0) {
            $liaison                = new CFactureLiaison();
            $liaison->object_id     = $object->_id;
            $liaison->object_class  = $object->_class;
            $liaison->facture_id    = $facture->_id;
            $liaison->facture_class = "CFactureEtablissement";
            $msg                    = $liaison->store();
        }

        return $msg;
    }

    /**
     * Chargement du montant relatif aux échéances de la facture
     *
     * @return int
     */
    protected function loadEcheancesMontant()
    {
        $this->_montant_echeance = 0;
        if (count($this->_ref_echeances) === 0) {
            return $this->_montant_echeance;
        }
        foreach ($this->_ref_echeances as $_echeance) {
            $this->_montant_echeance += $_echeance->montant;
        }
        $montant_facture               = $this->montant_total > 0 ? $this->montant_total : ($this->du_patient + $this->du_tiers);
        $this->_interest_echeance      = $montant_facture == 0 ?: round((($this->_montant_echeance / $montant_facture) - 1) * 100, 1);
        $this->_montant_total_echeance = self::roundValue($this->_montant_echeance);

        return $this->_montant_echeance;
    }
}
