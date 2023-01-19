<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use DateTime;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Ccam\CBillingPeriod;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Repas\CRepas;
use Ox\Mediboard\Repas\CTypeRepas;
use Ox\Mediboard\Rhm\CDIRHMEpisode;

/**
 * Classe CAffectation.
 *
 * @abstract Gère les affectation des séjours dans des lits
 */
class CAffectation extends CMbObject implements ImportableInterface, IGroupRelated
{
    /** @var bool */
    public static $skip_check_billing_period;

    public $affectation_id;

    // DB References
    public $service_id;
    public $lit_id;
    public $sejour_id;
    public $parent_affectation_id;
    public $function_id;
    public $praticien_id;
    public $mode_entree;
    public $mode_entree_id;
    public $provenance;
    public $etablissement_sortie_id;
    public $mode_sortie;
    public $mode_sortie_id;
    public $destination;

    // DB Fields
    public $entree;
    public $sortie;
    public $effectue;
    public $rques;
    public $uhcd;

    public $uf_hebergement_id; // UF de responsabilité d'hébergement
    public $uf_medicale_id; // UF de responsabilité médicale
    public $uf_soins_id; // UF de responsabilité de soins

    // Form Fields
    public $_entree_relative;
    public $_sortie_relative;
    public $_mode_sortie;
    public $_duree;
    public $_is_prolong;
    public $_entree;
    public $_sortie;
    public $_start_prolongation;
    public $_stop_prolongation;
    public $_width_prolongation;
    public $_affectations_enfant_ids = [];
    public $_mutation_urg            = false;
    public $_hour;
    public $_anesth;
    public $_liaisons_sejour;
    public $_alertes_ufs             = [];
    public $_block_lit;
    public $_in_permission;
    public $_in_permission_sup_48h;
    public $_affectation_perm_id;

    // Order fields
    public $_patient;
    public $_praticien;
    public $_chambre;

    /** @var CLit */
    public $_ref_lit;

    /** @var CFunctions */
    public $_ref_function;

    /** @var CService */
    public $_ref_service;

    /** @var CSejour */
    public $_ref_sejour;

    /** @var self */
    public $_ref_prev;

    /** @var self */
    public $_ref_next;

    /** @var self */
    public $_ref_next_affectation_same_bed;

    public $_no_synchro;
    public $_no_synchro_eai;
    public $_list_repas;

    /** @var CUniteFonctionnelle */
    public $_ref_uf_hebergement;

    /** @var CUniteFonctionnelle */
    public $_ref_uf_medicale;

    /** @var CUniteFonctionnelle */
    public $_ref_uf_soins;

    /** @var self */
    public $_ref_parent_affectation;

    /** @var CAffectation[] */
    public $_ref_affectations_enfant;
    public $_nb_affectations_enfant;

    /** @var CItemLiaison[] */
    public $_liaisons_for_prestation;

    /** @var CMediusers */
    public $_ref_praticien;

    /** @var CDIRHMEpisode[] */
    public $_ref_episodes;

    /** @var CMovement[] */
    public $_ref_movements;

    /** @var CEtabExterne */
    public $_ref_etablissement_transfert;

    /** @var CModeEntreeSejour */
    public $_ref_mode_entree;

    static $width_vue_tempo = 84.2;

    public $_synchro_sortie = true;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'affectation';
        $spec->key   = 'affectation_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                          = parent::getProps();
        $props["service_id"]            = "ref notNull class|CService back|affectations";
        $props["lit_id"]                = "ref class|CLit back|affectations";
        $props["sejour_id"]             = "ref class|CSejour cascade back|affectations";
        $props["parent_affectation_id"] = "ref class|CAffectation back|affectations_enfant";
        $props["function_id"]           = "ref class|CFunctions back|affectations";
        $props["praticien_id"]          = "ref class|CMediusers back|affectation";
        $props["entree"]                = "dateTime notNull";
        $props["sortie"]                = "dateTime notNull moreThan|entree";
        $props["effectue"]              = "bool";
        $props["rques"]                 = "text helped";
        $props["uhcd"]                  = "bool default|0";

        $props["uf_hebergement_id"] = "ref class|CUniteFonctionnelle seekable back|affectations_hebergement";
        $props["uf_medicale_id"]    = "ref class|CUniteFonctionnelle seekable back|affectations_medical";
        $props["uf_soins_id"]       = "ref class|CUniteFonctionnelle seekable back|affectations_soin";

        $props["mode_entree"]    = "enum list|N|8|7|6|0";
        $props["mode_entree_id"] = "ref class|CModeEntreeSejour autocomplete|libelle|true back|affectations";
        $props["provenance"]     = "enum list|1|2|3|4|5|6|7|8|R";

        $props["etablissement_sortie_id"] = "ref class|CEtabExterne autocomplete|nom back|affectations";
        $props["mode_sortie"]             = "enum list|0|4|5|6|7|8|9";
        $props["mode_sortie_id"]          = "ref class|CModeSortieSejour autocomplete|libelle|true back|affectations";
        $props["destination"]             = "enum list|0|" . implode("|", CSejour::$destination_values);

        $props["_duree"]       = "num";
        $props["_mode_sortie"] = "enum list|normal|mutation|transfert|deces default|normal";
        $props["_block_lit"]   = "bool";

        $props["_patient"]   = "str";
        $props["_praticien"] = "str";
        $props["_chambre"]   = "str";

        return $props;
    }

    /**
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        if (!$this->_id) {
            return;
        }

        $this->updateView();
        $sejour = $this->loadRefSejour();
        $sejour->loadRefPraticien();
        $sejour->loadRefPatient()->loadRefPhotoIdentite();

        if (CModule::getActive("maternite")) {
            if ($sejour->grossesse_id) {
                foreach ($sejour->loadRefGrossesse()->loadRefsNaissances() as $_naissance) {
                    $_naissance->loadRefSejourEnfant()->loadRefPatient();
                }
            } else {
                $sejour->loadRefNaissance()->loadRefSejourMaman()->loadRefPatient();
            }
        }

        $this->loadRefPraticien();
        $this->loadRefService();
        $this->loadRefParentAffectation();

        if (!$sejour->_ref_operations) {
            $sejour->loadRefsOperations();
        }

        foreach ($sejour->_ref_operations as $_operation) {
            $_operation->loadRefChir();
            $_operation->loadRefPlageOp();
        }

        $sejour->getDroitsC2S();

        CUniteFonctionnelle::getAlertesUFs($this);
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();
        $this->_duree = CMbDT::daysRelative($this->entree, $this->sortie);
    }

    public function updateView(): void
    {
        if (!$this->lit_id) {
            $this->_view = $this->loadRefService()->_view;
        } else {
            $this->loadRefLit()->loadCompleteView();
            $this->_view = $this->_ref_lit->_view;
        }
    }

    /**
     * @param self[] $affectations
     *
     * @return void
     * @throws Exception
     */
    public static function massUpdateView(array $affectations = []): void
    {
        if (!count($affectations)) {
            return;
        }

        $lits     = CStoredObject::massLoadFwdRef($affectations, 'lit_id');
        $chambres = CStoredObject::massLoadFwdRef($lits, 'chambre_id');
        CStoredObject::massLoadFwdRef($chambres, 'service_id');
        CStoredObject::massLoadFwdRef($affectations, 'service_id');

        foreach ($affectations as $affectation) {
            $affectation->updateView();
        }
    }

    /**
     * @see parent::check()
     */
    function check()
    {
        if ($msg = parent::check()) {
            return $msg;
        }

        if ($msg = $this->checkCollisions()) {
            return $msg;
        }

        return null;
    }

    /**
     * Check collision
     *
     * @return string|null Store-like message
     */
    function checkCollisions()
    {
        $this->completeField("sejour_id");
        if (!$this->sejour_id) {
            return null;
        }

        $affectation            = new CAffectation();
        $affectation->sejour_id = $this->sejour_id;
        $affectations           = $affectation->loadMatchingList();
        unset($affectations[$this->_id]);

        foreach ($affectations as $_aff) {
            if ($this->collide($_aff)) {
                return "Placement déjà effectué";
            }
        }

        return null;
    }

    /**
     * Delete only one
     *
     * @return null|string
     */
    function deleteOne()
    {
        return parent::delete();
    }

    /**
     * @see parent::delete()
     */
    function delete()
    {
        $this->completeField("sejour_id", "entree", "sortie");
        if (!$this->sejour_id || !$this->_synchro_sortie) {
            return $this->deleteOne();
        }

        if ($this->loadRefSejour()->type == "seances") {
            return $this->deleteOne();
        }

        $this->loadRefsAffectations();

        $entree = $this->entree;
        $sortie = $this->sortie;

        if ($msg = $this->deleteOne()) {
            return $msg;
        }

        // On positionne la sortie de la précédente affectation à l'affectation que l'on supprime
        $prev = $this->_ref_prev;
        if (isset($prev->_id)) {
            $prev->sortie   = $sortie;
            $prev->_is_prev = 1;

            // Si pas d'affectation suivante, on annule la validation du déplacement
            if (!$this->_ref_next->_id) {
                $prev->effectue = 0;
            }

            if ($msg = $prev->store()) {
                return $msg;
            }

            foreach ($this->_ref_sejour->loadRefsAffectations("entree ASC") as $_aff) {
                $_aff->storePrestations();
            }

            return null;
        }

        // On positionne l'entrée de la suivante affectation à l'affectation que l'on supprime
        $next = $this->_ref_next;
        if (isset($next->_id)) {
            $next->entree   = $entree;
            $next->_is_next = 1;

            if ($msg = $next->store()) {
                return $msg;
            }

            foreach ($this->_ref_sejour->loadRefsAffectations("entree ASC") as $_aff) {
                $_aff->storePrestations();
            }

            return null;
        }

        foreach ($this->_ref_sejour->loadRefsAffectations("entree ASC") as $_aff) {
            $_aff->storePrestations();
        }

        return null;
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->completeField("sejour_id", "lit_id", "entree", "sortie");
        $create_affectations = false;
        $sejour              = $this->loadRefSejour();
        $sejour->loadRefPatient();

        if (!static::$skip_check_billing_period && ($msg = CBillingPeriod::checkStore($sejour, $this))) {
            return $msg;
        }

        $lit_modified = $this->fieldModified("lit_id");

        // Conserver l'ancien objet avant d'enregistrer
        $old = new CAffectation();
        if ($this->_id) {
            $old->load($this->_id);
            // Si ce n'est pas la première affectation de la série, alors la ref_prev et la ref_next sont erronées
            // si prises depuis l'affectation old
            if (isset($this->_is_prev) || isset($this->_is_next)) {
                $this->loadRefsAffectations();
                $old->_ref_prev = $this->_ref_prev;
                $old->_ref_next = $this->_ref_next;
            } else {
                $old->loadRefsAffectations();
            }
        }

        // Gestion du service_id
        if ($this->lit_id) {
            $this->service_id = $this->loadRefLit(false)->loadRefChambre(false)->service_id;
        }

        $service = $this->loadRefService();

        if ($service->_id && $sejour->_id && in_array(
                $sejour->type,
                CSejour::getTypesSejoursUrgence($sejour->praticien_id)
            )
            && !$service->urgence && !$service->radiologie && !$service->obstetrique && !$service->uhcd
        ) {
            return CAppUI::tr("CAffectation-Emergency stay cant be in non emergency service");
        }

        $this->loadRefsAffectations();

        // Si un séjour est en UHCD, on recopie l'uf médicale de la précédente affectation
        if (!$this->_id && !$this->uf_medicale_id && $sejour->_id && $sejour->UHCD && $this->_ref_prev->_id) {
            $this->uf_medicale_id = $this->_ref_prev->uf_medicale_id;
        }

        // Gestion des UFs
        $this->makeUF();

        // Si c'est une création d'affectation, avec ni une précédente ni une suivante,
        // que le séjour est relié à une grossesse, et que le module maternité est actif,
        // alors il faut créer les affectations des bébés.
        if (CModule::getActive("maternite") &&
            !is_numeric($sejour->_ref_patient->nom) &&
            $sejour->grossesse_id &&
            !$this->_id
        ) {
            if (!$this->_ref_prev->_id && !$this->_ref_next->_id) {
                $create_affectations = true;
            }
        }

        $store_prestations = false;

        if ($this->lit_id && $this->sejour_id && (!$this->_id || $this->fieldModified("lit_id"))) {
            $store_prestations = true;
        }

        // Si on place le patient alors que le séjour a déjà une sortie réelle
        // alors on passe le flag effectue à 1 sur l'affectation
        if (!$this->_id && $sejour->sortie_reelle) {
            $this->effectue = 1;
        }

        // On empêche la création d'une deuxième affectation sur le séjour si pas d'entrée réelle
        if (!$this->_id && !CAppUI::gconf(
                "dPplanningOp CSejour multiple_affectation_pread"
            ) && !$sejour->entree_reelle) {
            $aff   = new CAffectation();
            $where = [
                "entree"    => "< '$this->entree'",
                "sejour_id" => "= '$this->sejour_id'",
            ];
            if ($aff->loadIds($where)) {
                return CAppUI::tr("CAffectation-one_affectation_pread");
            }
        }

        // Enregistrement standard
        if ($msg = parent::store()) {
            return $msg;
        }

        /** @var CAffectation $_affectation */
        if ($lit_modified) {
            $this->completeField("parent_affectation_id");
            if ($this->parent_affectation_id) {
                $parent_aff         = $this->loadRefParentAffectation();
                $parent_aff->lit_id = $this->lit_id;
                if ($msg = $parent_aff->store()) {
                    return $msg;
                }
            } else {
                foreach ($this->loadBackRefs("affectations_enfant") as $_affectation) {
                    $_affectation->lit_id = $this->lit_id;
                    if ($msg = $_affectation->store()) {
                        return $msg;
                    }
                }
            }
        }

        // Niveaux de prestations réalisées à créer
        if ($store_prestations) {
            foreach ($sejour->loadRefsAffectations("entree ASC") as $_aff) {
                $_aff->storePrestations();
            }
        }

        if ($create_affectations) {
            $grossesse  = $this->_ref_sejour->loadRefGrossesse();
            $naissances = $grossesse->loadRefsNaissances();

            $sejours = CMbObject::massLoadFwdRef($naissances, "sejour_enfant_id");

            foreach ($sejours as $_sejour) {
                $_affectation            = new self();
                $_affectation->sejour_id = $_sejour->_id;
                // Une affectation a pu être créée (règles de sectorisation)
                $_affectation->loadMatchingObject();
                $_affectation->lit_id                = $this->lit_id;
                $_affectation->parent_affectation_id = $this->_id;
                $_affectation->entree                = $_affectation->entree ?: $_sejour->entree;
                $_affectation->sortie                = $this->sortie;
                if ($msg = $_affectation->store()) {
                    return $msg;
                }
            }
        }

        // Pas de problème de synchro pour les blocages de lits
        if (!$this->sejour_id || $this->_no_synchro) {
            return $msg;
        }

        // Modification de la date d'admission et de la durée de l'hospi
        $this->load($this->_id);

        if ($old->_id) {
            $this->_ref_prev = $old->_ref_prev;
            $this->_ref_next = $old->_ref_next;
        } else {
            $this->loadRefsAffectations();
        }

        $changeSejour = 0;
        $changePrev   = 0;
        $changeNext   = 0;

        $prev = $this->_ref_prev;
        $next = $this->_ref_next;

        // Mise à jour vs l'entrée
        if (!$prev->_id) {
            if ($this->entree != $sejour->entree) {
                $field          = $sejour->entree_reelle ? "entree_reelle" : "entree_prevue";
                $sejour->$field = $this->entree;
                $changeSejour   = 1;
            }
        } elseif ($this->entree != $prev->sortie) {
            $prev->sortie = $this->entree;
            $changePrev   = 1;
        }

        // Mise à jour vs la sortie
        if (!$next->_id) {
            if ($this->sortie != $sejour->sortie) {
                $field          = $sejour->sortie_reelle ? "sortie_reelle" : "sortie_prevue";
                $sejour->$field = $this->sortie;
                $changeSejour   = 1;
            }
        } elseif ($this->sortie != $next->entree) {
            $next->entree = $this->sortie;
            $changeNext   = 1;
        }

        if ($changePrev) {
            $prev->_is_prev = 1;
            $prev->store();
        }

        if ($changeNext) {
            $next->_is_next = 1;
            $next->store();
        }

        if ($changeSejour) {
            $sejour->updateFormFields();
            $sejour->_no_synchro = true;
            $save_type           = $sejour->type;
            if ($msg = $sejour->store()) {
                return $msg;
            }

            // Si le type d'hospitalisation du séjour a changé, il faut refaire l'association d'ufs
            if ($sejour->type !== $save_type) {
                $this->load($this->_id);
                $this->makeUF();
                if ($msg = parent::store()) {
                    return $msg;
                }
            }
        }

        // Création d'un blocage lors d'un envoi en permission
        if ($this->_block_lit && $this->_ref_prev->lit_id) {
            $blocage         = new CAffectation();
            $blocage->entree = $this->entree;
            $blocage->sortie = $this->sortie;
            $blocage->lit_id = $this->_ref_prev->lit_id;
            $blocage->store();
        }

        return $msg;
    }

    /**
     * Chargement du lit de l'affectation
     *
     * @param bool $cache cache
     *
     * @return CLit
     */
    function loadRefLit($cache = true)
    {
        return $this->_ref_lit = $this->loadFwdRef("lit_id", $cache);
    }

    /**
     * Chargement de la fonction de l'affectation
     *
     * @param bool $cache cache
     *
     * @return CFunctions
     * @throws Exception
     */
    public function loadRefFunction($cache = true): CFunctions
    {
        return $this->_ref_function = $this->loadFwdRef("function_id", $cache);
    }

    /**
     * Chargement du séjour de l'affectation
     *
     * @param bool $cache cache
     *
     * @return CSejour
     */
    function loadRefSejour($cache = true)
    {
        return $this->_ref_sejour = $this->loadFwdRef("sejour_id", $cache);
    }

    /**
     * Chargement de l'affectation suivante d'un même lit
     *
     * @param CLit     $lit_id   Lit id
     * @param dateTime $date_end Exit date of a stay
     *
     * @return CAffectation
     */
    function loadNextAffectationInSameBed($lit_id, $date_end)
    {
        $date = CMbDT::date($date_end);

        $affectation     = new self();
        $where["lit_id"] = " = '$lit_id'";
        $where["sortie"] = "> '$date_end'";
        $where[]         = "DATE(sortie) = '$date'";

        $affectations = $affectation->loadList($where);

        return $this->_ref_next_affectation_same_bed = reset($affectations);
    }

    /**
     * Chargement du service de l'affectation
     *
     * @param bool $cache cache
     *
     * @return CService
     */
    function loadRefService($cache = true)
    {
        return $this->_ref_service = $this->loadFwdRef("service_id", $cache);
    }

    /**
     * @see parent::loadRefsFwd()
     */
    function loadRefsFwd($cache = true)
    {
        $this->loadRefLit($cache);
        $this->loadView();
        $this->loadRefSejour($cache);
        $this->loadRefsAffectations();
    }

    /**
     * Loads siblings (prev, next)
     *
     * @param bool $use_sejour Try to use sejour bounds to guess prev et next (mostly no prev nor next)
     *
     * @return void
     */
    function loadRefsAffectations($use_sejour = false)
    {
        $sejour = $this->_ref_sejour;

        $this->_ref_prev = new CAffectation();
        $guess_no_prev   = $use_sejour && $sejour && $this->entree == $sejour->entree;
        if (!$guess_no_prev) {
            $where = [
                "affectation_id" => "!= '$this->_id'",
                "sejour_id"      => "= '$this->sejour_id'",
                "sortie"         => "<= '$this->entree'",
            ];

            $this->_ref_prev->loadObject($where, "entree DESC");
            $this->_in_permission_sup_48h = CMbDT::hoursRelative($this->_ref_prev->entree, CMbDT::dateTime()) > 48;
        }

        $this->_ref_next = new CAffectation();
        $guess_no_next   = $use_sejour && $sejour && $this->sortie == $sejour->sortie;
        if (!$guess_no_next) {
            $where = [
                "affectation_id" => "!= '$this->_id'",
                "sejour_id"      => "= '$this->sejour_id'",
                "entree"         => ">= '$this->sortie'",
            ];

            $this->_ref_next->loadObject($where, "entree ASC");
        }
    }

    /**
     * Loads child affectations
     *
     * @return self[]
     */
    function loadRefsAffectationsEnfant()
    {
        return $this->_ref_affectations_enfant = $this->loadBackRefs("affectations_enfant");
    }

    /**
     * Count child affectations
     *
     * @param array $where Optional conditions
     *
     * @return int|null
     */
    function countRefsAffectationsEnfant($where = [])
    {
        return $this->_nb_affectations_enfant = $this->countBackRefs("affectations_enfant", $where);
    }

    /**
     * @see parent::getPerm()
     */
    function getPerm($permType)
    {
        // Gestion dans le cas des affectations de blocage de lits pour le service d'urgence
        if ($this->lit_id && $this->function_id) {
            $lit      = $this->loadRefLit();
            $fonction = $this->loadRefFunction();

            return $lit->getPerm($permType) && $fonction->getPerm($permType);
        }

        $sejour = $this->loadRefSejour();
        // Gestion dans le cas des affectations dans les couloirs (pas de lit_id)
        if (!$this->lit_id) {
            $service = $this->loadRefService();

            return $service->getPerm($permType) && $sejour->getPerm($permType);
        }
        $lit = $this->loadRefLit();

        return $lit->getPerm($permType) && ($sejour->_id ? $sejour->getPerm($permType) : true);
    }

    function checkDaysRelative($date)
    {
        if ($this->entree and $this->sortie) {
            $this->_entree_relative = CMbDT::daysRelative("$date 12:00:00", $this->entree);
            $this->_sortie_relative = CMbDT::daysRelative("$date 12:00:00", $this->sortie);
        }
    }

    /**
     * Tells if it collides with another affectation
     *
     * @param self $aff Other affectation
     *
     * @return bool
     */
    function collide($aff)
    {
        if ($this->_id && $aff->_id && $this->_id == $aff->_id) {
            return false;
        }

        return CMbRange::collides($this->entree, $this->sortie, $aff->entree, $aff->sortie);
    }

    function loadMenu($date, $listTypeRepas = null)
    {
        $this->_list_repas[$date] = [];
        $repas                    =& $this->_list_repas[$date];
        if (!$listTypeRepas) {
            $listTypeRepas = new CTypeRepas();
            $order         = "debut, fin, nom";
            $listTypeRepas = $listTypeRepas->loadList(null, $order);
        }

        $where                   = [];
        $where["date"]           = $this->_spec->ds->prepare(" = %", $date);
        $where["affectation_id"] = $this->_spec->ds->prepare(" = %", $this->_id);
        foreach ($listTypeRepas as $keyType => $typeRepas) {
            $where["typerepas_id"] = $this->_spec->ds->prepare("= %", $keyType);
            $repasDuJour           = new CRepas();
            $repasDuJour->loadObject($where);
            $repas[$keyType] = $repasDuJour;
        }
    }

    /**
     * @return self
     */
    function loadRefParentAffectation()
    {
        return $this->_ref_parent_affectation = $this->loadFwdRef("parent_affectation_id", true);
    }

    /**
     * @param bool $cache cache
     *
     * @return CMediusers
     */
    function loadRefPraticien($cache = true)
    {
        return $this->_ref_praticien = $this->loadFwdRef("praticien_id", $cache);
    }

    function makeUF()
    {
        $this->completeField(
            "lit_id",
            "uf_hebergement_id",
            "uf_soins_id",
            "uf_medicale_id",
            "praticien_id",
            "sortie",
            "entree"
        );
        $this->loadRefsAffectations();
        $this->loadRefLit()->loadRefChambre()->loadRefService();

        $lit     = $this->_ref_lit;
        $chambre = $lit->_ref_chambre;
        $service = $this->loadRefService(!$this->fieldModified("service_id"));
        $sejour  = $this->loadRefSejour();

        $affectations_ids  = $sejour->loadBackIds("affectations", "entree ASC");
        $first_affectation = !count($affectations_ids) || (reset($affectations_ids) == $this->_id);

        $use_uf_sejour_to_affectation = (CAppUI::gconf(
                "dPplanningOp CSejour use_uf_sejour_to_affectation"
            ) == "all" || $first_affectation) ? 1 : 0;

        if (!$this->praticien_id) {
            $this->_ref_sejour->loadRefCurrAffectation();
            $this->praticien_id = $sejour->_ref_curr_affectation->praticien_id ?: $sejour->praticien_id;
        }

        $prev_aff = $this->_ref_prev;
        $ljoin    = [
            "uf" => "uf.uf_id = affectation_uf.uf_id",
        ];

        $where   = [];
        $where[] = "uf.type_sejour IS NULL OR uf.type_sejour = '$sejour->type'";
        $where[] = "uf.date_debut IS NULL OR uf.date_debut < '" . CMbDT::date($this->sortie) . "'";
        $where[] = "uf.date_fin IS NULL OR uf.date_fin > '" . CMbDT::date($this->entree) . "'";

        if (!$this->uf_hebergement_id || $this->fieldModified("service_id") || $this->fieldModified("lit_id")) {
            $affectation_uf   = new CAffectationUniteFonctionnelle();
            $where["uf.type"] = "= 'hebergement'";

            if (!$affectation_uf->uf_id) {
                $where["object_id"]    = "= '$lit->_id'";
                $where["object_class"] = "= 'CLit'";
                $affectation_uf->loadObject($where, null, null, $ljoin);

                if (!$affectation_uf->_id) {
                    $where["object_id"]    = "= '$chambre->_id'";
                    $where["object_class"] = "= 'CChambre'";
                    $affectation_uf->loadObject($where, null, null, $ljoin);

                    if (!$affectation_uf->_id) {
                        $where["object_id"]    = "= '$service->_id'";
                        $where["object_class"] = "= 'CService'";
                        $affectation_uf->loadObject($where, null, null, $ljoin);
                    }
                }
            }

            $this->uf_hebergement_id = $affectation_uf->uf_id;
        }

        if (!$this->uf_soins_id || $this->fieldModified("service_id")) {
            $affectation_uf   = new CAffectationUniteFonctionnelle();
            $where["uf.type"] = "= 'soins'";

            if ($use_uf_sejour_to_affectation) {
                $affectation_uf->uf_id = $sejour->uf_soins_id;
            }

            if (!$affectation_uf->uf_id) {
                $where["object_id"]    = "= '$lit->_id'";
                $where["object_class"] = "= 'CLit'";
                $affectation_uf->loadObject($where, null, null, $ljoin);

                if (!$affectation_uf->uf_id) {
                    $where["object_id"]    = "= '$chambre->_id'";
                    $where["object_class"] = "= 'CChambre'";
                    $affectation_uf->loadObject($where, null, null, $ljoin);

                    if (!$affectation_uf->uf_id) {
                        $where["object_id"]    = "= '$service->_id'";
                        $where["object_class"] = "= 'CService'";
                        $affectation_uf->loadObject($where, null, null, $ljoin);
                    }
                }
            }

            $this->uf_soins_id = $affectation_uf->uf_id;
        }

        if (!$this->uf_medicale_id) {
            $affectation_uf   = new CAffectationUniteFonctionnelle();
            $where["uf.type"] = "= 'medicale'";

            if ($use_uf_sejour_to_affectation) {
                $affectation_uf->uf_id = $sejour->uf_medicale_id;
            }

            if ($prev_aff->_id && $prev_aff->uf_medicale_id) {
                $affectation_uf->uf_id = $prev_aff->uf_medicale_id;
            }

            if (!$affectation_uf->uf_id) {
                if (!$this->praticien_id) {
                    $praticien = $this->loadRefSejour()->loadRefPraticien();
                } else {
                    $praticien = $this->loadRefPraticien();
                    $praticien->loadRefFunction();
                }
                $where["object_id"]    = "= '$praticien->_id'";
                $where["object_class"] = "= 'CMediusers'";
                $affectation_uf->loadObject($where, null, null, $ljoin);

                if (!$affectation_uf->_id) {
                    $function              = $praticien->_ref_function;
                    $where["object_id"]    = "= '$function->_id'";
                    $where["object_class"] = "= 'CFunctions'";
                    $affectation_uf->loadObject($where, null, null, $ljoin);
                }
            }

            $this->uf_medicale_id = $affectation_uf->uf_id;
        }
    }

    /**
     * Chargement de l'ensemble des UFs de l'affectation
     *
     * @param bool $cache cache
     *
     * @return void
     */
    function loadRefUfs($cache = 1)
    {
        $this->loadRefUFHebergement($cache);
        $this->loadRefUFMedicale($cache);
        $this->loadRefUFSoins($cache);
    }

    /**
     * Chargement de l'UF d'hébergement
     *
     * @param bool $cache cache
     *
     * @return CUniteFonctionnelle
     */
    function loadRefUFHebergement($cache = true)
    {
        return $this->_ref_uf_hebergement = $this->loadFwdRef("uf_hebergement_id", $cache);
    }

    /**
     * Chargement de l'UF médicale
     *
     * @param bool $cache cache
     *
     * @return CUniteFonctionnelle
     */
    function loadRefUFMedicale($cache = true)
    {
        return $this->_ref_uf_medicale = $this->loadFwdRef("uf_medicale_id", $cache);
    }

    /**
     * Chargement de l'UF de soins
     *
     * @param bool $cache cache
     *
     * @return CUniteFonctionnelle
     */
    function loadRefUFSoins($cache = true)
    {
        return $this->_ref_uf_soins = $this->loadFwdRef("uf_soins_id", $cache);
    }

    /**
     * @return CUniteFonctionnelle[]
     */
    function getUFs()
    {
        $this->loadRefUfs();

        return [
            "hebergement" => $this->_ref_uf_hebergement,
            "medicale"    => $this->_ref_uf_medicale,
            "soins"       => $this->_ref_uf_soins,
        ];
    }

    function getMovementType()
    {
        $sejour = $this->loadRefSejour();

        return $sejour->getMovementType();
    }

    function isLast()
    {
        $this->loadRefsAffectations();

        return !$this->_ref_next->_id;
    }

    /**
     * Chargement des épisodes de soins
     *
     * @return CDIRHMEpisode[]
     */
    function loadRefsEpisodeSoins()
    {
        return $this->_ref_episodes = $this->loadBackRefs("episode_rhm");
    }

    /**
     * Vérifie la présence d'un patient mineur et d'un patient majeur dans la même chambre
     *
     * @param int          $lit_id      Lit dans lequel le patient va être placé
     * @param CAffectation $affectation Affectation concernée
     *
     * @return array
     */
    static function alertePlacement($lit_id, $affectation)
    {
        $alerte_patient_mineur = CAppUI::gconf("dPhospi placement alerte_patient_mineur");
        $alerte_sexe_opposes   = CAppUI::gconf("dPhospi placement alerte_sexe_opposes");

        $result = [
            "patient_mineur" => 0,
            "sexe_opposes"   => 0,
        ];

        if (!$alerte_patient_mineur && !$alerte_sexe_opposes) {
            return $result;
        }

        if (!$lit_id) {
            return $result;
        }

        $lit = new CLit();
        $lit->load($lit_id);

        $chambre = $lit->loadRefChambre();

        $lits_ids = $chambre->loadBackIds("lits");

        $other_affectation = new self();

        $where = [
            "lit_id" => CSQLDataSource::prepareIn($lits_ids),
            "entree" => "<= '$affectation->sortie'",
            "sortie" => ">= '$affectation->entree'",
        ];

        if ($affectation->_id) {
            $where["affectation_id"] = "!= '$affectation->_id'";
        }

        if (!$other_affectation->loadObject($where)) {
            return $result;
        }

        $sejour_1  = $affectation->loadRefSejour();
        $patient_1 = $sejour_1->loadRefPatient();

        $sejour_2  = $other_affectation->loadRefSejour();
        $patient_2 = $sejour_2->loadRefPatient();

        if ($alerte_patient_mineur) {
            $age_patient_1 = intval(CMbDT::daysRelative($patient_1->naissance, $sejour_1->entree) / 365.25);
            $age_patient_2 = intval(CMbDT::daysRelative($patient_2->naissance, $sejour_2->entree) / 365.25);

            $result["patient_mineur"] = (($age_patient_1 < 18 && $age_patient_2 >= 18) || ($age_patient_2 < 18 && $age_patient_1 >= 18));
        }

        if ($alerte_sexe_opposes) {
            $result["sexe_opposes"] = $patient_1->sexe !== $patient_2->sexe;
        }

        return $result;
    }

    /**
     * Récupère la liste des praticiens de l'affectation d'uf principale et secondaire
     *
     * @param int $uf_medicale_id Uf médicale
     *
     * @return CMediusers[]
     */
    static function loadPraticiensUfMedicale($uf_medicale_id)
    {
        $users        = [];
        $function_med = [];

        $where                         = [];
        $where["affectation_uf.uf_id"] = "= '" . $uf_medicale_id . "'";
        $where[]                       = "object_class = 'CMediusers' OR object_class = 'CFunctions'";
        /* @var CAffectationUniteFonctionnelle[] $affs */
        $aff_ufs = new CAffectationUniteFonctionnelle();
        $affs    = $aff_ufs->loadList($where);
        foreach ($affs as $_aff) {
            if ($_aff->object_class == "CMediusers") {
                $users[$_aff->object_id] = CMediusers::get($_aff->object_id);
            } elseif (!isset($function_med[$_aff->object_id])) {
                $user                           = new CMediusers();
                $function_med[$_aff->object_id] = $user->loadPraticiens(PERM_EDIT, $_aff->object_id);
            }
        }

        $where          = [];
        $where["uf_id"] = "= '" . $uf_medicale_id . "'";
        $where[]        = "object_class = 'CMediusers' OR object_class = 'CFunctions'";
        /* @var CAffectationUfSecondaire[] $affs_second */
        $aff_second_ufs = new CAffectationUfSecondaire();
        $affs_second    = $aff_second_ufs->loadList($where);
        foreach ($affs_second as $_aff) {
            if ($_aff->object_class == "CMediusers") {
                $users[$_aff->object_id] = CMediusers::get($_aff->object_id);
            } elseif (!isset($function_med[$_aff->object_id])) {
                $user                           = new CMediusers();
                $function_med[$_aff->object_id] = $user->loadPraticiens(PERM_EDIT, $_aff->object_id);
            }
        }

        foreach ($function_med as $users_by_fct) {
            foreach ($users_by_fct as $_user) {
                $users[$_user->_id] = $_user;
            }
        }

        return $users;
    }

    /**
     * Charge les mouvements de l'affectation
     *
     * @return CMovement[]
     */
    function loadRefsMovements()
    {
        return $this->_ref_movements = $this->loadBackRefs("movements", "start_of_movement DESC, last_update DESC");
    }

    function loadRefEtablissementTransfert()
    {
        return $this->_ref_etablissement_transfert = $this->loadFwdRef("etablissement_sortie_id");
    }

    function storePrestations()
    {
        $sejour       = $this->loadRefSejour();
        $lit          = $this->loadRefLit();
        $chambre      = $this->_ref_lit->loadRefChambre();
        $liaisons_lit = $lit->loadRefsLiaisonsItems();
        CStoredObject::massLoadFwdRef($liaisons_lit, "item_prestation_id");

        $where = [
            "sejour_id" => "= '$sejour->_id'",
        ];

        $filter_entree = CMbDT::date($this->entree);

        // Le patient est-il tout seul dans sa chambre ?
        // Pour le changement du réalisé du chambre double en chambre seule
        $lits_ids = $chambre->loadBackIds('lits');

        $aff      = new self();
        $whereAff = [
            "affectation.affectation_id" => "!= '$this->_id'",
            "lit_id"                     => CSQLDataSource::prepareIn($lits_ids),
            "affectation.entree"         => "< '$this->sortie'",
            "affectation.sortie"         => "> '$this->entree'",
        ];

        $count_aff = $aff->countList($whereAff);

        /** @var CLitLiaisonItem $_liaison */
        foreach ($liaisons_lit as $_liaison) {
            $item_liaison = new CItemLiaison();
            $_item        = $_liaison->loadRefItemPrestation();

            if (!$_item->actif) {
                continue;
            }

            // Recherche d'une liaison
            $where["prestation_id"] = "= '$_item->object_id'";
            $where["date"]          = "= '" . $filter_entree . "'";
            $item_liaison->loadObject($where);

            // Si existante, alors on affecte le réalisé au niveau de prestation du lit
            if ($item_liaison->_id) {
                $item_liaison->item_realise_id = $_liaison->item_prestation_id;
            } // Sinon création d'une liaison
            else {
                $item_liaison->sejour_id       = $sejour->_id;
                $item_liaison->date            = $filter_entree;
                $item_liaison->quantite        = 0;
                $item_liaison->item_realise_id = $_liaison->item_prestation_id;
                $item_liaison->prestation_id   = $_item->object_id;

                // Recherche d'une précédente liaison pour appliquer l'item souhaité s'il existe
                $where["date"]         = "<= '" . CMbDT::date($this->entree) . "'";
                $_item_liaison_souhait = new CItemLiaison();
                $_item_liaison_souhait->loadObject($where, "date DESC");

                if ($_item_liaison_souhait->_id) {
                    $item_liaison->item_souhait_id = $_item_liaison_souhait->item_souhait_id;
                    $item_liaison->sous_item_id    = $_item_liaison_souhait->sous_item_id;
                }
            }

            // Si le patient est tout seul dans une chambre double, alors qu'il a demandé
            // une chambre seule, le réalisé devient la chambre seule
            if (!$count_aff) {
                $item_liaison->loadRefItemRealise();
                if ($item_liaison->_ref_item_realise->chambre_double && $item_liaison->_ref_item_realise->chambre_part_id) {
                    $item_liaison->item_realise_id = $item_liaison->_ref_item_realise->chambre_part_id;
                }
            }

            if ($msg = $item_liaison->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }

            // Dans tous les cas, il faut parcourir les liaisons existantes entre les dates de début et fin de l'affectation
            $where["date"]       = "BETWEEN '" . $filter_entree . "' AND '" . CMbDT::date($this->sortie) . "'";
            $liaisons_existantes = $item_liaison->loadList($where, null, null, "item_liaison.item_liaison_id");

            foreach ($liaisons_existantes as $_liaison_existante) {
                $_liaison_existante->item_realise_id = $_liaison->item_prestation_id;

                if (!$count_aff) {
                    $_liaison_existante->loadRefItemRealise();
                    if ($_liaison_existante->_ref_item_realise->chambre_double && $_liaison_existante->_ref_item_realise->chambre_part_id) {
                        $_liaison_existante->item_realise_id = $_liaison_existante->_ref_item_realise->chambre_part_id;
                    }
                }

                if ($msg = $_liaison_existante->store()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                }
            }
        }
    }

    /**
     * Load the CModeEntreeSejour
     *
     * @return CModeEntreeSejour
     */
    public function loadRefModeEntree(): CModeEntreeSejour
    {
        return $this->_ref_mode_entree = $this->loadFwdRef("mode_entree_id", true);
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchAffectation($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return CGroups
     */
    public function loadRelGroup(): CGroups
    {
        return $this->loadRefService()->loadRefGroup();
    }
}

