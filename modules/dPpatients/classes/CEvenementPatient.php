<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DateTime;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFacturable;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Notifications\CNotification;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Snomed\CSnomed;
use Ox\Tamm\Cabinet\CAppelSIH;
use Ox\Tamm\Cabinet\TAMMSIHFields;

/**
 * Evenements importants passés et à venir des patients
 */
class CEvenementPatient extends CFacturable implements ImportableInterface, IGroupRelated
{
    // DB Table key
    public $evenement_patient_id;

    // DB fields
    public $date;
    public $libelle;
    public $description;
    public $praticien_id;
    public $dossier_medical_id;
    public $type_evenement_patient_id;
    public $owner_id;
    public $creation_date;
    public $valide;
    public $rappel;
    public $alerter;
    public $regle_id;
    public $traitement_user_id;
    public $type;
    public $parent_id;
    public $cancel;
    public $date_fin_operation;

    /** @var CMediusers */
    public $_ref_praticien;
    /** @var CDossierMedical */
    public $_ref_dossier_medical;
    /** @var CTypeEvenementPatient */
    public $_ref_type_evenement_patient;
    /** @var  CMediusers */
    public $_ref_owner;
    /** @var  CReglement[] */
    public $_ref_reglements;
    /** @var CNotification */
    public $_ref_notification;
    /** @var CRegleAlertePatient */
    public $_ref_regle_alerte;
    /** @var CPatient */
    public $_ref_patient;

    public $_ref_users_evt;
    public $_ref_users = [];

    /** @var CLoinc[] */
    public $_ref_codes_loinc;
    /** @var CSnomed[] */
    public $_ref_codes_snomed;

    /** @var CIdSante400 - tag can be: COperation-1, CSejour-2 ... */
    public $_ref_context_id400;
    /** @var CIdSante400 - the distant sih id */
    public $_ref_sih_id400;
    /** @var CIdSante400 */
    public $_ref_cabinet_id400;

    /** @var CPatientEventSentMail[] */
    public $_refs_sent_mail;

    /** @var CEvenementPatient */
    public $_ref_parent;
    /** @var CEvenementPatient */
    public $_ref_child;
    public $_type_sih;

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'evenement_patient';
        $spec->key   = 'evenement_patient_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                              = parent::getProps();
        $props["date"]                      = "dateTime notNull";
        $props["date_fin_operation"]        = "dateTime";
        $props["libelle"]                   = "str notNull";
        $props["description"]               = "text helped";
        $props["praticien_id"]              = "ref class|CMediusers back|evenements_patient";
        $props["dossier_medical_id"]        = "ref notNull class|CDossierMedical show|0 back|evenements_patient";
        $props["type_evenement_patient_id"] = "ref class|CTypeEvenementPatient back|evenements_patient";
        $props["consult_related_id"]        = " back|evenements_patient";
        $props["owner_id"]                  = "ref notNull class|CMediusers back|evenements_patient_owner";
        $props["creation_date"]             = "dateTime notNull";
        $props["valide"]                    = "bool show|0";
        $props["rappel"]                    = "bool default|0 show|0";
        $props["alerter"]                   = "bool default|0";
        $props["regle_id"]                  = "ref class|CRegleAlertePatient back|regle_alerte_patient";
        $props["traitement_user_id"]        = "ref class|CMediusers back|alert_traite_user";
        $props["type"]                      = "enum list|evt|sejour|intervention default|evt";
        $props["parent_id"]                 = "ref class|CEvenementPatient back|evt_parent cascade";
        $props["cancel"]                    = "bool default|0";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->getActeExecution();
        $this->_view = $this->libelle;
        // si _coded vaut 1 alors, impossible de modifier la cotation
        $this->_coded = $this->valide;
    }

     /**
     * @see parent::getPerm()
     */
    function getPerm($permType)
    {
        $perm = true;
        if ($this->_id) {
            $perm = $this->praticien_id ? $this->loadRefPraticien()->getPerm($permType)
                : $this->loadRefPatient()->getPerm($permType);
        }

        return $perm && parent::getPerm($permType);
    }

    /**
     * Charge le praticien
     *
     * @return CMediusers
     */
    public function loadRefPraticien(bool $cache = true): ?CMediusers
    {
        $this->_ref_praticien = $this->loadFwdRef("praticien_id");
        $this->_praticien_id  = $this->praticien_id;
        $this->_ref_executant = $this->_ref_praticien;
        $this->_ref_praticien->loadRefFunction();

        return $this->_ref_praticien;
    }

    /**
     * Loads the alert rule
     *
     * @return CRegleAlertePatient
     */
    function loadRefAlerte()
    {
        return $this->_ref_regle_alerte = $this->loadFwdRef("regle_id");
    }

    /**
     * Loads the evenement parent
     *
     * @return CEvenementPatient
     */
    function loadRefParent()
    {
        return $this->_ref_parent = $this->loadFwdRef("parent_id");
    }

    /**
     * @return CEvenementPatient
     * @throws Exception
     */
    public function loadRefChild()
    {
        return $this->_ref_child = $this->loadUniqueBackRef('evt_parent');
    }

    /**
     * Charge le dossier médical
     *
     * @return CDossierMedical
     */
    function loadRefDossierMedical()
    {
        return $this->_ref_dossier_medical = $this->loadFwdRef("dossier_medical_id");
    }

    /**
     * Charge le patient relié
     *
     * @return CPatient
     */
    public function loadRefPatient(bool $cache = true): ?CPatient
    {
        $this->loadRefDossierMedical();
        $this->_ref_dossier_medical->loadRefObject();

        return $this->_ref_patient = $this->_ref_dossier_medical->_ref_object;
    }

    /**
     * Charge le type d'évènement
     *
     * @return CTypeEvenementPatient
     */
    function loadRefTypeEvenementPatient()
    {
        return $this->_ref_type_evenement_patient = $this->loadFwdRef("type_evenement_patient_id");
    }

    /**
     * Charge le owner
     *
     * @return CMediusers
     */
    function loadRefOwner()
    {
        return $this->_ref_owner = $this->loadFwdRef("owner_id");
    }


    /**
     * @inheritdoc
     */
    function loadAllDocs($params = [])
    {
        $this->mapDocs($this, $params);
    }

    /**
     * @see parent::getTemplateClasses()
     */
    function getTemplateClasses()
    {
        $tab = [];

        // Stockage des objects liés à l'évènement
        $tab['CEvenementPatient'] = $this->_id;
        $tab['CDossierMedical']   = $this->_ref_dossier_medical->_id;

        return $tab;
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template)
    {
        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $this->loadRefTypeEvenementPatient();

        if (CMbDT::time($this->date) === "00:00:00") {
            $template->addLongDateProperty("Evenement - " . CAppUI::tr('common-Long date'), $this->date);
            $template->addDateProperty("Evenement - " . CAppUI::tr('common-Date'), $this->date);
        } else {
            $date_longue = ucfirst(CMbDT::format($this->date, CAppUI::conf("longdate")));
            $time        = CMbDT::format($this->date, CAppUI::conf("time"));
            $template->addDateTimeProperty("Evenement - " . CAppUI::tr('common-Date'), $this->date);
            $template->addProperty("Evenement - " . CAppUI::tr('common-Long date'), $date_longue . " " . $time);
        }
        $template->addProperty("Evenement - Libellé", $this->libelle);
        $template->addProperty("Evenement - Description", $this->description);
        $template->addProperty("Evenement - Type", $this->_ref_type_evenement_patient->libelle);

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }

    /**
     * @see parent::fillTemplate()
     */
    function fillTemplate(&$template)
    {
        $this->fillLimitedTemplate($template);

        // Dossier médical
        $this->loadRefDossierMedical()->fillTemplate($template, "Patient");

        // Patient
        $patient = new CPatient();
        if ($this->_ref_dossier_medical->object_class == "CPatient" && $this->_ref_dossier_medical->object_id) {
            $this->_ref_dossier_medical->loadRefObject();
            $patient = $this->_ref_dossier_medical->_ref_object;
        }
        $patient->fillLimitedTemplate($template);

        $praticien = $this->loadRefPraticien();
        $praticien->fillTemplate($template);

        if (CModule::getActive('oxCabinet')) {
            (new TAMMSIHFields($this))->fillFields($template);
        }
    }

    /**
     * @see parent::loadView()
     */
    public function loadView(): void
    {
        parent::loadView();
        $this->loadRefDossierMedical();
        $this->loadRefsCodesLoinc();
        $this->loadRefsCodesSnomed();
        $this->loadRefPatient();
        $this->loadRefsId400SIH();

        //Retrieve the type of SIH called for a synchronized event
        if ($this->_ref_sih_id400) {
            $sih_id    = $this->_ref_sih_id400->id400;
            $appel_sih = new CAppelSIH();
            $curr_user = CMediusers::get();
            if ($curr_user->rpps) {
                try {
                    $token = $appel_sih->getAccessCabinet($curr_user->rpps);
                } catch (CMbException $e) {
                    $token = null;
                }
                if (is_array($token) && isset($token['list_sih']) && isset($token['list_sih'][$sih_id])) {
                    $sih             = $token['list_sih'][$sih_id];
                    $this->_type_sih = $sih['type'];
                }
            }
        }
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->completeField("dossier_medical_id", "praticien_id");
        // Save owner and creation date
        if (!$this->_id) {
            if (!$this->creation_date) {
                $this->creation_date = CMbDT::dateTime();
            }

            if (!$this->owner_id) {
                $this->owner_id = CMediusers::get()->_id;
            }
        }

        // Gestion du tarif et precodage des actes
        if ($this->_bind_tarif && $this->_id) {
            $this->getActeExecution();
            if ($msg = $this->bindTarif()) {
                return $msg;
            }
        }

        //Lors de la validation , enregistrement de la facture
        if ($this->fieldModified("valide", "1")) {
            if ($msg = CFacture::save($this)) {
                echo $msg;
            }
        }

        //Lors de dévalidation de l'évènement
        if ($this->_id && $this->fieldModified("valide", "0")) {
            $reglements = $this->loadRefFacture()->loadRefsReglements();
            if (!count($reglements)) {
                /* Annulation de l'ensemble des factures de l'événement
                 * Il peut y en avoir plusieurs d'actives en même temps (ex des factures n°x de frais divers) */
                foreach ($this->_ref_factures as $_facture) {
                    $_facture->cancelFacture($this);
                }
            } else {
                return "Vous ne pouvez pas réouvrir un évènement ayant des règlements";
            }
        }

        return parent::store();
    }

    /**
     * Calcul de la date d'execution de l'acte
     *
     * @return dateTime
     */
    public function getActeExecution(): string
    {
        parent::getActeExecution();
        $this->_datetime = $this->_acte_execution;
        $this->_date     = CMbDT::date($this->_acte_execution);

        return $this->_acte_execution;
    }

    /**
     * Récupération de l'executant d'une activité donnée
     *
     * @param int $code_activite Code de l'activité
     *
     * @return int|null Id de l'executant
     */
    public function getExecutantId(string $code_activite = null): ?int
    {
        return $this->praticien_id;
    }

    /**
     * Load the linked notification object
     *
     * @return CNotification
     */
    public function loadRefNotification()
    {
        if (CModule::getActive('notifications')) {
            $this->_ref_notification = $this->loadUniqueBackRef('context_notifications');
        }

        return $this->_ref_notification;
    }

    /**
     * Chargement utilisateurs associés à l'alerte
     *
     * @return CMediusers[]|null
     */
    function loadRefsUsers($sort = true)
    {
        $this->_ref_users_evt = $this->loadBackRefs("users_alert_evt");
        foreach ($this->_ref_users_evt as $_user_evt) {
            /* @var CEvenementAlerteUser $_user_evt */
            $user = $_user_evt->loadRefUser();
            $user->loadRefFunction();
            $this->_ref_users[$user->_id] = $user;
        }
        if ($sort) {
            $order_view = CMbArray::pluck($this->_ref_users, "_view");
            array_multisort($order_view, SORT_ASC, $this->_ref_users);
        }

        return $this->_ref_users;
    }

    /**
     * Compteur des événements à alerter en retard
     *
     * @return int
     */
    static function countRetardsAlerteUser()
    {
        $ljoin                                 = [];
        $ljoin["evenement_alert_user"]         = "evenement_alert_user.object_id = evenement_patient.evenement_patient_id
        AND evenement_alert_user.object_class = 'CEvenementPatient'";
        $where                                 = [];
        $where["alerter"]                      = " = '1'";
        $where["evenement_alert_user.user_id"] = " = '" . CMediusers::get()->_id . "'";
        $where["traitement_user_id"]           = "IS NULL";
        $evt                                   = new self();

        return $evt->countList($where, null, $ljoin);
    }

    /**
     * Counts late events in a patient file
     *
     * @param CPatient $patient
     *
     * @return int
     * @throws Exception
     */
    static function countAlertePatient(CPatient $patient)
    {
        $where                       = [];
        $where["dossier_medical_id"] = " = '" . $patient->loadRefDossierMedical()->dossier_medical_id . "'";
        $where["alerter"]            = " = '1'";
        $where["traitement_user_id"] = "IS NULL";
        $evt                         = new self();

        return $evt->countList($where);
    }

    /**
     * Counts events te be reminded
     *
     * @param CPatient $patient
     *
     * @return int
     * @throws Exception
     */
    static function countRemindersPatient($patient)
    {
        $where                       = [];
        $where["rappel"]             = " = '1'";
        $where["dossier_medical_id"] = " = '" . $patient->loadRefDossierMedical()->dossier_medical_id . "'";
        $where["traitement_user_id"] = "IS NULL";
        $evt                         = new self();

        return $evt->countList($where);
    }

    static function countNbUntreatedEvts()
    {
        $where                       = [];
        $where["rappel"]             = " = '1'";
        $where["praticien_id"]       = " = '" . CMediusers::get()->_id . "'";
        $where["traitement_user_id"] = "IS NULL";
        $evt                         = new self();

        return $evt->countList($where);
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        return (!$prat_ids || in_array($this->praticien_id, $prat_ids)) && ((!$date_min && !$date_max)
                || ($date_max && $this->creation_date <= $date_max) || ($date_min && $this->creation_date >= $date_min));
    }

    /**
     * Return idex type if it's special
     *
     * @param CIdSante400 $idex Idex
     *
     * @return string|null
     */
    function getSpecialIdex(CIdSante400 $idex)
    {
        if (CModule::getActive('snomed') && ($idex->tag == CSnomed::getSnomedTag())) {
            return "SNOMED";
        }

        if (CModule::getActive('loinc') && ($idex->tag == CLoinc::getLoincTag())) {
            return "LOINC";
        }

        return null;
    }

    /**
     * Get all CLoinc[] backrefs
     *
     * @return CLoinc[]|null
     */
    function loadRefsCodesLoinc()
    {
        if (!CModule::getActive('loinc')) {
            return null;
        }

        $codes_loinc = [];

        $idex = new CIdSante400();
        $idex->setObject($this);
        $idex->tag = CLoinc::getLoincTag();
        $idexes    = $idex->loadMatchingList();

        foreach ($idexes as $_idex) {
            $loinc = new CLoinc();
            $loinc->load($_idex->id400);

            $codes_loinc[$loinc->_id] = $loinc;
        }

        return $this->_ref_codes_loinc = $codes_loinc;
    }

    /**
     * Get all CSnomed[] backrefs
     *
     * @return CSnomed[]|null
     */
    function loadRefsCodesSnomed()
    {
        if (!CModule::getActive('snomed')) {
            return null;
        }

        $codes_snomed = [];

        $idex = new CIdSante400();
        $idex->setObject($this);
        $idex->tag = CSnomed::getSnomedTag();
        $idexes    = $idex->loadMatchingList();

        foreach ($idexes as $_idex) {
            $snomed = new CSnomed();
            $snomed->load($_idex->id400);

            $codes_snomed[$snomed->_id] = $snomed;
        }

        return $this->_ref_codes_snomed = $codes_snomed;
    }

    /**
     * Gets the context id of the stay/surgery ... (tamm-sih)
     *
     * @return CIdSante400
     */
    public function loadRefsId400SIH()
    {
        if ($this->type == "evt") {
            return null;
        }
        $this->_ref_context_id400 = $this->loadLastId400("context_guid_sih");
        $this->_ref_sih_id400     = $this->loadLastId400("sih_id");
        $this->_ref_cabinet_id400 = $this->loadLastId400("cabinet_id");
    }

    /**
     * @return CPatientEventSentMail|null
     * @throws Exception
     */
    public function loadRefSentMail()
    {
        return $this->_refs_sent_mail = $this->loadBackRefs("event_sent_mail");
    }

    /**
     * @param $object_guid
     * @param $cabinet_id
     * @param $sih_id
     *
     * @throws Exception
     */
    public function createTagsSIH($object_guid, $cabinet_id, $sih_id)
    {
        $id400 = new CIdSante400();
        $id400->setObject($this);
        $id400->tag   = "context_guid_sih";
        $id400->id400 = $object_guid;
        $id400->store();

        $id400->_id   = null;
        $id400->tag   = "cabinet_id";
        $id400->id400 = $cabinet_id;
        $id400->store();

        $id400->_id   = null;
        $id400->tag   = "sih_id";
        $id400->id400 = $sih_id;
        $id400->store();
    }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchEvenementPatient($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * Display additionnal event info
     *
     * @param CEvenementPatient $event
     *
     * @return string
     */
    public static function viewTemplate(CEvenementPatient $event): string
    {
        $praticien = $event->loadRefPraticien();
        $type      = $event->loadRefTypeEvenementPatient();

        return $event->date
            . ' <br/> Praticien prescripteur : ' . $praticien->_view
            . ' <br/> Libelle : ' . $event->libelle
            . ' <br/> Type : ' . $type->libelle;
    }

    /**
     * @return CGroups
     */
    public function loadRelGroup(): CGroups
    {
        return $this->loadRefOwner()->loadRefFunction()->loadRefGroup();
    }
}
