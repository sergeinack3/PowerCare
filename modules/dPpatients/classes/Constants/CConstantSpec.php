<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Constants;

use DOMDocument;
use DOMNode;
use Exception;
use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\UnknownFunctionException;
use NXP\Exception\UnknownOperatorException;
use NXP\Exception\UnknownTokenException;
use NXP\Exception\UnknownVariableException;
use Ox\Api\CFitbitAPI;
use Ox\Api\CWithingsAPI;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbMath;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Core\CStoredObject;

/**
 * Description
 */
class CConstantSpec extends CStoredObject
{
    /** @var string */
    private const NS_OX_CONSTANTS = "http://www.openxtrem.com";

    /** @var int */
    public static $PERIOD_DAILY = 86400;
    /** @var int */
    public static $PERIOD_HOURLY = 3600;
    /** @var int */
    public static $PERIOD_INSTANTLY = 0;

    /** @var int */
    public static $ALL_SPECS = 0;
    /** @var int */
    public static $TABLE_SPECS = 2;
    /** @var int */
    public static $XML_SPECS = 1;

    /** @var int Primary key */
    public $constant_spec_id;

    //db field
    /** @var string */
    public $code;
    /** @var string */
    public $name;
    /** @var string */
    public $unit;
    /** @var string */
    public $value_class;
    /** @var string */
    public $category;
    /** @var int */
    public $period;
    /** @var int|float|string */
    public $min_value;
    /** @var int|float|string */
    public $max_value;
    /** @var string */
    public $list;
    /** @var int */
    public $alert_id;
    /** @var string */
    public $formule;
    /** @var int */
    public $active;
    /** @var int */
    public $alterable;

    // formfield
    /** @var bool */
    public $_ready = false;
    /** @var string */
    public $_primary_unit;
    /** @var array */
    public $_secondaries_units;
    /** @var bool */
    public $_is_constant_base = true;
    /** @var string */
    public $_input_type;
    /** @var array */
    public $_dependencies = [];
    /** @var array */
    public $_formule_constants = [];
    /** @var string */
    public $_view_formule;
    /** @var int */
    public $_warning_formule = 0;
    /** @var string */
    public $_warning_formule_error;

    // refs
    /** @var CConstantAlert si c'est une constant xml, alert par défaut */
    public $_alert;
    /** @var CConstantAlert alerte en base */
    public $_ref_alert;

    /**
     * Get constants spec by category
     *
     * @param string $cat   name of category
     * @param int    $scope filter for list constants
     *
     * @return CConstantSpec[]
     * @throws CConstantException
     */
    public static function getListSpecByCategory(string $cat = "all", int $scope = 1): array
    {
        $cache = new Cache("CConstantSpec.loadXMLConstant", "releve_constant_list", Cache::INNER_DISTR);
        if (!$constants = $cache->get()) {
            $constants = self::loadXMLConstants();
        }
        if ($cat == "all") {
            return self::getListSpecByCode($scope);
        }
        $spec_by_cat = CMbArray::get(CMbArray::get($constants, $scope), "by_category", []);

        return CMbArray::get($spec_by_cat, $cat, []);
    }

    /**
     * Get all constant from xml file
     *
     * @return mixed
     * @throws CConstantException
     */
    public static function loadXMLConstants()
    {
        $cache = new Cache('CConstantSpec.loadXMLConstants', "releve_constant_list", Cache::INNER_DISTR);
        if ($data = $cache->get()) {
            return $data;
        }

        $path        = CAppUI::conf("root_dir") . "/modules/dPpatients/resources/ConstantSpec.xml";
        $path_schema = CAppUI::conf("root_dir") . "/modules/dPpatients/resources/ConstantSpec.xsd";
        $doc         = new CMbXMLDocument("UTF-8");
        $doc->load($path);
        if (!$doc->schemaValidate($path_schema)) {
            throw new CConstantException(CConstantException::INVALID_DOCUMENT_XML);
        }

        $xpath = new CMbXPath($doc);
        $xpath->registerNamespace("ox", self::NS_OX_CONSTANTS);
        $nodes         = $xpath->getNode("/ox:constantSpec/ox:constants");
        $constant_spec = [];
        foreach ($nodes->childNodes as $_node) {
            $constant = self::loadXMLConstant($doc, $_node);
            if (!$constant) {
                continue;
            }
            $constant_spec[CMbArray::get($constant, "id")] = $constant;
        }
        //on récupére toutes les constantes en base, et on attribut le code comme key et on supprime la key originale
        $spec           = new self();
        $constants_list = $spec->loadList(["active" => "= '1'"]);
        foreach ($constants_list as $_constant) {
            $constants_list[$_constant->code] = $_constant;
            unset($constants_list[$_constant->_id]);
        }

        //on fusionne les deux tableaux contenant les constantes xml et les constantes en base
        $constants_list = CMbArray::mergeRecursive($constants_list, self::xmlToObjects($constant_spec));

        //pour chaque constantes en base, on va load dessus les alertes et faire un check sur les formules.
        /** @var CConstantSpec $_constant_spec */
        foreach ($constants_list as $_constant_spec) {
            if ($_constant_spec->_ready) {
                continue;
            }
            $_constant_spec->loadRefAlert();
            $_constant_spec->deserializeUnit();
            if ($_constant_spec->isCalculatedConstant()) {
                $_constant_spec->alterable = 1;
                CConstantSpec::addFormulaOnSpec($_constant_spec, $constants_list, $_constant_spec->formule);
                $_constant_spec->isValidFormule();
            }
            $_constant_spec->_ready = true;
        }

        return self::generateCache($constants_list);
    }

    /**
     * Load xml constant
     *
     * @param DOMDocument $doc  document xml
     * @param DOMNode     $node node of constant
     *
     * @return array constant
     */
    private static function loadXMLConstant(DOMDocument $doc, DOMNode $node): array
    {
        $xpath = new CMbXPath($doc);
        $xpath->registerNamespace("ox", self::NS_OX_CONSTANTS);

        $period = $xpath->getValueAttributNode($node, "period");
        $code   = $xpath->getValueAttributNode($node, "code");
        $id     = $xpath->getValueAttributNode($node, "id");
        $class  = $xpath->queryTextNode("ox:value_class", $node);

        $alerts      = [];
        $node_alerts = $xpath->getNode("ox:alerts", $node);
        if ($node_alerts) {
            foreach ($node_alerts->childNodes as $_node_alert) {
                $id_alert          = $xpath->getValueAttributNode($_node_alert, "id");
                $alerts[$id_alert] = [
                    "seuil_bas_$id_alert"    => $xpath->queryTextNode("ox:seuil_bas", $_node_alert),
                    "seuil_haut_$id_alert"   => $xpath->queryTextNode("ox:seuil_haut", $_node_alert),
                    "comment_bas_$id_alert"  => $xpath->queryTextNode("ox:text_bas", $_node_alert),
                    "comment_haut_$id_alert" => $xpath->queryTextNode("ox:text_haut", $_node_alert),
                ];
            }
        }
        $constant_spec = [
            "code"        => $code,
            "name"        => $xpath->queryTextNode("ox:name", $node),
            "id"          => $id,
            "value_class" => $class,
            "period"      => $period,
            "unit"        => $xpath->queryTextNode("ox:unit", $node),
            "category"    => $xpath->queryTextNode("ox:category", $node),
            "min_value"   => $xpath->queryTextNode("ox:min_value", $node),
            "max_value"   => $xpath->queryTextNode("ox:max_value", $node),
            "list"        => $xpath->queryTextNode("ox:list", $node),
            "formule"     => $xpath->queryTextNode("ox:formule", $node),
            "alert"       => $alerts,
            "alterable"   => $xpath->queryTextNode("ox:alterable", $node),
        ];

        return $constant_spec;
    }

    /**
     * Transform xml spec to object spec
     *
     * @param array $specs XML spec
     *
     * @return CConstantSpec[]
     */
    private static function xmlToObjects(array $specs): array
    {
        $constants_spec = [];
        foreach ($specs as $_spec) {
            /** @var CConstantSpec $constant */
            $constant = self::xmlToObject($_spec, $constants_spec);
            if ($constant) {
                $constant->_ready                = true;
                $constants_spec[$constant->code] = $constant;
            }
        }

        return $constants_spec;
    }

    /**
     * Create CConstantSpec from xml
     *
     * @param array           $_spec          array spec
     * @param CConstantSpec[] $constants_spec array CConstantSpec
     *
     * @return CConstantSpec|null
     */
    private static function xmlToObject(array $_spec, array &$constants_spec): ?CConstantSpec
    {
        if (!$_spec) {
            return null;
        }
        $code = CMbArray::get($_spec, "code");
        if (CMbArray::get($constants_spec, "$code")) {
            return null;
        }

        $constant_spec                    = new CConstantSpec();
        $constant_spec->code              = $code;
        $constant_spec->min_value         = CMbArray::get($_spec, "min_value");
        $constant_spec->max_value         = CMbArray::get($_spec, "max_value");
        $constant_spec->list              = CMbArray::get($_spec, "list");
        $constant_spec->_id               = CMbArray::get($_spec, "id");
        $constant_spec->constant_spec_id  = CMbArray::get($_spec, "id");
        $constant_spec->name              = CMbArray::get($_spec, "code");
        $constant_spec->value_class       = CMbArray::get($_spec, "value_class");
        $constant_spec->period            = CMbArray::get($_spec, "period");
        $constant_spec->unit              = CMbArray::get($_spec, "unit");
        $constant_spec->category          = CMbArray::get($_spec, "category");
        $constant_spec->alterable         = CMbArray::get($_spec, "alterable", 0);
        $constant_spec->_is_constant_base = false;

        $constant_spec = self::addAlertOnSpec($constant_spec, $_spec);
        // on l'ajoute ici pour empecher d'avoir une boucle sur le chargement car pour,
        // imc = poids + taille et poids = imc - taille
        // on va charger une constante si elle n'existe pas dans l'array de constants_spec,
        // et sans l'ajouter ici, on boucle infinement
        $constants_spec[$constant_spec->code] = $constant_spec;

        if ($formule = CMbArray::get($_spec, "formule")) {
            $constant_spec->alterable = 1;
            if (!self::addFormulaOnSpec($constant_spec, $constants_spec, $formule)) {
                return null;
            }
            $constant_spec->isValidFormule();
        }
        $constant_spec->updateFormFields();
        $constant_spec->deserializeUnit();

        return $constant_spec;
    }

    /**
     * Add on cache alert
     *
     * @param CConstantSpec $spec      spec to add alert
     * @param array         $data_spec data to create spec
     *
     * @return CConstantSpec spec
     * @throws Exception
     */
    private static function addAlertOnSpec(CConstantSpec $spec, array $data_spec): CConstantSpec
    {
        $alert       = new CConstantAlert();
        $count_alert = 0;
        foreach (CMbArray::get($data_spec, "alert") as $key => $_alerts) {
            $attr_seuil_bas            = "seuil_bas_$key";
            $attr_seuil_haut           = "seuil_haut_$key";
            $attr_texte_bas            = "comment_haut_$key";
            $attr_texte_haut           = "comment_bas_$key";
            $alert->{$attr_seuil_bas}  = CMbArray::get($_alerts, $attr_seuil_bas);
            $alert->{$attr_seuil_haut} = CMbArray::get($_alerts, $attr_seuil_haut);
            $alert->{$attr_texte_bas}  = CMbArray::get($_alerts, $attr_texte_bas);
            $alert->{$attr_texte_haut} = CMbArray::get($_alerts, $attr_texte_haut);
            $count_alert++;
        }

        if ($count_alert) {
            $alert->updateFormFields();
            $spec->_alert = $alert;
        }
        $where          = ["spec_id" => "= '$spec->_id'"];
        $constant_alert = new CConstantAlert();
        $constant_alert->loadObject($where);
        if ($constant_alert->_id) {
            $spec->_ref_alert = $constant_alert;
        }

        return $spec;
    }

    /**
     * @param CConstantSpec   $spec      constant spec
     * @param CConstantSpec[] $constants array of constants specs
     * @param string          $formula   formula to check
     *
     * @return CConstantSpec|null
     */
    public static function addFormulaOnSpec(CConstantSpec &$spec, array &$constants, string $formula): ?CConstantSpec
    {
        CConstantSpec::findAllConstantsInFormula($spec, $formula);

        foreach (CMbArray::get($spec->_formule_constants, "all", []) as $_code) {
            /** @var CConstantSpec $spec_dependency */
            //si on a pas la constante déjà charger en depuis le xml, on va la chercher
            if (!$spec_dependency = CMbArray::get($constants, "$_code")) {
                $spec_dependency = self::xmlToObject(self::loadXMLConstantByCode($_code), $constants);
                if (!$spec_dependency) {
                    $spec->_warning_formule       = 1;
                    $spec->_warning_formule_error = "CConstantSpec-msg-error this variable doesn t known";
                    unset($spec->_formule_constants["$_code"]);
                    continue;
                }
                //si on a charger la constante, on la sauvegarde pour pas aller la rechercher
                $constants[$spec_dependency->code] = $spec_dependency;
            }
            // dès qu'on la, on sauvegarde le nom de la constante calculé sur la constante
            $spec_dependency->_dependencies[$spec->code] = $spec->code;
        }

        return $spec;
    }

    /**
     * Format array __formule_constants with constants in formula
     *
     * @param CConstantSpec $spec    ConstantSpec
     * @param string        $formula formule
     *
     * @return array
     */
    private static function findAllConstantsInFormula(CConstantSpec &$spec, string $formula): array
    {
        // supprime les constantes optionnelle ([$constant]?number)
        $formula_mandatory = preg_replace('/\[\$(\w+)\]\?(\d|\.)+/i', "", $formula);
        //on garde toutes les constantes obligatoires
        self::findConstantsInFormula($spec, '/\[\$(\w+)\]/i', $formula_mandatory, "mandatory");
        // toutes les constantes <formule> [$constant] + $[constant]?45 ... </formule>
        self::findConstantsInFormula($spec, '/\[\$(\w+)\]/', $formula, "all");
        // on supprime les []
        $spec->formule = preg_replace("/(\[|\])/", "", $formula);

        return $spec->_formule_constants;
    }

    /**
     * Search constants with pattern and value in _formul_constant
     *
     * @param CConstantSpec $spec     Spec
     * @param string        $parttern parttern to search
     * @param string        $formula  formule to examine
     * @param string        $scope    key of _formule_constants, mandatory|optionals
     *
     * @return array
     */
    private static function findConstantsInFormula(
        CConstantSpec &$spec,
        string $parttern,
        string $formula,
        string $scope
    ): array {
        $constants = [];
        preg_match_all($parttern, $formula, $constants);
        // preg_match_all ajoute les resultat dans $constant[1]
        foreach ($constants[1] as $_constant) {
            $spec->_formule_constants[$scope][$_constant] = $_constant;
        }

        return $spec->_formule_constants;
    }

    /**
     * Load xml constant
     *
     * @param string $code code of constant
     *
     * @return array constant
     * @throws Exception
     */
    private static function loadXMLConstantByCode(string $code): array
    {
        $path = CAppUI::conf("root_dir") . "/modules/dPpatients/resources/ConstantSpec.xml";
        $doc  = new CMbXMLDocument("UTF-8");
        $doc->load($path);
        $xpath = new CMbXPath($doc);
        $xpath->registerNamespace("ox", self::NS_OX_CONSTANTS);
        $node = $xpath->getNode("ox:constants/ox:constant[@code='$code']");
        if (!$node) {
            return [];
        }

        return self::loadXMLConstant($doc, $node);
    }

    /**
     * Check if formule is valide
     *
     * @return bool
     */
    public function isValidFormule(): bool
    {
        if (!$this->isCalculatedConstant() || $this->_warning_formule) {
            return !$this->_warning_formule = 1;
        }
        $vars = [];
        /** @var CConstantSpec $_constant */
        foreach (CMbArray::get($this->_formule_constants, "all", []) as $_constant) {
            $vars["$_constant"] = 1;
        }
        // on enléve les valeur par défaut '?number'
        $formule_test = preg_replace("/(\?(\d+|\.)+)/", "", $this->formule);
        if ($this->checkAndEvaluateFormule($formule_test, $vars) === null) {
            return !$this->_warning_formule = 1;
        }

        return !$this->_warning_formule = 0;
    }

    /**
     * To know if spec is calculated
     *
     * @return bool
     */
    public function isCalculatedConstant(): bool
    {
        return $this->formule !== null ? true : false;
    }

    /**
     * Check formule
     *
     * @param String $formule Formule to evaluate
     * @param array  $vars    Variables
     *
     * @return int|null result or 0 if error and put error in _warning_formule_error
     */
    private function checkAndEvaluateFormule(string $formule, array $vars): ?int
    {
        $res = null;
        try {
            $this->_warning_formule_error = null;
            $res                          = CMbMath::evaluate($formule, $vars);
        } catch (UnknownFunctionException $e) {
            $this->_warning_formule_error = "CConstantSpec-msg-error this function doesn t exist";
        } catch (UnknownTokenException $ute) {
            $this->_warning_formule_error = "CConstantSpec-msg-error this token doesn t exist";
        } catch (UnknownOperatorException $uoe) {
            $this->_warning_formule_error = "CConstantSpec-msg-error this operator doesn t exist";
        } catch (UnknownVariableException $uve) {
            $this->_warning_formule_error = "CConstantSpec-msg-error this variable doesn t known";
        } catch (IncorrectExpressionException $iee) {
            $this->_warning_formule_error = "CConstantSpec-msg-error in expression on formula";
        }
        if ($this->_warning_formule_error) {
            return null;
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        if ($this->isCalculatedConstant()) {
            $this->_view_formule = "$this->name = " . preg_replace('/[\[|\]|\$]/', "", $this->formule);
        }
    }

    /**
     * Deserialize Unit
     *
     * @return void
     */
    public function deserializeUnit(): void
    {
        $explode = explode("|", $this->unit);

        $this->_primary_unit      = CMbArray::extract($explode, "0");
        $this->_secondaries_units = [];
        foreach ($explode as $value) {
            $unit_coeff                                              = explode(" ", $value);
            $this->_secondaries_units[CMbArray::get($unit_coeff, 0)] = [
                "label"   => CMbArray::get($unit_coeff, 0),
                "formula" => CMbArray::get($unit_coeff, 1),
            ];
        }
    }

    /**
     * Load ref CConstantAlert
     *
     * @return CConstantAlert|CStoredObject
     * @throws Exception
     */
    public function loadRefAlert(): ?CConstantAlert
    {
        $this->_ref_alert = $this->loadFwdRef("alert_id", true);
        if ($this->_ref_alert) {
            $this->_ref_alert->updateFormFields();
        }

        return $this->_ref_alert;
    }

    /**
     * Generate array list constants
     *
     * @param array $constants_spec constants spec from xml
     *
     * @return array
     */
    private static function generateCache(array $constants_spec): array
    {
        $cache     = new Cache("CConstantSpec.loadXMLConstant", "releve_constant_list", Cache::INNER_DISTR);
        $constants = [];
        /** @var CConstantSpec $_spec */
        $scopes = [self::$ALL_SPECS, self::$TABLE_SPECS, self::$XML_SPECS];
        foreach ($scopes as $_scope) {
            foreach ($constants_spec as $_spec) {
                if ($_scope == self::$TABLE_SPECS && !$_spec->_is_constant_base) {
                    continue;
                }
                if ($_scope == self::$XML_SPECS && $_spec->_is_constant_base) {
                    continue;
                }
                $constants[$_scope]["by_code"][$_spec->code]                      = $_spec;
                $constants[$_scope]["by_id"][$_spec->_id]                         = $_spec;
                $constants[$_scope]["by_category"][$_spec->category][$_spec->_id] = $_spec;
            }
        }

        return $cache->put($constants, true);
    }

    /**
     * Get List of constant
     *
     * @param int $scope filter for list constants
     *
     * @return CConstantSpec[] constant list
     */
    public static function getListSpecByCode(int $scope = 1): array
    {
        $cache = new Cache("CConstantSpec.loadXMLConstant", "releve_constant_list", Cache::INNER_DISTR);
        if (!$constants = $cache->get()) {
            $constants = self::loadXMLConstants();
        }

        return CMbArray::get(CMbArray::get($constants, $scope), "by_code", []);
    }

    /**
     * @param string[] $codes names of constants specs
     *
     * @return CConstantSpec[]
     */
    public static function getSpecsByCodes(array $codes): array
    {
        $specs = [];
        foreach ($codes as $_code) {
            $specs[] = self::getSpecByCode($_code);
        }

        return $specs;
    }

    /**
     * @param int[]|string[] $ids ids of constants specs
     *
     * @return CConstantSpec[]
     * @throws CConstantException
     */
    public static function getSpecsByIds(array $ids): array
    {
        $specs = [];
        foreach ($ids as $id) {
            if ($spec = self::getSpecById($id)) {
                $specs[] = $spec;
            }
        }

        return $specs;
    }

    /**
     * Get constants by name
     *
     * @param string $name Name of constant.
     *
     * @return CConstantSpec
     * @throws CConstantException
     */
    public static function getSpecByCode(string $name): CConstantSpec
    {
        if (!$spec = CMbArray::get(self::getListSpecByCode(self::$ALL_SPECS), $name, null)) {
            throw new CConstantException(CConstantException::INVALID_SPEC);
        }

        return $spec;
    }

    /**
     * Get constants by name
     *
     * @param string|int $id id
     *
     * @return CConstantSpec|null
     * @throws CConstantException
     */
    public static function getSpecById($id): CConstantSpec
    {
        if (!$spec = CMbArray::get(self::getListSpecById(self::$ALL_SPECS), $id, null)) {
            throw new CConstantException(CConstantException::INVALID_SPEC);
        }

        return $spec;
    }

    /**
     * @param int|string $group_id
     *
     * @return CConstantSpec[]
     */
    public static function getManualConstantSpecs($group_id = null): array
    {
        if (!$codes = CAppUI::gconf("appFine General list_constants_managed_manually", $group_id)) {
            return [];
        }

        return self::getSpecsByCodes(explode("|", $codes));
    }

    public static function getConstantsSourceManaged(): array
    {
        $sources = [CConstantReleve::SOURCE_MANUAL];

        // add source fitbit only if configured
        if (CFitbitAPI::getCronID()) {
            $sources[] = CConstantReleve::SOURCE_FITBIT;
        }

        // add source withings only if configured
        if (CWithingsAPI::getCronID()) {
            $sources[] = CConstantReleve::SOURCE_WITHINGS;
        }

        return $sources;
    }

    /**
     * Get constants spec by id
     *
     * @param int $scope filter for list constants
     *
     * @return CConstantSpec[]
     * @throws CConstantException
     */
    public static function getListSpecById(int $scope = 1): array
    {
        $cache = new Cache("CConstantSpec.loadXMLConstant", "releve_constant_list", Cache::INNER_DISTR);
        if (!$constants = $cache->get()) {
            $constants = self::loadXMLConstants();
        }
        $constants = CMbArray::get(CMbArray::get($constants, $scope), "by_id", []);

        return $constants;
    }

    /**
     * Get specs whitout some specs
     *
     * @param CConstantSpec[]   $specs   specs
     * @param CConstantReleve[] $releves releves
     * @param string            $prop    prop would be conherent whit filter spec
     * @deprecated
     * @return CConstantSpec[]
     */
    public static function getSpecWhitout(array $specs, array $releves, string $prop = "code"): array
    {
        foreach ($releves as $_releve) {
            if (!$_releve->_ref_all_values) {
                $_releve->loadAllValues(["active" => "= '1'"]);
            }
            /** @var CAbstractConstant $_contant */
            foreach ($_releve->_ref_all_values as $_contant) {
                unset($specs[$_contant->_ref_spec->{$prop}]);
            }
        }

        return $specs;
    }

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "constant_spec";
        $spec->key             = "constant_spec_id";
        $spec->uniques["code"] = ["code"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $values_class = self::getConstantClasses();
        $scopes       = [self::$ALL_SPECS, self::$XML_SPECS, self::$TABLE_SPECS];

        $props                  = parent::getProps();
        $props["name"]          = "str notNull";
        $props["code"]          = "str notNull";
        $props["unit"]          = "str notNull";
        $props["value_class"]   = "enum list|" . implode("|", $values_class) . " notNull";
        $props["category"]      = "enum list|biolo|physio|activity notNull";
        $props["period"]        = "enum list|" . self::$PERIOD_DAILY . "|" . self::$PERIOD_HOURLY
            . "|" . self::$PERIOD_INSTANTLY . " notNull";
        $props["min_value"]     = "str";
        $props["max_value"]     = "str";
        $props["list"]          = "str";
        $props["alert_id"]      = "ref class|CConstantAlert back|alert";
        $props["formule"]       = "str";
        $props["alterable"]     = "bool notNull default|0";
        $props["active"]        = "bool notNull";
        $props["_view_formule"] = "str";

        return $props;
    }

    /**
     * @param bool $short_name
     *
     * @return array
     * @throws \Exception
     */
    public static function getConstantClasses(bool $short_name = true): array
    {
        $classes = CAbstractConstant::CONSTANT_CLASS;
        foreach ($classes as $key => $class_name) {
            $classes[$key] = CClassMap::getSN($class_name);
        }

        return $classes;
    }

    /**
     * To know if spec has dependencies
     *
     * @return bool
     */
    public function hasDependencies(): bool
    {
        return count($this->_dependencies) ? true : false;
    }

    /**
     * Prepare formula to be calculated, remove default value ...
     *
     * @param CAbstractConstant[] $constants array with values
     *
     * @return string new formula
     */
    public function prepareFormula(array $constants): string
    {
        $formule = $this->formule;
        foreach ($this->_formule_constants["all"] as $_constant) {
            $formule = $this->prepareFormulaForOneConstant($constants, $_constant, $formule);
        }

        return $formule;
    }

    /**
     * Prepare formula for one constant in formula
     *
     * @param CAbstractConstant[] $constants constants values
     * @param string              $name      spec name
     * @param string              $formule   formula
     *
     * @return string
     */
    private function prepareFormulaForOneConstant(array $constants, string $name, string $formule): string
    {
        $constant = CMbArray::get($constants, $name);
        if ($constant) {
            // remplace $nom?1 par $nom
            $formule_test = preg_replace("/\$" . $name . "(\?(\d+|\.)+)/", "$" . $name, $formule);
        } else {
            // remplace $nom?1 par 1
            $formule_test = preg_replace("(\$" . $name . "\?)", "", $formule);
        }

        return $formule_test;
    }

    /**
     * Get name of constant translate
     *
     * @return string if not spec, return unknown constant
     */
    public function getViewName(): string
    {
        if (!$this->_id) {
            return CAppUI::tr("CConstantSpec.name.unknown");
        }
        if ($this->_is_constant_base) {
            return "*" . $this->name . "*";
        }

        return CAppUI::tr($this->name);
    }

    /**
     * To know if spec has alerts
     *
     * @return bool
     */
    public function hasAlert(): bool
    {
        return $this->alert_id || $this->_ref_alert->_id || $this->_alert;
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        $spec = CConstantSpec::getSpecByCode($this->code);

        //si on ne trouve pas de spec avec ce code
        if (!$spec) {
            if ($this->_is_constant_base) {
                return parent::check();
            }

            return "";
        }
        // si c'est une nouvelle constante et qu'on trouve une spec
        if (!$this->_id) {
            return "CConstantSpec-add-msg failed spec name already exist";
        }

        // si on trouve une spec et que son id et le name sont différent au this

        if ($this->_id != $spec->_id) {
            return "CConstantSpec-add-msg failed spec name already exist";
        }

        if (!$this->_is_constant_base) {
            return "CConstantSpec-add-msg failed this constant is not storable";
        }
        if ($this->_is_constant_base) {
            return parent::check();
        }

        return "";
    }

    /**
     * Restore spec after delete
     *
     * @return string
     * @throws CConstantException
     */
    public function restoreSpec(): string
    {
        if ($this->active) {
            return CAppUI::tr("CConstantSpec-msg-constant is already active");
        }
        if (!$this->_is_constant_base) {
            return CAppUI::tr("CConstantSpec-msg-constant don't be restore");
        }
        $where = [
            "active" => "= '1'",
            "name"   => "= '$this->name'",
        ];
        $spec  = new CConstantSpec();
        $spec->loadObject($where);
        if ($spec->_id) {
            if ($msg = $spec->storeInactive()) {
                throw new CConstantException(CConstantException::INVALID_DELETE_SPEC, $msg);
            }
        };
        $this->active = 1;
        if ($msg = $this->store()) {
            throw new CConstantException(CConstantException::INVALID_STORE_SPEC, $msg);
        }

        return "";
    }

    /**
     * Store spec in inactive
     *
     * @return string msg error or ""
     * @throws CConstantException
     */
    public function storeInactive(): string
    {
        if (!$this->_ref_alert && $this->alert_id) {
            $this->loadRefAlert();
        }

        $this->alert_id = null;
        if ($this->_ref_alert) {
            $this->_ref_alert->spec_id = null;
            if ($msg = $this->_ref_alert->delete()) {
                throw new CConstantException(CConstantException::INVALID_DELETE_ALERT, $msg);
            }
        }
        $this->active = 0;

        return $this->store();
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        if ($msg = parent::store()) {
            return $msg;
        }
        CConstantSpec::resetListConstants();

        return $msg;
    }

    /**
     * Reload constants spec list
     *
     * @return void
     */
    public static function resetListConstants(): void
    {
        $cache = new Cache("CConstantSpec.loadXMLConstant", "releve_constant_list", Cache::INNER_DISTR);
        $cache->rem();
    }

    /**
     * @return array
     */
    public function getUnits(): array
    {
        $units = array_keys($this->_secondaries_units);
        array_unshift($units, $this->_primary_unit);

        return $units;
    }

    /**
     * Serialize an array of secondary units
     *
     * @param String $primaru_unit Primary unit
     * @param array  $units        Secondary units
     *
     * @return void
     */
    public function serializeUnit(string $primaru_unit, array $units): void
    {
        $unit = $primaru_unit;

        foreach ($units as $_s_unit) {
            $unit .= "|" . CMbArray::get($_s_unit, "label") . " " . CMbArray::get($_s_unit, "formula");
        }
        $this->unit = $unit;
        $this->deserializeUnit();
    }

    /**
     * @param string $unit
     * @param mixed $value
     *
     * @return int|string
     * @throws CConstantException
     */
    public function convertInPrimaryUnit(string $unit, $value)
    {
        $u = CMbArray::get($this->_secondaries_units, $unit);
        if (!$u && $unit !== $this->_primary_unit) {
            throw new CConstantException(CConstantException::INVALID_UNIT);
        }

        if ($unit === $this->_primary_unit) {
            return $value;
        }

        return $this->convertUnit(CMbArray::get($u, "formula"), $value);
    }

    /**
     * Convert unit in an other unit with a formula
     *
     * @param String $formula formula to convert in an other unit
     * @param mixed  $value   Value to convert
     *
     * @return int|string
     */
    public function convertUnit(string $formula, $value)
    {
        // Deux formule possible 1: '*1000' || 2: '($a+340)*2'
        if (!strstr($formula, '$a')) {
            $formula = '$a' . $formula;
        }

        $vars = ["a" => $value];
        $res  = $this->checkAndEvaluateFormule($formula, $vars);
        if ($res !== null) {
            return $res;
        }

        return $value;
    }

    /**
     * @param string $unit
     * @param mixed  $value
     *
     * @return int|string
     * @throws CConstantException
     */
    public function convertPrimaryUnitTo(string $unit, $value)
    {
        if ($unit === $this->_primary_unit) {
            return $value;
        }

        if (!CMbArray::get($this->_secondaries_units, $unit)) {
            throw new CConstantException(CConstantException::INVALID_UNIT, $unit);
        }

        $reverseFormule = $this->reverseUnitFormula($unit);

        return $this->convertUnit($reverseFormule, $value);
    }

    /**
     * @param string $unit
     *
     * @return string
     * @throws CConstantException
     */
    private function reverseUnitFormula(string $unit): string
    {
        if (!$unit = CMbArray::get($this->_secondaries_units, $unit)) {
            if (!CMbArray::in($unit, $this->_secondaries_units)) {
                throw new CConstantException(CConstantException::INVALID_UNIT, $unit);
            }
        }

        $formula           = CMbArray::get($unit, "formula");
        $has_complexity    = !(strpos($formula, "(") === false);
        $functionInFormula = $this->hasFunctionInFormula($formula);
        if (!$has_complexity && !CMbArray::get($functionInFormula, "hasFunction")) {
            $operators = $this->getOperatorsInFormula($formula);
            foreach ($operators as $operator => $opData) {
                $index           = CMbArray::get($opData, "pos");
                $formula[$index] = CMbArray::get($opData, "reverse");
            }

            return $formula;
        }

        // !$has_complexity && $functionInFormula
        if (!$has_complexity) {
            //todo not implemented
            throw new CConstantException(CConstantException::INVALID_UNIT);
        }
        // has complexity et / ou function in formula
        //todo not implemented
        throw new CConstantException(CConstantException::INVALID_UNIT);
    }

    /**
     * @param $formula
     *
     * @return array
     */
    private function hasFunctionInFormula(string $formula): array
    {
        $has_function = false;
        $functions    = [];
        foreach (CMbMath::getCustomOps() as $nbParameters => $_customOps) {
            foreach ($_customOps as $_customOp) {
                if (($pos = strpos($formula, $_customOp)) !== false) {
                    $has_complexity        = true;
                    $functions[$_customOp] = ["pos" => $pos, "nbParams" => $nbParameters];
                }
            }
        }

        return ["hasFunction" => $has_function, "functions" => $functions];
    }

    /**
     * @param string $formula
     *
     * @return array
     * @throws CConstantException
     */
    private function getOperatorsInFormula(string $formula): array
    {
        $possibleOps = ['+', "-", "/", "*"];
        $operators   = [];
        foreach ($possibleOps as $possibleOp) {
            if (($pos = strpos($formula, $possibleOp)) !== false) {
                $operators[$possibleOp] = ["pos" => $pos, "reverse" => $this->reverseOperator($possibleOp)];
            }
        }

        return $operators;
    }

    /**
     * @param string $operator
     *
     * @return string
     * @throws CConstantException
     */
    private function reverseOperator(string $operator): string
    {
        switch ($operator) {
            case "+":
                return "-";
            case "/":
                return "*";
            case "*":
                return "/";
            case "-":
                return "+";
            default:
                throw new CConstantException(CConstantException::INVALID_OPERATOR, $operator);
        }
    }

    /**
     * Get class target by value_class
     *
     *
     * @return CAbstractConstant
     */
    public function getTargetClass(): CAbstractConstant
    {
        return new $this->value_class();
    }
}
