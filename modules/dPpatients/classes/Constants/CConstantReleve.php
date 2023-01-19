<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use Exception;
use Ox\Api\CAPITiers;
use Ox\Api\CAPITiersException;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CPatient;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Description
 */
class CConstantReleve extends CStoredObject
{
    // const constants
    /** @var string */
    public const FROM_MEDIBOARD = "self"; // todo to rename => SOURCE_SELF
    /** @var string */
    public const FROM_DEVICE = "device";  // todo to rename => SOURCE_DEVICE
    /** @var string */
    public const FROM_API = "api"; // todo to rename => SOURCE_API
    /** @var string */
    public const SOURCE_MANUAL = "manuel";
    /** @var string */
    public const SOURCE_FITBIT = 'fitbit';
    /** @var string */
    public const SOURCE_WITHINGS = 'withings';

    // const API
    /** @var string */
    public const RESOURCE_TYPE = "releve";
    /** @var string */
    public const FIELDSET_CONTEXT = 'context';
    /** @var string */
    public const FIELDSET_IDENTIFIERS = 'identifiers';
    /** @var string */
    public const RELATION_CONSTANTS_INT = 'constantsInt';
    /** @var string */
    public const RELATION_CONSTANTS_FLOAT = 'constantsFloat';
    /** @var string */
    public const RELATION_CONSTANTS_TEXT = 'constantsText';
    /** @var string */
    public const RELATION_CONSTANTS_ENUM = 'constantsEnum';
    /** @var string */
    public const RELATION_CONSTANTS_STATE_INTERVAL = 'constantsStateInterval';
    /** @var string */
    public const RELATION_CONSTANTS_DATETIME_INTERVAL = 'constantsDatetimeInterval';
    /** @var string */
    public const RELATION_CONSTANTS_VALUE_INTERVAL = 'constantsValueInterval';
    /** @var string */
    public const RELATION_COMMENTS = 'comments';

    /** @var CActionReport|null */
    public static $report = null;

    /** @var bool */
    public static $only_manual = false;

    // DB Fields
    /** @var int primary key */
    public $constant_releve_id;
    /** @var int */
    public $patient_id;
    /** @var string */
    public $source;
    /** @var int */
    public $type;
    /** @var int */
    public $user_id;
    /** @var string */
    public $datetime;
    /** @var string */
    public $created_datetime;
    /** @var string */
    public $update;
    /** @var int */
    public $context_id;
    /** @var string */
    public $context_class;
    /** @var int */
    public $active;
    /** @var int */
    public $validated;

    // form field
    private $_create_calculated = false;

    // refs
    /** @var array */
    public $_ref_all_values = [];
    /** @var array */
    public $_ref_stored_values = [];
    /** @var CPatient */
    public $_ref_patient;

    /**
     * Get all sources for constants
     *
     * @param array $except_sources except these sources
     *
     * @return array
     */
    public static function getAllSources(array $except_sources = []): array
    {
        $sources = [self::FROM_DEVICE, self::FROM_MEDIBOARD, self::FROM_API];

        return array_filter(
            $sources,
            function ($source) use ($except_sources) {
                return !CMbArray::in($source, $except_sources);
            }
        );
    }

    /**
     * Load all values
     *
     * @param CConstantReleve[] $releves        Collectoin of Releve
     * @param array             $where_constant clause where for loadAllValue
     *
     * @return void
     * @throws Exception
     */
    public static function massLoadAllConstantBackRefs(array $releves, array $where_constant = []): void
    {
        $releve    = new self();
        $backprops = $releve->getConstantBackProps();
        foreach ($backprops as $back_name => $backref) {
            CStoredObject::massLoadBackRefs($releves, $back_name, null, $where_constant);
        }
    }

    /**
     * Get list of backprops wich concern constants
     *
     * @return array
     * @throws Exception
     */
    protected function getConstantBackProps(): array
    {
        $keys = array_keys($this->getBackProps());

        return array_filter(
            $keys,
            function ($back_name) {
                return strpos($back_name, "constants_") !== false;
            }
        );
    }

    /**
     * Create releve and linked constants
     *
     * @param array $data       array of data
     * @param int   $patient_id patient id
     * @param int   $user_id    user id
     * @param int|null  $ctx_id     context id
     * @param string|null  $ctx_class  context class
     * @param bool  $skip_calculated
     *
     * @deprecated
     * @return CActionReport
     */
    public static function storeReleveAndConstants(
        array $data,
        int $patient_id,
        int $user_id,
        ?int $ctx_id = null,
        ?string $ctx_class = null,
        bool $skip_calculated = false
    ): CActionReport {
        $releves      = [];
        self::$report = new CActionReport();
        foreach ($data as $_data) {
            try {
                $spec        = self::getConstantSpec($_data);
                $data_releve = self::storeReleve($_data, $patient_id, $user_id, $spec, $ctx_id, $ctx_class);
                /** @var CConstantReleve $releve */
                $releve      = CMbArray::get($data_releve, "releve");
                $type_action = CMbArray::get($data_releve, "type", "other");
                $constant    = $releve->setConstante($_data, $spec, $type_action);
                if (!$constant) {
                    continue;
                }

                self::$report->addStoredObject($constant);
                if ($str_comment = CMbArray::get($_data, "comment")) {
                    if ($comment = $constant->addComment($str_comment)) {
                        self::$report->addStoredComment($comment);
                    }
                }

                /** @var CConstantReleve $releve_constant_stored */
                //sauvegarde du releve avec les constantes dessus
                if ($releve_constant_stored = CMbArray::get($releves, $releve->_id)) {
                    $releve_constant_stored->_ref_stored_values[$constant->_ref_spec->code] = $constant;
                } else {
                    $releve->_ref_stored_values[$constant->_ref_spec->code] = $constant;
                    $releves[$releve->_id]                                  = $releve;
                }
            } catch (CConstantException $exception) {
                self::$report->addException($exception);
            }
        }

        /** @var CConstantReleve $_releve */
        // calculated constants
        if ($skip_calculated) {
            return self::extractReport();
        }

        foreach ($releves as $_releve) {
            try {
                $_releve->updateCalculatedConstants();
            } catch (CConstantException $exception) {
                self::$report->addException($exception);
            }
        }

        return self::extractReport();
    }

    /**
     * Get spec from data
     *
     * @param array $data data
     *
     * @return CConstantSpec|null
     * @throws CConstantException
     */
    private static function getConstantSpec(array $data): ?CConstantSpec
    {
        $spec = CConstantSpec::getSpecByCode(CMbArray::get($data, "spec_code"));
        if (!$spec) {
            $spec = CConstantSpec::getSpecById(CMbArray::get($data, "spec_id"));
            if (!$spec) {
                throw new CConstantException(CConstantException::INVALID_SPEC);
            }
        }

        return $spec;
    }

    /**
     * @return CConstantReleve|null
     */
    public function map(array $data): CConstantReleve
    {
        if (!$datetime = CMbArray::get($data, 'releve_datetime')) {
            $datetime  = CMbArray::get($data, "datetime");
        }

        $this->constant_releve_id = $this->_id = CMbArray::get($data, "releve_id", $this->_id);
        $this->patient_id         = CMbArray::get($data, "patient_id", $this->patient_id);
        $this->source             = CMbArray::get($data, "source", $this->source);
        $this->validated          = CMbArray::get($data, "validated", $this->validated ?: 0);
        $this->user_id            = CMbArray::get($data, "user_id", $this->user_id);
        $this->context_class      = CMbArray::get($data, "context_class", $this->context_class);
        $this->context_id         = CMbArray::get($data, "context_id", $this->context_id);
        $this->type               = CMbArray::get($data, "period", $this->type);
        $this->datetime           = $datetime ?: $this->datetime;

        return $this;
    }

    /**
     * @return $this
     * @throws CConstantException
     */
    public function save(): self
    {
        // update releve
        if ($this->_id || $this->constant_releve_id) {
            $this->loadOldObject();
            if (!$this->_old->_id) {
                throw new CConstantException(CConstantException::RELEVE_NOT_FOUND);
            }
            $fields = array_keys($this->getPlainProps());
            $this->completeField($fields);
            $this->constant_releve_id = $this->_id = $this->_old->_id;

            if ($msg = $this->store()) {
                throw new CConstantException(CConstantException::INVALID_STORE_RELEVE, $msg);
            }
            self::$report->addUpdatedObject($this);
        }

        // check attributes releves
        $this->checkReleve();

        // try to find it
        if (!$this->loadMatchingObject()) {
            //save new releve
            if ($msg = $this->store()) {
                throw new CConstantException(CConstantException::INVALID_STORE_RELEVE, $msg);
            }

            self::$report->addStoredObject($this);
        } else {
            //load releve
            self::$report->addLoadedObject($this);
        }

        return $this;
    }

    /**
     * @throws CConstantException
     */
    public function checkReleve() {
        if ($this->type === null) {
            throw new CConstantException(CConstantException::INVALID_ARGUMENT, 'type');
        }

        if ($this->datetime === null) {
            throw new CConstantException(CConstantException::INVALID_ARGUMENT, 'datetime');
        }

        if (!$this->source) {
            throw new CConstantException(CConstantException::INVALID_ARGUMENT, 'source');
        }

        // normalize datetime
        $this->datetime = self::checkDuration($this->type, $this->datetime);
    }

    /**
     * @param CAbstractConstant[] $constants
     *
     * @return CAbstractConstant[]
     */
    public function saveConstants(array $constants): array
    {
        $constants_saved = [];
        foreach ($constants as $constant) {
            try {
                $constant->_ref_releve = $this;
                $constant->releve_id   = $this->_id;
                $constant->patient_id  = $this->patient_id;
                $values                = $constant->extractValues();
                $constant              = $constant->save($values);

                if ($constant->_comment) {
                    $comment = $constant->addComment($constant->_comment, true);
                    self::$report->addStoredObject($comment);
                }

                $constants_saved[]     = $constant;
            } catch (Exception $exception) {
                CConstantReleve::$report->addException($exception);
            }
        }

        return $constants_saved;
    }

    /**
     * Store releve
     *
     * @param array         $data       data linked to constant
     * @param int           $patient_id patient id
     * @param int           $user_id    user id (possible cron)
     * @param CConstantSpec $spec       specification
     * @param int           $ctx_id     context id
     * @param String        $ctx_class  context class
     *
     * @deprecated
     * @return array
     * @throws CConstantException
     */
    private static function storeReleve(
        array $data,
        int $patient_id,
        int $user_id,
        string $spec,
        ?int $ctx_id = null,
        ?string $ctx_class = null
    ): array {
        $period = CMbArray::get($data, "period");
        if (!$period) {
            $period = $spec->period;
        }
        if (!$source = CMbArray::get($data, "source")) {
            throw new CConstantException(CConstantException::INVALID_ARGUMENT, 'source');
        }
        $datetime  = CMbArray::get($data, "datetime", CMbDT::dateTime());
        $validated = CMbArray::get($data, "validated", 0);

        $releve = new CConstantReleve();
        if ($releve_id = CMbArray::get($data, "releve_id")) {
            $releve->load($releve_id);
            if (!$releve->_id) {
                throw new CConstantException(CConstantException::RELEVE_NOT_FOUND);
            }
        }

        $releve->patient_id    = $patient_id;
        $releve->source        = $source;
        $releve->validated     = $validated;
        $releve->user_id       = $user_id;
        $releve->context_class = $ctx_class;
        $releve->context_id    = $ctx_id;
        $releve->type          = $period;
        $releve->datetime      = self::checkDuration($period, $datetime);
        $releve->active        = 1;
        //update releve
        if ($releve_id) {
            if ($msg = $releve->store()) {
                throw new CConstantException(CConstantException::INVALID_STORE_RELEVE, $msg);
            }
            self::$report->addUpdatedObject($releve);

            return ["releve" => $releve, "type" => "updated"];
        }

        if (!$releve->loadMatchingObject()) {
            //save new releve
            if ($msg = $releve->store()) {
                throw new CConstantException(CConstantException::INVALID_STORE_RELEVE, $msg);
            }
            self::$report->addStoredObject($releve);
            $type_action = "saved";
        } else {
            //load releve
            self::$report->addLoadedObject($releve);
            $type_action = "loaded";
        }

        return ["releve" => $releve, "type" => $type_action];
    }

    /**
     * Convert datetime according to the date
     *
     * @param string $duree    releve period
     * @param string $datetime datetime to coonvert
     *
     * @return string
     */
    public static function checkDuration(string $duree, string $datetime): string
    {
        switch ($duree) {
            case 3600:
                return CMbDT::roundTime($datetime, CMbDT::ROUND_HOUR);

            case 86400:
                return CMbDT::roundTime($datetime, CMbDT::ROUND_DAY);

            default:
                return $datetime;
        }
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        if (!$this->_id) {
            $this->created_datetime = CMbDT::dateTime();
        }

        if (!$this->datetime) {
            $this->datetime = CMbDT::dateTime();
        }

        if ($this->active === null) {
            $this->active = 1;
        }

        return parent::store();
    }

    /**
     * Set constant value
     *
     * @param array         $values        Value to set
     * @param CConstantSpec $constant_spec Constant spec
     *
     * @deprecated
     * @return CAbstractConstant|null
     * @throws CConstantException
     */
    protected function setConstante(
        array $values,
        CConstantSpec $constant_spec,
        string $action_type = "other"
    ): ?CAbstractConstant {
        if ($this->type !== CConstantSpec::$PERIOD_INSTANTLY && $datetime = CMbArray::get($values, "datetime")) {
            $values["datetime"] = self::checkDuration($this->type, $datetime);
        }
        /** @var CAbstractConstant $value */
        $value             = new $constant_spec->value_class();
        $value->spec_id    = $constant_spec->constant_spec_id;
        $value->releve_id  = $this->_id;
        $value->patient_id = $this->patient_id;
        $value->active     = 1;
        $res               = $value->checkChangeValue($values, $constant_spec, $action_type);

        return $res;
    }

    /**
     * @return CActionReport
     */
    public static function extractReport(): CActionReport
    {
        $report       = self::$report;
        self::$report = null;

        return $report;
    }

    /**
     * Update all constants calculated
     *
     * @return void
     * @throws CConstantException
     */
    public function updateCalculatedConstants(): void
    {
        $aed_constants = [
            "tobrowse" => $this->getAedConstants(),
            "calculated" => []
        ];
        if (count(CMbArray::get($aed_constants, "tobrowse")) === 0) {
            return;
        }
        $this->recursiveUpdate($aed_constants);
    }

    /**
     * Return array with all dependencies to update
     *
     * @return array Dependencies
     */
    private function getAedConstants(): array
    {
        $constants_dependencies = [];
        /** @var CAbstractConstant $_constant */
        foreach ($this->_ref_stored_values as $_constant) {
            $constants_dependencies[$_constant->_ref_spec->code] = $_constant;
        }

        return $constants_dependencies;
    }

    /**
     * Update and calculate recursively
     *
     * @param array $aed_constants constants
     *
     * @return mixed
     * @throws CConstantException|CMbException
     */
    private function recursiveUpdate(array &$aed_constants)
    {
        foreach ($aed_constants["tobrowse"] as $_code => $_value) {
            $spec = CConstantSpec::getSpecByCode($_code);
            //pour toutes les dépendances de la spec parcourue
            foreach ($spec->_dependencies as $_calculated_constant) {
                // si la constante a déjà été calculé, on continue
                if (CMbArray::get($aed_constants["calculated"], $_calculated_constant)) {
                    continue;
                }
                $calculated_spec = CConstantSpec::getSpecByCode("$_calculated_constant");
                //si il y a une erreur dans la formule on passe
                if ($calculated_spec->_warning_formule) {
                    continue;
                }
                if (!$calculated = $this->updateDependency($calculated_spec)) {
                    continue;
                }
                $aed_constants["calculated"][$calculated->_ref_spec->code] = $calculated;
                $aed_constants["tobrowse"][$calculated->_ref_spec->code]   = $calculated;

                return $this->recursiveUpdate($aed_constants);
            }
            unset($aed_constants["tobrowse"][$spec->code]);

            return $this->recursiveUpdate($aed_constants);
        }

        return $aed_constants;
    }

    /**
     * Update constants calculated
     *
     * @param CConstantSpec $spec Constant Calculated
     *
     * @return CAbstractConstant|null
     * @throws CConstantException|CMbException
     */
    private function updateDependency(CConstantSpec $spec): ?CAbstractConstant
    {
        //_formule_constants contient les constantes nécessaires pour update la constante à calculer
        $constants = [];
        foreach (CMbArray::get($spec->_formule_constants, "all") as $_constant_code) {
            $spec_dependency = CConstantSpec::getSpecByCode("$_constant_code");
            if (!$spec_dependency) {
                continue;
            }
            // on regarde dans les constants ajoutées
            if (!$constant = CMbArray::get($this->_ref_stored_values, "$_constant_code")) {
                //si y a pas, on load les values et on regarde dedans
                if (!$this->_ref_all_values) {
                    $this->loadAllValues(["active" => "= '1'"]);
                }
                /** @var CAbstractConstant $_constant */
                foreach ($this->_ref_all_values as $_constant) {
                    if ($_constant->_ref_spec->code === "$_constant_code") {
                        $constant = $_constant;
                        break;
                    }
                }
                if (!$constant) {
                    // si y a toujours pas, on récup la dernière enregistrer
                    $filter = CConstantFilter::getFilterLast($_constant_code, $this->patient_id, 1);
                    $filter->addSources($this->source);
                    $filter->addUserIds($this->user_id);
                    $constant = (new CReleveRepository())->loadConstant($filter);
                }
            }
            // si y a pas de data et que c'est une constante obligatoire, on calcul pas
            if (!$constant && CMbArray::get($spec->_formule_constants["mandatory"], "$_constant_code")) {
                self::$report->addCaculatedFailed(
                    CAppUI::tr('CConstantReleve-msg-calculated constant failed', $_constant_code)
                );

                return null;
            }
            // on sauvegarde la constante trouvée.
            $constants[$_constant_code] = $constant;
        }
        // on retire les accolades
        if (!$constant_calculated = $this->storeCalculatedConstant($spec, $constants)) {
            return null;
        }
        self::$report->addCaculatedStored($constant_calculated);
        $this->_ref_stored_values[$constant_calculated->_ref_spec->code] = $constant_calculated;

        return $constant_calculated;
    }

    /**
     * Load all values for the releves
     *
     * @param array           $where clause where
     * @param string|string[] $only_spec_ids
     *
     * @return array values
     * @throws CConstantException|CMbException
     */
    public function loadAllValues(array $where = [], $only_spec_ids = null): array
    {
        if ($only_spec_ids ) {
            if (!is_array($only_spec_ids)) {
                $only_spec_ids = [$only_spec_ids];
            }
            $where['spec_id'] = (new CConstantReleve())->getDS()->prepareIn($only_spec_ids);
            $specs = CConstantSpec::getSpecsByIds($only_spec_ids);
            $backs = $this->getBackPropsForConstantSpec($specs);
        } else {
            $backs = $this->getConstantBackProps();
        }

        $this->_ref_all_values = [];
        foreach ($backs as $back_name) {
            $backs = $this->loadBackRefs(
                $back_name,
                null,
                null,
                null,
                null,
                null,
                null,
                $where
            );
            /** @var CAbstractConstant $_value */
            foreach ($backs as $_value) {
                $_value->updateFormFields();
                $this->_ref_all_values[$_value->_guid] = $_value;
            }
        }

        return $this->_ref_all_values;
    }

    /**
     * @param CConstantSpec[]|CConstantSpec $specs
     *
     * @return string[]
     * @throws CMbException
     */
    public function getBackPropsForConstantSpec($specs): array {
        if (!is_array($specs)) {
            $specs = [$specs];
        }

        $classes = array_unique(CMbArray::pluck($specs, 'value_class'));
        $accepted_backs = $this->getConstantBackProps();

        $backs = array_filter(
            $accepted_backs,
            function ($backname) use ($classes) {
                $backprop = $this->getBackProp($backname);
                return in_array(explode(' ', $backprop)[0], $classes);
            }
        );

        return array_values($backs);
    }

    /**
     * Add constants calculated
     *
     * @param CConstantSpec       $spec      spec constant calculated
     * @param CAbstractConstant[] $constants constants to evaluate
     *
     * @return CAbstractConstant|null
     * @throws CConstantException
     */
    private function storeCalculatedConstant(CConstantSpec $spec, array $constants): ?CAbstractConstant
    {
        $formule = $spec->prepareFormula($constants);
        /** @var CAbstractConstant $constant_value */
        $constant_value = new $spec->value_class();
        $values         = $constant_value::calculateValue($constants, $formule);
        $data           = ["datetime" => $this->datetime];
        if ($value = CMbArray::get($values, "value")) {
            $data["value"] = $value;
        }
        if ($min_value = CMbArray::get($values, "min_value")) {
            $data["min_value"] = $value;
        }
        if ($max_value = CMbArray::get($values, "max_value")) {
            $data["max_value"] = $value;
        }

        return self::setConstante($data, $spec);
    }

    /**
     * Get possibles sources for releve
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws CAPITiersException
     * @deprecated
     */
    public static function getSources(): array
    {
        $cache = new Cache('CConstantReleve.getSources', "api_cron_ids", Cache::INNER_OUTER);
        if ($data = $cache->get()) {
            return $data;
        }
        $apis    = CAPITiers::getAPIList();
        $sources = [];
        foreach ($apis as $_api_name) {
            if (!$user_api_id = CAPITiers::getCronID($_api_name)) {
                continue;
            }

            $sources[$user_api_id] = $_api_name;
        }

        try {
            $ref_class_releve  = new ReflectionClass("CConstantReleve");
            $constants_sources = $ref_class_releve->getConstants();
            foreach ($constants_sources as $_key_source => $_source) {
                if (!strstr("$_key_source", "FROM_")) { //todo to rename => SOURCE_DEVICE
                    continue;
                }
                $sources["$_source"] = $_source;
            }
        } catch (Exception $exception) {
            CApp::log($exception->getMessage());
        }

        return $cache->put($sources, true);
    }

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'constant_releve';
        $spec->key      = 'constant_releve_id';
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                     = parent::getProps();
        $props["patient_id"]       = "ref notNull class|CPatient seekable back|releve fieldset|identifiers";
        $props["source"]           = "enum list|self|manuel|device|api notNull fieldset|default";
        $props["user_id"]          = "ref class|CUser notNull back|constant_releve fieldset|identifiers";
        $props["datetime"]         = "dateTime notNull fieldset|default";
        $props["created_datetime"] = "dateTime notNull fieldset|extra";
        $props["update"]           = "dateTime fieldset|extra";
        $props["type"]             = "num notNull fieldset|default";
        $props["context_id"]       = "ref class|CMbObject meta|context_class back|context fieldset|context";
        $props["context_class"]    = "str fieldset|context";
        $props["active"]           = "bool notNull fieldset|default";
        $props["validated"]        = "bool notNull fieldset|default";

        return $props;
    }

    /**
     * Active creation of calculated constants
     */
    public function activeCalculatedConstants(): void
    {
        $this->_create_calculated = true;
    }

    /**
     * @return bool
     */
    public function isCalculatedConstantsActive(): bool
    {
        return $this->_create_calculated;
    }

    /**
     * Load referenced patient
     *
     * @return CStoredObject|CPatient
     * @throws Exception
     */
    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
    }

    /**
     * Check if releve is always active
     *
     * @return bool
     * @throws Exception
     */
    public function checkActive(): bool
    {
        $where        = [
            "releve_id"  => "= '$this->_id'",
            "patient_id" => "= '$this->patient_id'",
            "active"     => "= '1'",
        ];
        $values_count = 0;
        foreach ($this->getConstantBackProps() as $back_name) {
            $values_count += $this->countBackRefs($back_name, $where);
        }
        if ($values_count > 0) {
            return true;
        }

        return false;
    }

    /**
     * Set releve inactive
     *
     * @param int $forceInactive if true, store inactive constants in releve
     *
     * @return void
     * @throws CConstantException
     */
    public function storeInactive(bool $forceInactive = true): bool
    {
        if (!$this->active) {
            return false;
        }
        $this->active = 0;
        if ($msg = $this->store()) {
            throw new CConstantException(CConstantException::INVALID_STORE_RELEVE, $msg);
        }
        if (!$forceInactive) {
            return true;
        }

        return $this->storeConstantsInactive();
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstantsInt(): ?Collection
    {
        $object = new CValueInt();
        return $this->getResourceConstants($object->_class, $object::RESOURCE_TYPE);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstantsFloat(): ?Collection
    {
        $object = new CValueFloat();
        return $this->getResourceConstants($object->_class, $object::RESOURCE_TYPE);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstantsText(): ?Collection
    {
        $object = new CValueText();
        return $this->getResourceConstants($object->_class, $object::RESOURCE_TYPE);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstantsEnum(): ?Collection
    {
        $object = new CValueEnum();
        return $this->getResourceConstants($object->_class, $object::RESOURCE_TYPE);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstantsStateInterval(): ?Collection
    {
        $object = new CStateInterval();
        return $this->getResourceConstants($object->_class, $object::RESOURCE_TYPE);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstantsDatetimeInterval(): ?Collection
    {
        $object = new CDateTimeInterval();
        return $this->getResourceConstants($object->_class, $object::RESOURCE_TYPE);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstantsValueInterval(): ?Collection
    {
        $object = new CValueInterval();
        return $this->getResourceConstants($object->_class, $object::RESOURCE_TYPE);
    }

    /**
     * @param string $class
     * @param string $type
     *
     * @return Collection|null
     * @throws ApiException
     * @throws CConstantException
     * @throws CMbException
     */
    public function getResourceConstants(string $class, string $type): ?Collection
    {
        if ($this->_ref_all_values) {
            $constants = $this->_ref_all_values;
        } else {
            $constants = $this->loadConstants(self::$only_manual);
        }

        if (!$constants) {
            return null;
        }

        $constants = array_filter(
            $constants,
            function ($key) use ($class) {
                return str_starts_with($key, $class);
            },
            ARRAY_FILTER_USE_KEY
        );

        $items = new Collection($constants);
        $items->setType($type);

        return $items;
    }

    /**
     * @return Collection|null
     * @throws CConstantException
     * @throws ApiException|CMbException
     */
    public function loadConstants(bool $onlyManual = false): array
    {
        if ($onlyManual) {
            $appFine_group_id    = CAppUI::conf('appFine CRGPDConsent group_id_global_consent');
            $spec_codes_manually = CAppUI::gconf('appFine General list_constants_managed_manually', $appFine_group_id);
            $specs               = CConstantSpec::getSpecsByCodes(explode('|', $spec_codes_manually));
            $spec_ids            = CMbArray::pluck($specs, '_id');
        }

        return $this->loadAllValues(['active' => "= '1'"], $spec_ids ?? null);
    }

    /**
     * @return Collection|null
     * @throws CConstantException
     * @throws ApiException|CMbException
     * @throws Exception
     */
    public function getResourceComments(): ?Collection
    {
        if (!$comments = $this->loadRefComments()) {
            return null;
        }

        return new Collection($comments);
    }

    /**
     * @return CReleveComment[]
     * @throws Exception
     */
    public function loadRefComments(): array {
        return $this->loadBackRefs('comments_releve');
    }

    /**
     * Force linked constant to inactive
     *
     * @return void
     * @throws CConstantException
     */
    public function storeConstantsInactive(): bool
    {
        $where = [
            "releve_id"  => "= '$this->_id'",
            "patient_id" => "= '$this->patient_id'",
            "active"     => "= '1'",
        ];

        $result = true;
        foreach ($this->getConstantBackProps() as $back_name) {
            $backref = $this->loadBackRefs($back_name, null, null, null, null, null, null, $where);
            /** @var CAbstractConstant $_constant */
            foreach ($backref as $_constant) {
                $result = $result && $_constant->storeInactive(false);
            }
        }

        return $result;
    }
}
