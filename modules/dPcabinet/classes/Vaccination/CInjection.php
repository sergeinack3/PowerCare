<?php
/**
 * @package Mediboard\OxCabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Vaccination;

use DateTime;
use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Medicament\IMedicamentProduit;
use Ox\Mediboard\Patients\CPatient;

/**
 * Vaccination follow-up
 * Here, CVaccination is an injection (can prevent several diseases)
 * e.g. DTP, HIB, Coqueluche at the same time is ONE CVaccination
 */
class CInjection extends CMbObject implements ImportableInterface
{
    /** @var string */
    public const RESOURCE_TYPE         = 'injection';
    public const RELATION_VACCINATIONS = 'vaccinations';

    /** FIELDS ETIQUETTE */
    public const NOM_VACCIN = "NOM VACCIN";
    public const NOM_PRODUIT_ADMINISTRE = "NOM PRODUIT ADMINISTRE";
    public const DATE_INJECTION = "DATE INJECTION";
    public const HEURE_INJECTION = "HEURE INJECTION";
    public const LOT_VACCIN = "LOT VACCIN";
    public const DATE_EXPIRATION = "DATE EXPIRATION";
    public const CODE_BARRE_CIP_PRODUIT = "CODE BARRE CIP PRODUIT";

    /** @var int Primary key */
    public $injection_id;
    public $patient_id;
    public $practitioner_name; // Can be another GP or pharmacy
    public $injection_date;
    public $batch; // Translation: lot du vaccin
    public $speciality; // Brand / name of the vaccine
    public $remarques;
    public $cip_product;
    public $expiration_date;

    public        $recall_age;
    public ?string $_recall_age_string;
    public ?string $_type_vaccin;

    // Current recall if there is one
    /** @var CRecallVaccin|null */
    public $_recall = null;
    /** @var string */
    public $_date_injection;
    /** @var string */
    public $_heure_injection;
    /** @var CPatient */
    public $_ref_patient;
    /** @var CVaccin */
    public $_ref_vaccine;
    /** @var CVaccination[] */
    public $_ref_vaccinations;

    /** @var IMedicamentProduit|null */
    private $_ref_produit;

    private static $fields_etiq = [
        self::NOM_VACCIN,
        self::NOM_PRODUIT_ADMINISTRE,
        self::DATE_INJECTION,
        self::HEURE_INJECTION,
        self::LOT_VACCIN,
        self::DATE_EXPIRATION,
        self::CODE_BARRE_CIP_PRODUIT
    ];

    /**
     * Generates an array with all the vaccines ordered by age and vaccine
     *
     * @param CInjection[]    $injections - all vaccinations of the patient
     * @param CVaccin[]       $vaccines
     * @param CRecallVaccin[] $recalls
     *
     * @return array
     * @throws Exception
     */
    public static function generateArray($injections, $vaccines, $recalls)
    {
        $display_array = [];
        $vaccinations  = [];
        if ($injections) {
            // Can't use pluck (_ref_vaccinations is an array)
            foreach ($injections as $_injection) {
                $vaccinations = array_merge($vaccinations, $_injection->_ref_vaccinations);
            }
        }

        foreach ($vaccines as $_vaccs) {
            $display_array[$_vaccs->type] = [];
            $dealt_recalls                = [];

            // Go through all recalls
            foreach ($recalls as $_recall) {
                $injection = null;

                $vaccination = self::hasVaccination($vaccinations, $_vaccs->type, $_recall);

                // If the patient has been vaccinated, add it with all the infos
                // Otherwise, add a vaccination
                // If there is a recall, add it (will make a clickable square)
                // else, add nothing (unclickable grey square)
                $filtered = array_filter($_vaccs->recall, function ($val) use ($_recall) {
                    return ($val->age_recall === $_recall->age_recall);
                });


                if (sizeof($filtered) > 0) {
                    $injection = ($vaccination) ? $vaccination->_ref_injection : new CInjection();

                    if (!$vaccination) {
                        $vaccination               = new CVaccination();
                        $vaccination->type         = $_vaccs->type;
                        $vaccination->_ref_vaccine = $_vaccs;
                    }

                    $injection->_recall           = reset($filtered);
                    $injection->_ref_vaccinations = [$vaccination];
                }

                $dealt_recalls[] = $_recall;

                $display_array[$_vaccs->type][$_recall->age_recall] = $injection;
            }
        }

        return $display_array;
    }

    /**
     * Checks if the patient has had a vaccination
     *
     * @param CVaccination[] $vaccinations - all vaccinations
     * @param string         $vacc_type    - the vaccination label
     * @param CRecallVaccin  $recall       - the vaccination recall
     *
     * @return CVaccination|false - the vaccination
     */
    public static function hasVaccination($vaccinations, $vacc_type, $recall)
    {
        if (empty($vaccinations)) {
            return false;
        }

        $recall_age = $recall->getRecallAge();

        // Go through all vaccinations to check if a recall has been made
        foreach ($vaccinations as $key_index => $_vaccination) {
            $_recall_age = (int)$_vaccination->_ref_injection->recall_age;

            if ($_vaccination->type === $vacc_type &&
                (($recall->age_max && ($_recall_age >= $recall_age) && ($_recall_age <= $recall->age_max)) ||
                    (!$recall->age_max && ($_recall_age === $recall_age)))) {
                return $_vaccination;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "injection";
        $spec->key   = "injection_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["patient_id"]        = "ref class|CPatient back|injections fieldset|extra";
        $props["practitioner_name"] = "str fieldset|default";
        $props["injection_date"]    = "dateTime notNull fieldset|default";
        $props["recall_age"]        = "num fieldset|default";
        $props["batch"]             = "str fieldset|default";
        $props["speciality"]        = "str fieldset|default";
        $props["remarques"]         = "text helped seekable markdown|true fieldset|default";
        $props["cip_product"]       = "str fieldset|default";
        $props["expiration_date"]   = "date fieldset|default";

        $props["_date_injection"]    = "birthDate";
        $props["_heure_injection"]   = "time";
        $props["_recall_age_string"] = "str";

        return $props;
    }

    /**
     * @inheritDoc
     */
    function updateFormFields() {
        parent::updateFormFields();

        $this->_recall_age_string   = $this->getRecallAgeString();
    }

    /**
     * @inheritDoc
     */
    public function store()
    {
        if (!$this->batch) {
            $this->batch = 'N/A';
        }

        return parent::store();
    }

    /**
     * @return CStoredObject[]|null
     * @throws Exception
     */
    public function loadRefVaccinations()
    {
        return $this->_ref_vaccinations = $this->loadBackRefs("vaccinations");
    }

    /**
     * Loads the patient
     *
     * @return CStoredObject|CPatient
     * @throws Exception
     */
    public function loadRefPatient()
    {
        return $this->_ref_patient = $this->loadFwdRef("patient_id");
    }

    /**
     * Chargement du produit
     *
     * @param bool $cache
     *
     * @return IMedicamentProduit|null
     */
    public function loadRefProduit(bool $cache = true): ?IMedicamentProduit
    {
        if (!$this->cip_product) {
            return null;
        }

        if (!$this->_ref_produit || !$cache) {
            $this->_ref_produit = CMedicamentProduit::get($this->cip_product);
        }

        return $this->_ref_produit;
    }

    /**
     * Checks if the age is coherent with the recall (2 months/years flexibility)
     *
     * @param string $birthday - understandable by a DateTime object
     *
     * @return bool
     * @throws Exception
     */
    public function isDateCoherent($birthday)
    {
        if (!$this->injection_date || !$this->getRecallAge()) {
            return true;
        }

        $injection_date = new DateTime($this->injection_date);
        $diff           = $injection_date->diff(new DateTime($birthday));

        if ($diff->y > 1) {
            return (abs($diff->y * 12 - $this->getRecallAge()) <= 2);
        }

        $age_months = (int)$diff->m + $diff->y * 12;

        return (abs($age_months - $this->getRecallAge()) <= 2);
    }

    /**
     * Gets the recall age (recommended vaccination)
     *
     * @return int|null
     */
    public function getRecallAge()
    {
        if ($this->_recall) {
            return $this->_recall->age_recall;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isVaccinated()
    {
        return ($this->speciality !== "N/A" || $this->hasBatch());
    }

    /**
     * @return bool
     */
    public function hasBatch(): bool
    {
        return $this->batch !== "N/A";
    }

    /**
     * Get the recall age as string
     *
     * @return string
     */
    public function getRecallAgeString(): string
    {
        return (CRecallVaccin::makeRecallVaccine($this->recall_age))->getStringDates();
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceVaccinations(): ?Collection
    {
        $vaccinations = $this->loadRefVaccinations();
        if (!$vaccinations) {
            return null;
        }

        return new Collection($vaccinations);
    }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchInjection($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * Getter to fields_etiq variale
     *
     * @return array
     * @throws Exception
     */
    public static function getFieldsEtiq()
    {
        $fields_etiq   = self::$fields_etiq;

        return $fields_etiq;
    }

    public function getNameVaccin()
    {
        $this->loadRefVaccinations();

        foreach ($this->_ref_vaccinations as $vaccination) {
            if ($vaccination->injection_id === $this->injection_id) {
                return $vaccination->type;
            }
        }
    }

    public function completeLabelFields(&$fields, $params)
    {
        $this->_date_injection      = CMbDT::format($this->injection_date, "%d-%m-%Y");
        $this->_heure_injection     = CMbDT::time($this->injection_date);

        $this->loadRefPatient()->completeLabelFields($fields, $params);
        $this->loadRefVaccinations();
        $dateTime = str_replace([':', '-', ' '], '', CMbDT::dateTime());

        $fields = array_merge(
            $fields,
            [
                self::NOM_VACCIN             => $this->getNameVaccin(),
                self::NOM_PRODUIT_ADMINISTRE => $this->speciality,
                self::DATE_INJECTION         => $this->_date_injection,
                self::HEURE_INJECTION        => $this->_heure_injection,
                self::LOT_VACCIN             => $this->batch,
                self::DATE_EXPIRATION        => CMbDT::format($this->expiration_date, "%d-%m-%Y"),
                self::CODE_BARRE_CIP_PRODUIT => "@BARCODE_" . $this->cip_product . $dateTime . "@",
            ]
        );
    }
}
