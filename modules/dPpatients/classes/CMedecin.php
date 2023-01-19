<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\AppFine\Server\Appointment\CContactPlace;
use Ox\AppFine\Server\Appointment\CHonoraryPlace;
use Ox\AppFine\Server\Appointment\CInformationTarifPlace;
use Ox\AppFine\Server\Appointment\CPresentation;
use Ox\AppFine\Server\Appointment\CSchedulePlace;
use Ox\AppFine\Server\Appointment\CTemporaryInformation;
use Ox\AppFine\Server\CAppFineAppointment;
use Ox\AppFine\Server\CAppFineMotifConsult;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CPerson;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CMbvCardExport;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\System\CGeoLocalisation;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\IGeocodable;

/**
 * The CMedecin Class
 */
class CMedecin extends CPerson implements IGeocodable, ImportableInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'doctor';

    /** @var string */
    public const FIELDSET_CONTACT = "contact";

    /** @var string */
    public const FIELDSET_IDENTIFIER = "identifier";

    /** @var string */
    public const FIELDSET_SPECIALITY = "speciality";

    /** @var string */
    public const FIELDSET_APPOINTMENT = "appointment";

    /** @var string */
    public const RELATION_APPOINTMENTS = "appointments";

    /** @var string */
    public const RELATION_MOTIFS = "motifs";

    public const RELATION_EXERCICE_PLACE = 'exercicePlace';

    /** @var string */
    public const RELATION_MEDECIN_EXERCICE_PLACE = 'medecinExercicePlace';

    /** @var string */
    public const RELATION_PRESENTATION = 'presentation';

    /** @var string */
    public const RELATION_PRESENTATION_PICTURE = 'presentationPicture';

    /** @var string */
    public const RELATION_TEMPORARY_INFORMATION = 'temporaryInformation';

    /** @var string */
    public const RELATION_SCHEDULE_PLACE = 'schedulePlace';

    /** @var string */
    public const RELATION_CONTACT_PLACE = 'contactPlace';

    /** @var string */
    public const RELATION_HONORARY_PLACE = 'honoraryPlace';

    /** @var string */
    public const RELATION_INFORMATION_TARIF_PLACE = 'informationTarifPlace';

    /** @var string */
    public const RELATION_APPFINE_MOTIF_CONSULT = 'appFineMotifConsult';

    /** @var string */
    public const RELATION_APPFINE_APPOINTMENT = 'appFineAppointment';

    /** @var string */
    public const OID_IDENTIFIER_NATIONAL = '1.2.250.1.71.4.2.1';

    // DB Table key
    public static $types = [
        10 => 'medecin', // Médecin
        21 => 'pharmacie', // Pharmacie
        26 => 'audio', // Audioprothésiste
        28 => 'opticien', // Opticien-Lunetier
        31 => 'assistant_dent', // Assistant dentaire
        40 => 'dentiste', // Chirurgien dentiste
        41 => 'assistant_service_social', // Assistant de service social
        50 => 'sagefemme', // Sage femme
        60 => 'infirmier', // Infirmier
        69 => 'infirmierpsy', // Infirmier psychiatrique
        70 => 'kine', // Masseur-Kinésithérapeute
        71 => 'osteo', // Ostéopathe
        72 => 'psychotherapeute', // Psychothérapeute
        73 => 'chiro', // Chiropracteur
        80 => 'podologue', // Pédicure-podologue
        81 => 'orthoprot', // Orthoprothésiste
        82 => 'podoorth', // Podo-orthésiste
        83 => 'ortho', // Orthopédiste-orthésiste
        84 => 'oculariste', // Oculariste
        85 => 'epithesiste', // Épithésiste
        86 => 'technicien', // Technicien de laboratoire médical
        91 => 'orthophoniste', // Orthophoniste
        92 => 'orthoptiste', // Orthoptiste
        93 => 'psychologue', // Psychologue
        94 => 'ergo', // Érgothérapeute
        95 => 'diete', // Diététicien
        96 => 'psycho', // Psychomotricien
        98 => 'maniperm', // Manipulateur ERM
    ];

    // Owner
    public $medecin_id;
    public $function_id;
    public $group_id;

    // DB Fields
    public $spec_cpam_id;
    public $nom;
    public $prenom;
    public $full_name;
    public $medecin_fictif;
    public $jeunefille;
    public $sexe;
    public $actif;
    /** @var string Practitioner title */
    public $titre;
    public $adresse;
    public $ville;
    public $cp;
    public $tel;
    public $tel_autre;
    public $fax;
    public $portable;
    public $email;
    public $disciplines;
    public $orientations;
    public $complementaires;
    public $type;
    public $adeli;
    public $rpps;
    public $email_apicrypt;
    public $mssante_address;
    public $last_ldap_checkout;
    public $ignore_import_rpps;
    public $import_file_version;
    public $modalite_publipostage;
    public $ean;
    public $categorie_professionnelle;

    // AppFine Prise RDV
    public $mode_exercice;
    public $use_online_appointment_booking;
    public $authorize_booking_new_patient;
    public $authorize_teleconsultation;

    // form fields
    /** @var integer Allow to link a Mediuser with a CMedecin */
    public $user_id;
    public $_titre_long;
    /** @var string Current user starting formula */
    public $_starting_formula;
    /** @var string Current user closing formula */
    public $_closing_formula;

    // Object References
    public $_tutoiement;

    // Medecin exercices place
    public $_mep_disciplines;
    public $_mep_tel;
    public $_mep_tel2;
    public $_mep_adresse;
    public $_mep_fax;
    public $_mep_cp;
    public $_mep_ville;
    public $_mep_email;
    public $_mep_adeli;
    public $_mep_mssante_emails = [];

    // Calculated fields
    /** @var CPatient[] */
    public $_ref_patients;
    public $_count_patients_traites;
    public $_count_patients_correspondants;
    public $_has_siblings;
    public $_confraternite;
    /** @var string Practitioner long view (with title in full text) */
    public $_longview;

    /** @var CFunctions */
    public $_ref_function;

    /** @var CSpecCPAM */
    public $_ref_spec_cpam;

    /** @var CMediusers */
    public $_ref_user;

    /** @var CGeoLocalisation */
    public $_ref_geolocalisation;
    public $_is_importing = false;

    /** @var CMedecinExercicePlace Le lieu d'exercice préféré (ou sélectionné) */
    public $_medecin_exercice_place;
    /** @var CMedecinExercicePlace[] */
    public $_ref_medecin_exercice_places = [];

    /** @var CExercicePlace[] */
    public $_ref_exercice_places = [];
    /** @var bool */
    public $_is_praticien;

    /**
     * @inheritdoc
     */
    static function isGeocodable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'medecin';
        $spec->key   = 'medecin_id';
        $spec->seek  = 'match';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["function_id"]  = "ref class|CFunctions back|medecins_function";
        $props["group_id"]     = "ref class|CGroups back|medecins";
        $props["spec_cpam_id"] = "num fieldset|speciality";
        $props["nom"]          = "str notNull confidential seekable fieldset|default";
        $props["prenom"]       = "str seekable fieldset|default";
        $props["jeunefille"]   = "str confidential fieldset|default";
        $props["sexe"]         = "enum list|u|f|m default|u fieldset|default";
        $props["actif"]        = "bool default|1";
        $props["titre"]        = "enum list|m|mme|dr|pr fieldset|default";
        $props["adresse"]      = "text confidential fieldset|contact";
        $props["ville"]        = "str confidential fieldset|contact";
        [$min_cp, $max_cp] = CPatient::getLimitCharCP();
        $props["cp"]                             = "str minLength|$min_cp maxLength|$max_cp confidential fieldset|contact";
        $props["tel"]                            = "phone confidential fieldset|contact";
        $props["tel_autre"]                      = "str maxLength|20 fieldset|contact";
        $props["fax"]                            = "phone confidential fieldset|contact";
        $props["portable"]                       = "phone confidential fieldset|contact";
        $props["email"]                          = "str confidential fieldset|contact";
        $props["disciplines"]                    = "text fieldset|speciality";
        $props["orientations"]                   = "text fieldset|speciality";
        $props["complementaires"]                = "text fieldset|speciality";
        $props["type"]                           = "enum list|" . implode(
                '|',
                self::$types
            ) . "|pharmacie|maison_medicale|autre default|medecin fieldset|speciality";
        $props["adeli"]                          = "code confidential mask|9*S*S99999S9 adeli fieldset|identifier";
        $props["rpps"]                           = "numchar length|11 confidential mask|99999999999 control|luhn fieldset|identifier";
        $props["email_apicrypt"]                 = "email confidential fieldset|contact";
        $props['mssante_address']                = 'email confidential fieldset|contact';
        $props["last_ldap_checkout"]             = "date";
        $props["ignore_import_rpps"]             = "bool default|0";
        $props["import_file_version"]            = "str loggable|0";
        $props['user_id']                        = 'ref class|CMediusers nullify back|medecin';
        $props["modalite_publipostage"]          = "enum list|apicrypt|docapost|mail|mssante fieldset|contact";
        $props["ean"]                            = "str fieldset|identifier";
        $props["categorie_professionnelle"]      = "enum list|civil|militaire|etudiant default|civil fieldset|speciality";
        $props["mode_exercice"]                  = "enum list|liberal|salarie|benevole default|liberal fieldset|speciality";
        $props["use_online_appointment_booking"] = "bool fieldset|appointment";
        $props["authorize_booking_new_patient"]  = "bool fieldset|appointment";
        $props["authorize_teleconsultation"]     = "bool default|0 fieldset|appointment";
        $props["full_name"]                      = "str";
        $props["medecin_fictif"]                 = "bool default|0";

        $props["_starting_formula"] = "str";
        $props["_closing_formula"]  = "str";

        return $props;
    }

    /**
     * @see parent::loadView()
     */
    public function loadView(): void
    {
        parent::loadView();

        $this->mapMedecinExercicesPaces();
    }

    /**
     * Check all properties according to specification
     *
     * @return string|null Store-like message, null when no problem
     * @throws Exception
     */
    function check(): ?string
    {
        if (CModule::getActive('appFine') || CModule::getActive('appFineClient')) {
            // We authorize to store fictif RPPS
            if ($this->rpps && self::isFictifRPPS($this->rpps)) {
                return null;
            } else {
                return parent::check();
            }
        } else {
            return parent::check();
        }
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        // Création d'un correspondant en mode cabinets distincts
        if (!$this->_id && !$this->isImporting()) {
            if (CAppUI::isCabinet()) {
                $this->function_id = CMediusers::get()->function_id;
            } elseif (CAppUI::isGroup()) {
                $this->group_id = CMediusers::get()->loadRefFunction()->group_id;
            }
        }

        // update full_name when nom or prenom is update
        $full_name = $this->nom . ($this->prenom ? ' ' . $this->prenom : '');
        if ($full_name && $this->full_name !== $full_name) {
            $this->full_name = $full_name;
        }

        if ($this->fieldModified("ignore_import_rpps")) {
            if ($this->ignore_import_rpps) {
                $this->setAnnuleExercicePlace(1);
            } else {
                $this->setAnnuleExercicePlace(0);
            }
        }
        return parent::store();
    }

    /**
     * @throws Exception
     */
    private function setAnnuleExercicePlace(bool $annule): void
    {
        $this->getMedecinExercicePlaces();
        foreach ($this->_ref_medecin_exercice_places as $_medecin_exercice_place) {
            $_medecin_exercice_place->loadRefExercicePlace();
            $_medecin_exercice_place->annule                      = $annule;
            $_medecin_exercice_place->_ref_exercice_place->annule = $annule;
            $_medecin_exercice_place->store();
            $_medecin_exercice_place->_ref_exercice_place->store();
        }
    }

    /**
     * @return bool
     */
    function isImporting()
    {
        return $this->_is_importing;
    }

    /**
     * Compte les patients attachés
     *
     * @return void
     */
    function countPatients()
    {
        $this->_count_patients_traites        = $this->countBackRefs("patients_traites");
        $this->_count_patients_correspondants = $this->countBackRefs("patients_correspondants");
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        $this->nom    = CMbString::upper($this->nom);
        $this->prenom = CMbString::capitalize(CMbString::lower($this->prenom));

        $this->mapPerson();
        parent::updateFormFields();

        $this->_shortview = "{$this->nom} {$this->prenom}";
        $this->_view      = "{$this->nom} {$this->prenom}";
        $this->_longview  = "{$this->nom} {$this->prenom}";

        if ($this->type == "medecin") {
            $this->_confraternite = $this->sexe == "f" ? "Chère consoeur" : "Cher confrère";

            if (!$this->titre) {
                $this->_view     = CAppUI::tr("CMedecin.titre.dr") . " {$this->nom} {$this->prenom}";
                $this->_longview = CAppUI::tr("CMedecin.titre.dr-long") . " {$this->nom} {$this->prenom}";
            }
        }

        if ($this->titre) {
            $this->_view       = CAppUI::tr("CMedecin.titre.{$this->titre}") . " {$this->_view}";
            $this->_titre_long = CAppUI::tr("CMedecin.titre.{$this->titre}-long");
            $this->_longview   = "{$this->_titre_long} {$this->nom} {$this->prenom}";
        }

        if ($this->type && $this->type != 'medecin') {
            $this->_view     .= " (" . CMbArray::get($this->_specs['type']->_locales, $this->type) . ")";
            $this->_longview .= " (" . CMbArray::get($this->_specs['type']->_locales, $this->type) . ")";
        }
    }

    /**
     * Map the class variable with CPerson variable
     *
     * @return void
     */
    function mapPerson()
    {
        $this->_p_city                = $this->ville;
        $this->_p_postal_code         = $this->cp;
        $this->_p_street_address      = $this->adresse;
        $this->_p_phone_number        = $this->tel;
        $this->_p_fax_number          = $this->fax;
        $this->_p_mobile_phone_number = $this->portable;
        $this->_p_email               = $this->email;
        $this->_p_first_name          = $this->prenom;
        $this->_p_last_name           = $this->nom;
        $this->_p_maiden_name         = $this->jeunefille;
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();

        if ($this->nom) {
            $this->nom = CMbString::upper($this->nom);
        }
        if ($this->prenom) {
            $this->prenom = CMbString::capitalize(CMbString::lower($this->prenom));
        }
    }

    /**
     * @inheritdoc
     */
    function loadRefs()
    {
        // Backward references
        $obj                 = new CPatient();
        $this->_ref_patients = $obj->loadList("medecin_traitant = '$this->medecin_id'");
    }

    /**
     * Chargement de la fonction reliée
     *
     * @return CFunctions
     */
    function loadRefFunction()
    {
        return $this->_ref_function = $this->loadFwdRef("function_id", true);
    }

    /**
     * Chargement de la spécialité CPAM reliée
     *
     * @return CSpecCPAM
     */
    function loadRefSpecCPAM()
    {
        return $this->_ref_spec_cpam = CSpecCPAM::get($this->spec_cpam_id);
    }

    /**
     * Load the CMediusers
     *
     * @param bool $cache Set to true if you want to use the cache
     *
     * @return CMediusers
     */
    public function loadRefUser($cache = true)
    {
        return $this->_ref_user = $this->loadFwdRef('user_id', $cache);
    }

    /**
     * Charge les médecins identiques
     *
     * @param bool $strict_cp Stricte sur la recherche par code postal
     *
     * @return self[]
     */
    function loadExactSiblings($strict_cp = true)
    {
        $ds = $this->getDS();

        $medecin      = new self();
        $where        = [];
        $where["nom"] = $ds->prepare(" = %", $this->nom);

        if ($this->prenom) {
            $where["prenom"] = $ds->prepare(" = %", $this->prenom);
        } else {
            $where["prenom"] = "IS NULL";
        }

        if (CAppUI::isCabinet()) {
            $where["function_id"] = $ds->prepare(" = %", CMediusers::get()->function_id);
        } elseif (CAppUI::isGroup()) {
            $where["group_id"] = $ds->prepare(" = %", CMediusers::get()->loadRefFunction()->group_id);
        }


        if ($this->cp) {
            if (!$strict_cp) {
                $cp          = substr($this->cp, 0, 2);
                $where["cp"] = " LIKE '{$cp}___'";
            } else {
                $where["cp"] = " = '$this->cp'";
            }
        }

        $medecin->escapeValues();

        $siblings = $medecin->loadList($where);
        unset($siblings[$this->_id]);

        return $siblings;
    }

    /**
     * @inheritdoc
     */
    function getSexFieldName()
    {
        return "sexe";
    }

    /**
     * @inheritdoc
     */
    function getPrenomFieldName()
    {
        return "prenom";
    }

    /**
     * @inheritdoc
     */
    function getNomFieldName()
    {
        return 'nom';
    }

    /**
     * Exporte au format vCard
     *
     * @param CMbvCardExport $vcard Objet vCard
     *
     * @return void
     */
    function toVcard(CMbvCardExport $vcard)
    {
        $vcard->addName($this->prenom, $this->nom, "");
        $vcard->addPhoneNumber($this->tel, 'WORK');
        $vcard->addPhoneNumber($this->portable, 'CELL');
        $vcard->addPhoneNumber($this->fax, 'FAX');
        $vcard->addEmail($this->email);
        $vcard->addAddress($this->adresse, $this->ville, $this->cp, "", 'WORK');
    }

    /**
     * Load all the CMedecin object with one RPPS
     *
     * @param string $rpps        RPPS to search for
     * @param int    $function_id Function id of CMedecin
     * @param int    $group_id    Group id of CMedecin
     *
     * @return CMedecin
     */
    function loadFromRPPS($rpps, $function_id = null, $group_id = null)
    {
        $cache = new Cache('CMedecin.loadFromRPPS', [$rpps, $function_id, $group_id], CACHE::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        $where = [
            'rpps'        => $this->getDS()->prepare('= ?', $rpps),
            'function_id' => ($function_id !== null) ? $this->getDS()->prepare('= ?', $function_id) : 'IS NULL',
            'group_id'    => ($group_id !== null) ? $this->getDS()->prepare('= ?', $group_id) : 'IS NULL',
        ];

        $this->loadObject($where);

        return $cache->put($this);
    }

    /**
     * Load all the CMedecin object with one RPPS
     *
     * @param string $adeli       ADELI to search for
     * @param int    $function_id Function id of CMedecin
     *
     * @return CMedecin
     */
    function loadByAdeli($adeli, $function_id = null)
    {
        $cache = new Cache('CMedecin.loadByAdeli', [$adeli, $function_id], CACHE::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        $where = [
            'adeli'       => $this->getDS()->prepare('= ?', $adeli),
            'function_id' => ($function_id !== null) ? $this->getDS()->prepare('= ?', $function_id) : 'IS NULL',
        ];

        $this->loadObject($where);

        return $cache->put($this);
    }

    /**
     * Load all the CMedecin with $nom, $prenom, $type, $cp and $function_id
     *
     * @param string $nom         Last name to search for
     * @param string $prenom      First name to search for
     * @param string $type        Type
     * @param string $cp          Cp can be full or only first two nums
     * @param int    $function_id Function ID
     *
     * @return CMedecin
     */
    function loadMedecinList($nom, $prenom, $type, $cp, $function_id = null)
    {
        $cache = new Cache('CMedecin.loadMedecinList', [$nom, $prenom, $type, $cp, $function_id], Cache::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        $ds = $this->getDS();

        if ($cp && strlen($cp) > 2) {
            $cp_condition = $ds->prepare('= ?', $cp);
        } elseif ($cp) {
            $cp_condition = $ds->prepareLike("$cp%");
        } else {
            $cp_condition = "IS NULL";
        }

        $where = [
            'nom'         => $ds->prepare('= ?', $nom),
            'prenom'      => $ds->prepare('= ?', $prenom),
            'type'        => $ds->prepare('= ?', self::$types[$type]),
            'cp'          => $cp_condition,
            'function_id' => ($function_id !== null) ? $ds->prepare('= ?', $function_id) : 'IS NULL',
        ];

        $medecins = $this->loadList($where);

        if (count($medecins) > 1) {
            $this->handleImportDoublon($medecins);
        }

        if ($medecins) {
            return $cache->put(reset($medecins));
        }

        return $this;
    }

    /**
     * Store the CMedecin duplicates in SHM
     *
     * @param array $medecins Array of duplicates CMedecin
     *
     * @return void
     */
    function handleImportDoublon($medecins)
    {
        $doublons = [];

        $cache = Cache::getCache(Cache::OUTER);

        if ($cache->has('CMedecin-doublons-import')) {
            $doublons = $cache->get('CMedecin-doublons-import');
        }

        $medecin = reset($medecins);
        $key     = sprintf("%s-%s-%s", $medecin->nom, $medecin->prenom, $medecin->type);

        if ($medecin->cp) {
            $key .= "-$medecin->cp";
        }

        if (isset($doublons[$key])) {
            foreach ($medecins as $_med) {
                if (!isset($doublons[$key][$_med->_id])) {
                    $doublons[$key][$_med->_id] = true;
                }
            }
        } else {
            $doublons[$key] = [];
            foreach ($medecins as $_med) {
                $doublons[$key][$_med->_id] = true;
            }
        }

        $cache->set('CMedecin-doublons-import', $doublons);
    }

    /**
     * @inheritdoc
     */
    function getGeocodeFields()
    {
        return [
            'adresse',
            'cp',
            'ville',
        ];
    }

    function getFullAddress()
    {
        return $this->getAddress() . ' ' . $this->getZipCode() . ' ' . $this->getCity() . ' ' . $this->getCountry();
    }

    function getAddress()
    {
        return $this->adresse;
    }

    function getZipCode()
    {
        return $this->cp;
    }

    function getCity()
    {
        return $this->ville;
    }

    function getCountry()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    function createGeolocalisationObject()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            $geo = new CGeoLocalisation();
            $geo->setObject($this);
            $geo->processed = '0';
            $geo->store();

            return $geo;
        } else {
            return $this->_ref_geolocalisation;
        }
    }

    /**
     * @inheritdoc
     */
    function loadRefGeolocalisation()
    {
        return $this->_ref_geolocalisation = $this->loadUniqueBackRef('geolocalisation');
    }

    /**
     * @inheritdoc
     */
    function getLatLng()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        return $this->_ref_geolocalisation->lat_lng;
    }

    /**
     * @inheritdoc
     */
    function setLatLng($latlng)
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->lat_lng = $latlng;

        return $this->_ref_geolocalisation->store();
    }

    /**
     * @inheritdoc
     */
    function getCommuneInsee()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        return $this->_ref_geolocalisation->commune_insee;
    }

    /**
     * @inheritdoc
     */
    function setCommuneInsee($commune_insee)
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->commune_insee = $commune_insee;

        return $this->_ref_geolocalisation->store();
    }

    /**
     * @inheritdoc
     */
    function resetProcessed()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->processed = "0";

        return $this->_ref_geolocalisation->store();
    }

    function setProcessed(CGeoLocalisation $object = null)
    {
        if (!$object || !$object->_id) {
            $object = $this->loadRefGeolocalisation();
        }

        if (!$object || !$object->_id) {
            return null;
        }

        $object->processed = "1";

        return $object->store();
    }

    /**
     * Enable the importation state and do not put function_id on store
     *
     * @return void
     */
    function enableImporting()
    {
        $this->_is_importing = true;
    }

    /**
     * Disable the importation state and do not put function_id on store
     *
     * @return void
     */
    function disableImporting()
    {
        $this->_is_importing = false;
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchMedecin($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getSyncAvancement(): array
    {
        $versions = $this->countByVersion();
        $total    = $this->countList();

        foreach ($versions as &$_version) {
            $_version['pct']   = ($total > 0) ? number_format(($_version['total'] / $total) * 100, 4, ',', ' ') : 0;
            $_version['total'] = number_format($_version['total'], 0, ',', ' ');
        }

        return [$versions, number_format($total, 0, ',', ' ')];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function countByVersion(): array
    {
        $request = new CRequest();
        $request->addSelect(['import_file_version', 'COUNT(*) as total']);
        $request->addTable($this->_spec->table);
        $request->addGroup('import_file_version');
        $request->addOrder('total DESC');

        return $this->getDS()->loadList($request->makeSelect());
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceMotifs(?array $exercice_place_ids = null): ?Collection
    {
        $where = [];

        if ($exercice_place_ids) {
            $where['exercice_place_id'] = (new CAppFineMotifConsult())->getDS()->prepareIn($exercice_place_ids);
        }

        // On retourne que les créneaux dispos et dans le futur
        if (!$motifs = $this->loadBackRefs('praticien_motif_consult', null, null, null, null, null, null, $where)) {
            return null;
        }

        return new Collection($motifs);
    }

    /**
     * @param array|null $exercice_place_ids
     * @param array|null $dates
     * @param bool|null  $teleconsult
     * @param int|null   $motif_id
     *
     * @return Collection|null
     * @throws ApiException
     */
    public function getResourceAppointments(
        ?array $exercice_place_ids = null,
        ?array $dates = null,
        ?bool $teleconsult = null,
        ?int $motif_id = null
    ): ?Collection {
        $appointment   = new CAppFineAppointment();
        $default_where = [
            'appfine_appointment.unavailable' => " = '0'",
        ];
        $default_ljoin = [];

        // gestion des dates
        if ($dates) {
            $start = $dates['start'];
            if (!preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $start)) {
                $start = CMbDT::dateTime(null, $start);
            }
            $end = $dates['end'];
            if (!preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $end)) {
                $end = CMbDT::dateTime("+23 HOURS +59 MINUTES +59 SECONDS", $end);
            }

            // prend max entre start & now si now e [start,end]
            if ($start < CMbDT::dateTime()) {
                $start = CMbDT::dateTime();
            }

            if ($end < CMbDT::dateTime()) {
                $end = CMbDT::dateTime("+23 HOURS +59 MINUTES +59 SECONDS", CMbDT::roundTime($start, CMbDT::ROUND_DAY));
            }

            $limit_date = "appfine_appointment.start_at " . $appointment->getDS()->prepareBetween($start, $end);
        } else {
            $default_where['appfine_appointment.start_at'] = " >= '" . CMbDT::dateTime() . "'";
        }

        // teleconsultation
        if ($teleconsult !== null) {
            $teleconsult                                                    = $teleconsult ? 1 : 0;
            $default_where['appfine_appointment.eligible_teleconsultation'] = "= '$teleconsult'";
        }

        // Filtering by referenced motif_id filter
        if ($motif_id !== null) {
            $default_ljoin['appointment_motif']          = 'appointment_motif.appointment_id = appfine_appointment.appfine_appointment_id';
            $default_where['appointment_motif.motif_id'] = $appointment->getDS()->prepare(' = ?', $motif_id);
        } else {
            // When search all appointments without motif, we want only appointment which are motifs in database
            $default_ljoin['appointment_motif'] = 'appointment_motif.appointment_id = appfine_appointment.appfine_appointment_id';
            $default_where[]                    = "appointment_motif.appointment_motif_id IS NOT NULL";
        }

        // exercice_place
        if ($exercice_place_ids) {
            $default_where['appfine_appointment.exercice_place_id'] = $this->getDS()->prepareIn($exercice_place_ids);
        }

        // On retourne que les créneaux dispos et dans le futur
        $where = array_merge($default_where, isset($limit_date) ? [$limit_date] : []);

        // Grouping by appointment_id to avoid some duplicates or errors
        $group_by = ['appfine_appointment.appfine_appointment_id'];

        $appointments = $this->loadBackRefs(
            'praticien_appointment',
            null,
            null,
            $group_by,
            $default_ljoin,
            null,
            null,
            $where
        );
        if (!$appointments && $dates) {
            $start = $start ?? CMbDT::dateTime();
            $where = array_merge($default_where, ['start_at' => $appointment->getDS()->prepare(' >= ?1', $start)]);
            /** @var CAppFineAppointment $first_appointment */
            $first_appointment = $this->loadFirstBackRef(
                'praticien_appointment',
                'start_at ASC',
                null,
                $default_ljoin,
                null,
                true,
                $where
            );
            if ($first_appointment && $first_appointment->_id) {
                $appointments = [$first_appointment];
            }
        }

        if (!$appointments) {
            return null;
        }

        return new Collection($appointments);
    }


    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourcePresentation(): ?Item
    {
        $presentation = $this->loadUniqueBackRef('presentation');
        if (!$presentation || !$presentation->_id) {
            return null;
        }

        return new Item($presentation);
    }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourcePresentationPicture(): ?Collection
    {
        /** @var CPresentation $presentation */
        $presentation = $this->loadUniqueBackRef('presentation');

        if (!$presentation || !$presentation->_id) {
            return null;
        }

        return $presentation->getResourcePresentationPictures();
    }

    /**
     * Loads referenced `CExercicePlace` objects from a `CMedecin` object.
     *
     * @return Collection|null
     * @throws Exception
     * @throws ApiException
     */
    public function getResourceExercicePlace(): ?Collection
    {
        // Loading reference CMedecinExercicePlace objects from datasource
        $medecin_exercice_place             = new CMedecinExercicePlace();
        $medecin_exercice_place->medecin_id = $this->_id;
        $medecin_exercice_places            = $medecin_exercice_place->loadMatchingList();

        // Loading reference CExercicePlace identifiers
        $exercice_place_ids = CMbArray::pluck($medecin_exercice_places, 'exercice_place_id');

        // Loading reference CExercicePlace objects from datasource
        $where                      = [];
        $where['exercice_place_id'] = CSQLDataSource::prepareIn($exercice_place_ids);
        $exercice_places            = (new CExercicePlace())->loadList($where);

        return new Collection($exercice_places);
    }

    /**
     * @return Collection|null
     * @throws Exception|ApiException
     */
    public function getResourceMedecinExercicePlace(): ?Collection
    {
        $medecin_exercice_place = new CMedecinExercicePlace();

        $ljoin                   = [];
        $ljoin['exercice_place'] = 'exercice_place.exercice_place_id = medecin_exercice_place.exercice_place_id';

        $where                                             = [];
        $where['medecin_exercice_place.medecin_id']        = $medecin_exercice_place->getDS()->prepare(
            '= ?',
            $this->_id
        );
        $where['medecin_exercice_place.exercice_place_id'] = $medecin_exercice_place->getDS()->prepare('IS NOT NULL');
        $where['exercice_place.commune']                   = ' IS NOT NULL';

        $medecin_exercice_places = $medecin_exercice_place->loadList($where, null, null, null, $ljoin);

        if (empty($medecin_exercice_places)) {
            return null;
        }

        return new Collection($medecin_exercice_places);
    }

    /**
     * @return Item|null
     * @throws Exception|ApiException
     */
    public function getResourceContactPlace(): ?Item
    {
        $contact_place               = new CContactPlace();
        $contact_place->object_id    = $this->_id;
        $contact_place->object_class = $this->_class;
        $contact_place->loadMatchingObject();

        if (!$contact_place || !$contact_place->_id) {
            return null;
        }

        return new Item($contact_place);
    }

    /**
     * @return Collection|null
     * @throws Exception|ApiException
     */
    public function getResourceSchedulePlace(): ?Collection
    {
        // Loading CMedecinExercicePlace objects
        $medecin_exercice_place             = new CMedecinExercicePlace();
        $medecin_exercice_place->medecin_id = $this->_id;
        $medecin_exercice_places            = $medecin_exercice_place->loadMatchingListEsc();

        // Getting object UIDs
        $medecin_exercice_place_ids = CMbArray::pluck($medecin_exercice_places, '_id');

        // Loading CSchedulePlace objects
        $schedule_place        = new CSchedulePlace();
        $where                 = [];
        $where['object_id']    = CSQLDataSource::prepareIn($medecin_exercice_place_ids);
        $where['object_class'] = $schedule_place->getDS()->prepare(' = ?', $medecin_exercice_place->_class);
        $schedule_places       = $schedule_place->loadList($where);

        return new Collection($schedule_places);
    }

    /**
     * @return Collection|null
     * @throws Exception|ApiException
     */
    public function getResourceTemporaryInformation(): ?Item
    {
        // Fetching connected CUser object from datasource
        $user = CUser::get();

        $temporary_information               = new CTemporaryInformation();
        $temporary_information->object_id    = $this->_id;
        $temporary_information->object_class = $this->_class;

        // If CUser object is a robot (data fetch from third party app), loading CTemporaryInformation object without
        // active criteria
        // Else, loading CTemporaryInformation object only if active
        if (!$user->isRobot()) {
            $temporary_information->active = true;
        }

        $temporary_information->loadMatchingObject();

        if (!$temporary_information->_id) {
            return null;
        }

        return new Item($temporary_information);
    }

    /**
     * Provides a JSON:API representation of `CHonoraryPlace` relation resources of a `CMedecin` object.
     *
     * @return Collection|null
     * @throws Exception
     */
    public function getResourceHonoraryPlace(): ?Collection
    {
        // Loading CMedecinExercicePlace objects
        $medecin_exercice_place             = new CMedecinExercicePlace();
        $medecin_exercice_place->medecin_id = $this->_id;
        $medecin_exercice_places            = $medecin_exercice_place->loadMatchingListEsc();

        if (!$medecin_exercice_places) {
            return null;
        }

        // Getting object UIDs
        $medecin_exercice_place_ids = CMbArray::pluck($medecin_exercice_places, 'medecin_exercice_place_id');

        // Loading CHonoraryPlace objects
        $where                              = [];
        $where['medecin_exercice_place_id'] = CSQLDataSource::prepareIn($medecin_exercice_place_ids);
        $honorary_places                    = (new CHonoraryPlace())->loadList($where);

        return new Collection($honorary_places);
    }

    /**
     * Provides a JSON:API representation of `CInformationTarifPlace` relation resources of a `CMedecin` object.
     *
     * @return Collection|null
     * @throws Exception
     */
    public function getResourceInformationTarifPlace(): ?Collection
    {
        // Loading CMedecinExercicePlace objects
        $medecin_exercice_place             = new CMedecinExercicePlace();
        $medecin_exercice_place->medecin_id = $this->_id;
        $medecin_exercice_places            = $medecin_exercice_place->loadMatchingListEsc();

        if (!$medecin_exercice_places) {
            return null;
        }

        // Getting object UIDs
        $medecin_exercice_place_ids = CMbArray::pluck($medecin_exercice_places, 'medecin_exercice_place_id');

        // Loading CInformationTarifPlace objects
        $where                              = [];
        $where['medecin_exercice_place_id'] = CSQLDataSource::prepareIn($medecin_exercice_place_ids);
        $information_tarif_places           = (new CInformationTarifPlace())->loadList($where);

        return new Collection($information_tarif_places);
    }

    /**
     * Provides a JSON:API representation of `CAppFineMotifConsult` relation resources of a `CMedecin` object.
     *
     * @return Collection|null
     * @throws Exception
     */
    public function getResourceAppFineMotifConsult(): ?Collection
    {
        // Loading CAppFineMotifConsult objects
        $appfine_motif_consult               = new CAppFineMotifConsult();
        $appfine_motif_consult->praticien_id = $this->_id;
        $appfine_motif_consults              = $appfine_motif_consult->loadMatchingListEsc();

        return new Collection($appfine_motif_consults);
    }

    /**
     * Provides a JSON:API representation of `CAppFineAppointment` relation resources of a `CMedecin` object.
     *
     * @return Collection|null
     * @throws Exception
     */
    public function getResourceAppFineAppointment(): ?Collection
    {
        // Loading CAppFineMotifConsult objects
        $appfine_appointment               = new CAppFineAppointment();
        $appfine_appointment->praticien_id = $this->_id;
        $appfine_appointments              = $appfine_appointment->loadMatchingListEsc();

        return new Collection($appfine_appointments);
    }

    public function getMedecinExercicePlaces(): array
    {
        return $this->_ref_medecin_exercice_places = $this->loadBackRefs('medecins_exercices_places');
    }

    public function getExercicePlaces(): array
    {
        if (!$this->_id) {
            return [];
        }

        $medecin_exercice_places = $this->getMedecinExercicePlaces();

        $exercice_places = CStoredObject::massLoadFwdRef($medecin_exercice_places, 'exercice_place_id');

        /** @var CMedecinExercicePlace $_medecin_exercice_place */
        foreach ($medecin_exercice_places as $_medecin_exercice_place) {
            $_medecin_exercice_place->loadRefExercicePlace();
        }

        return $this->_ref_exercice_places = is_array($exercice_places) ? array_unique(
            $exercice_places,
            SORT_REGULAR
        ) : [];
    }

    public function setExercicePlace(?CMedecinExercicePlace $place): self
    {
        $this->_medecin_exercice_place = $place;

        return $this;
    }

    /**
     * This function load the exercice places matching filters
     *
     * @param string|null $cps
     * @param string|null $ville
     * @param string|null $type
     * @param string|null $discipline
     *
     * @return array|CExercicePlace[]
     * @throws Exception
     */
    public function getExercicePlacesByFilters(
        ?string $cps,
        ?string $ville,
        ?string $type,
        ?string $discipline
    ): array {
        $mep              = new CMedecinExercicePlace();
        $mep->medecin_id  = $this->_id;
        $mep->type        = $type != "" ? $type : null;
        $mep->disciplines = $discipline != "" ? $discipline : null;

        $medecin_exercice_places = $mep->loadMatchingList();

        if (empty($medecin_exercice_places)) {
            return [];
        }

        $exercice_place_ids = CMbArray::pluck($medecin_exercice_places, "exercice_place_id");

        $where = [];

        $sql_query = implode(",", array_filter($exercice_place_ids));

        if (empty($sql_query)) {
            return [];
        }

        $where[] = "exercice_place_id IN (" . $sql_query . ")";

        if ($cps != "") {
            $cps = explode(",", trim($cps));

            $where_cp = [];
            foreach ($cps as $cp) {
                $where_cp[] = "cp LIKE '" . trim($cp) . "%'";
            }
            $where[] = implode(" OR ", $where_cp);
        }

        if ($ville != "") {
            $where[] = "commune LIKE '" . $ville . "%'";
        }

        $ep = new CExercicePlace();

        return $this->_ref_exercice_places = $ep->loadList($where);
    }

    /**
     * Disable all correspondents without import date or before a date
     *
     * @return int
     * @throws Exception
     */
    public function disableCorrespondentsWithoutImportDate(string $date): int
    {
        $ds    = CSQLDataSource::get('std');
        $query = "UPDATE `medecin` SET `actif` = '0' WHERE `import_file_version` IS NULL";

        if ($date) {
            $query .= $ds->prepare(" OR `import_file_version` <= ?", $date);
        }
        $result = $ds->exec($query);

        return $ds->numRows($result);
    }

    /**
     * Check is RPPS is a fictif RPPS
     *
     * @param string $rpps
     *
     * @return bool
     */
    public static function isFictifRPPS(string $rpps): bool
    {
        if (strlen($rpps) !== 11) {
            return false;
        }

        $first_character = substr($rpps, 0, 1);

        // Fictif RPPS has 5 times the same character at the beginning of the string
        $common_pattern = $first_character . $first_character . $first_character . $first_character . $first_character;

        if (preg_match('#' . $common_pattern . '#', $rpps)) {
            return true;
        }

        return false;
    }

    /**
     * Get Medecin from his rpps
     *
     * @param string $rpps
     *
     * @return static|null
     */
    public static function getFromRPPS(string $rpps): ?self
    {
        $medecin = new self();
        $medecin->loadFromRPPS($rpps);

        return $medecin->_id ? $medecin : null;
    }

    /**
     * Found doctors
     *
     * @param string $medecin_last_name
     * @param string $medecin_first_name
     * @param string $rpps
     * @param string $strict
     * @param int    $step
     * @param int    $page
     *
     * @return array
     * @throws Exception
     */
    public static function foundDoctors(
        string $medecin_last_name,
        string $medecin_first_name,
        string $rpps,
        ?string $strict,
        int $step,
        int $page
    ): array {
        // Parsing slashes
        $medecin_last_name  = stripslashes($medecin_last_name);
        $medecin_first_name = stripslashes($medecin_first_name);

        $medecin    = new CMedecin();
        $medecin_ds = $medecin->getDS();
        $where      = [];

        if (!$medecin_last_name && !$medecin_first_name && !$rpps) {
            return [
                'medecins' => [],
                'count'    => 0,
            ];
        }

        if ($medecin_last_name) {
            $where['nom'] = $strict ? $medecin_ds->prepare('= ?', $medecin_last_name) : $medecin_ds->prepareLike(
                "%$medecin_last_name%"
            );
        }

        if ($medecin_first_name) {
            $where['prenom'] = $strict ? $medecin_ds->prepare('= ?', $medecin_first_name) : $medecin_ds->prepareLike(
                "%$medecin_first_name%"
            );
        }

        if ($rpps) {
            $where['rpps'] = $medecin_ds->prepare('= ?', $rpps);
        }

        $medecins = $medecin->loadList($where, null, "$page, $step");

        $count = $medecin->countList($where);

        return [
            'medecins' => $medecins,
            'count'    => $count,
        ];
    }

    /**
     * Check whether user is a pratician in function of his type or titre or spec cpam
     * List of praticien medecin chirurgien dentiste
     *
     * @return bool
     */
    public function isPraticien(): bool
    {
        return $this->_is_praticien = array_search($this->type, ["medecin", "chirurgien", "dentiste"]) !== false
            || $this->titre == 'dr'
            || in_array(
                $this->spec_cpam_id,
                [
                    "1",
                    "2",
                    "19",
                    "36",
                    "4",
                    "10",
                    "16",
                    "36",
                    "53",
                    "54",
                    "41",
                    "43",
                    "44",
                    "45",
                    "46",
                    "47",
                    "48",
                    "49",
                ]
            );
    }

    /**
     * Comparison of objects (for PHPUnit assertions).
     * This is for the manual import of correspondant purpose
     *
     * @param CMedecin $other
     *
     * @return bool
     */
    public function equals(self $other): bool
    {
        return (
            $this->nom === $other->nom
            && $this->rpps === $other->rpps
            && $this->titre === $other->titre
            && $this->sexe === $other->sexe
            && $this->spec_cpam_id === $other->spec_cpam_id
            && $this->type === $other->type
            && $this->import_file_version === $other->import_file_version
        );
    }

    /**
     * Map the class CMedecinExercicePlace with CMedecin
     *
     * @return void
     */
    function mapMedecinExercicesPaces()
    {
        $this->getExercicePlaces();

        $medecin_exercice_places = $this->_ref_medecin_exercice_places;

        foreach ($medecin_exercice_places as $_medecin_exercice_place) {
            $_exercice_place = $_medecin_exercice_place->_ref_exercice_place;

            if ($_medecin_exercice_place->disciplines) {
                $this->_mep_disciplines = $_medecin_exercice_place->disciplines;
            }
            if ($_exercice_place->tel) {
                $this->_mep_tel = $_exercice_place->tel;
            }
            if ($_exercice_place->tel2) {
                $this->_mep_tel2 = $_exercice_place->tel2;
            }
            if ($_exercice_place->adresse) {
                $this->_mep_adresse = $_exercice_place->adresse;
            }
            if ($_exercice_place->fax) {
                $this->_mep_fax = $_exercice_place->fax;
            }
            if ($_exercice_place->cp) {
                $this->_mep_cp = $_exercice_place->cp;
            }
            if ($_exercice_place->commune) {
                $this->_mep_ville = $_exercice_place->commune;
            }
            if ($_exercice_place->email) {
                $this->_mep_email = $_exercice_place->email;
            }
            if ($_medecin_exercice_place->adeli) {
                $this->_mep_adeli = $_medecin_exercice_place->adeli;
            }
            if ($_medecin_exercice_place->_mssante_addresses) {
                foreach ($_medecin_exercice_place->_mssante_addresses as $_ms_sante_email) {
                    $this->_mep_mssante_emails[] = $_ms_sante_email;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        $medecin_exercices_places = $this->getMedecinExercicePlaces();
        $exercice_place_ids = CMbArray::pluck($medecin_exercices_places, 'exercice_place_id');

        CMbArray::removeValue('', $exercice_place_ids);

        /** @var self $object */
        foreach ($objects as $object) {
            // Suppression des liens vers des mêmes lieux d'exercices
            foreach ($object->getMedecinExercicePlaces() as $medecin_exercice_place) {
                if (in_array($medecin_exercice_place->exercice_place_id, $exercice_place_ids)) {
                    $medecin_exercice_place->delete();
                }
            }
        }

        parent::merge($objects, $fast, $merge_log);
    }
}
