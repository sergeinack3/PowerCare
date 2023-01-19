<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Gm;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cim10\Atih\CCodeCIM10ATIH;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\CFavoriCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Represents a code in the Cim10GM
 */
class CCodeCIM10GM extends CCodeCIM10
{
    /** @var int The id of the code */
    public $id;

    /** @var string The short code */
    public $code;

    /** @var string The full code */
    public $code_long;

    /** @var int The id of the parent chapter */
    public $chapter_id;

    /** @var int The id of the parent code */
    public $parent_id;

    /** @var string The usage of the code (asterisk, dagger or optional) */
    public $usage;

    /** @var string A sex restriction for the code (m: man, w: woman) */
    public $sex_code;

    /** @var string The reject code in case of error between the code and the sex (9: irrelevant, k: possible error) */
    public $sex_reject;

    /** @var string The minimum age for applying this code (d: days, m: months, y: years) */
    public $age_low;

    /** @var string The maximum age for applying this code (d: days, m: months, y: years) */
    public $age_high;

    /** @var string The reject code in case of error between the code and the age (9: irrelevant, k: possible error, m: incompatible) */
    public $age_reject;

    /** @var bool Indicate if the disease is rare in western Europe */
    public $rare_disease;

    /** @var bool Indicate if the code need informations on the content */
    public $content;

    /** @var bool Indicate that the disease is infectious */
    public $infectious;

    /** @var bool No fokin idea mate */
    public $ebm_labour;

    /** @var string The level 1 mortality code */
    public $mortality_level1;

    /** @var string The level 2 mortality code */
    public $mortality_level2;

    /** @var string The level 3 mortality code */
    public $mortality_level3;

    /** @var string The level 4 mortality code */
    public $mortality_level4;

    /** @var string The morbidity code */
    public $morbidity;

    /** @var string No fokin idea mate */
    public $para_295;

    /** @var string No fokin idea mate */
    public $para_301;

    /** @var string The name of the code */
    public $libelle;

    /** @var string The character corresponding to the usage of the code */
    public $_usage = '';

    /** @var string The readable form of the age_low */
    public $_age_low;

    /** @var string The readable form of the age_high */
    public $_age_high;

    /** @var CNoteCIM10GM The preferred name of the code */
    public $_preferred;

    /** @var CNoteCIM10GM The preferred long name of the code */
    public $_preferred_long;

    /** @var CNoteCIM10GM[] The exclusions notes */
    public $_exclusions;

    /** @var CNoteCIM10GM[] The inclusions notes */
    public $_inclusions;

    /** @var CNoteCIM10GM[] The conding hints notes */
    public $_coding_hints;

    /** @var CNoteCIM10GM A simple note */
    public $_note;

    /** @var CNoteCIM10GM A note containing simple text */
    public $_text;

    /** @var CNoteCIM10GM[] The definitions notes */
    public $_definitions;

    /** @var CCategoryCIM10GM The parent category */
    public $_category;

    /** @var CCodeCIM10GM The parent code */
    public $_parent;

    /** @var CCodeCIM10GM[] The direct descendants of the code */
    public $_descendants;

    /** @var int The load level of the object */
    public $_load_level;

    /**
     * CCodeCIM10GM constructor.
     *
     * @param string $code  The code
     * @param int    $level Indicate if the code data must be loaded
     */
    public function __construct($code = 'A00', $level = self::LITE)
    {
        $this->code = strtoupper($code);

        if ($level == self::LITE) {
            $this->loadLite();
        } else {
            $this->load();
        }

        switch ($this->usage) {
            case 'asterisk':
                $this->_usage = "&ast;";
                break;
            case 'dagger':
                $this->_usage = "&dagger;";
                break;
            case 'optional':
                $this->_usage = "?";
                break;
            default:
        }

        if ($this->age_low) {
            $this->_age_low = self::formatAge($this->age_low);
        }

        if ($this->age_high) {
            $this->_age_high = self::formatAge($this->age_high);
        }

        $this->_load_level = $level;
    }

    /**
     * Format the given age string into a readable format
     *
     * @param string $age The age
     *
     * @return string
     */
    protected static function formatAge($age)
    {
        if (strpos($age, 'd') !== false) {
            $age = str_replace('d', ' ' . CAppUI::tr('common-days'), $age);
        } elseif (strpos($age, 'm') !== false) {
            $age = str_replace('m', ' ' . CAppUI::tr('month'), $age);
        } elseif (strpos($age, 'y') !== false) {
            $age = str_replace('y', ' ' . CAppUI::tr('years'), $age);
        }

        return $age;
    }

    /**
     * Chargement minimal
     *
     * @param string $lang The language (not used in the Cim10GM)
     *
     * @return bool
     */
    public function loadLite($lang = null)
    {
        $ds = self::getDS();

        $this->exist = false;
        $this->id    = self::getId($this->code);
        if ($this->id) {
            $data = $ds->loadHash($ds->prepare("SELECT * FROM codes_gm WHERE id = ?1", $this->id));
            $this->map($data);
            $this->loadName();
            $this->exist = true;
        } else {
            $this->libelle = CAppUI::tr("CCodeCIM10.no_exist");
        }

        return $this->exist;
    }

    /**
     * Load the complete data fo the code
     *
     * @param string $lang The used language (not used in the Cim10GM)
     *
     * @return bool
     */
    public function load($lang = null)
    {
        if (!$this->loadLite()) {
            return false;
        }

        $this->loadNotes();
        $this->loadCategory();
        $this->loadAncestor();
        $this->loadDescendants();

        return true;
    }

    /**
     * Load the code name note
     *
     * @return void
     */
    public function loadName()
    {
        $this->_preferred = CNoteCIM10GM::getSingleFor($this, 'preferred');
        if ($this->_preferred) {
            $this->libelle = $this->_preferred->content;
        }
    }

    /**
     * Load the notes
     *
     * @return void
     */
    public function loadNotes()
    {
        $this->_preferred_long = CNoteCIM10GM::getSingleFor($this, 'preferredLong');
        $this->_note           = CNoteCIM10GM::getSingleFor($this, 'note');
        $this->_text           = CNoteCIM10GM::getSingleFor($this, 'text');
        $this->_coding_hints   = CNoteCIM10GM::getFor($this, 'coding-hint');
        $this->_exclusions     = CNoteCIM10GM::getFor($this, 'exclusion');
        $this->_inclusions     = CNoteCIM10GM::getFor($this, 'inclusion');
        $this->_definitions    = CNoteCIM10GM::getFor($this, 'definition');
    }

    /**
     * Load the parent category
     *
     * @return void
     */
    public function loadCategory()
    {
        $this->_category = CCategoryCIM10GM::get($this->chapter_id);
    }

    /**
     * Load the parent code
     *
     * @return void
     */
    public function loadAncestor()
    {
        if ($this->parent_id) {
            $this->_parent = self::get(self::getCode($this->parent_id), self::LITE);
        }
    }

    /**
     * Load the directs descendants of the code
     *
     * @return void
     */
    public function loadDescendants()
    {
        $this->_descendants = [];
        $ds                 = self::getDS();

        $results = $ds->loadList($ds->prepare("SELECT code FROM codes_gm WHERE parent_id = ?1;", $this->id));

        foreach ($results as $result) {
            $this->_descendants[] = self::get($result['code'], self::LITE);
        }
    }

    /**
     * Map the data from the Datasource to the object
     *
     * @param array $data The data from the database
     *
     * @return void
     */
    protected function map($data)
    {
        foreach ($data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->$field = $value;
            }
        }
    }

    /**
     * Charge le Code depuis le cache ou la base de données
     *
     * @param string $code    Le code CIM10
     * @param int    $level   Le niveau de chargement
     * @param string $lang    Langue
     * @param string $version La version de la base (oms ou atih)
     *
     * @return CCodeCIM10ATIH
     */
    public static function get($code, $level = self::LITE, $lang = null, $version = null)
    {
        /* Suppression of the parenthesis since they are not present in the field code of the categories */
        $code  = str_replace(['(', ')'], '', $code);
        $cache = new Cache('CCodeCIM10GM.get', [$code], self::$cache_layers);

        if ($cache->exists()) {
            $object = $cache->get();
        } else {
            $object = new self($code, $level);
            $cache->put($object, true);
        }

        /* If the object was loaded with the lite level,
        and we ask a greater load level, we lod it full and update the cache */
        if ($level !== self::LITE && $object->_load_level === self::LITE) {
            $object->loadNotes();
            $object->loadCategory();
            $object->loadAncestor();
            $object->loadDescendants();
            $object->_load_level = $level;
            $cache->put($object, true);
        }

        return $object;
    }

    /**
     * Search the codes from the CIm10GM
     *
     * @param string     $code           Recherche du code
     * @param string     $keys           Recherche textuelle (libellé)
     * @param string     $chapter        Recherche par chapitre
     * @param string     $category       Recherche par categorie
     * @param int        $max_length     La taille maximum du code
     * @param string     $where          Clause where
     * @param string     $version        La version de la base (oms ou atih)
     * @param string     $sejour_type    Le type de séjour (mco, ssr ou psy) pour déterminer si le code est autorisé
     * @param string     $field_type     Le type de champ (dp, dr, da, fppec, mmp, ae, das) pour déterminer si le code
     *                                   est autorisé
     * @param CMediusers $user_favorites Si renseigné, les favoris de l'utilisateur sont retournés en premiers
     *
     * @return array
     */
    public static function findCodes(
        $code,
        $keys,
        $chapter = null,
        $category = null,
        $max_length = null,
        $where = null,
        $version = null,
        $sejour_type = null,
        $field_type = null,
        $user_favorites = null
    ) {
        $ds = self::getDS();

        $query = new CRequest();
        $query->addTable('codes_gm');
        $query->addColumn('codes_gm.id', 'id');
        $query->addColumn('codes_gm.code', 'code');
        $query->addLJoinClause('notes_gm', 'notes_gm.owner_id = codes_gm.id');
        $query->addWhereClause('notes_gm.owner_type', " = 'code'");
        $query->addWhereClause('notes_gm.type', " = 'preferred'");

        if ($where) {
            $query->addWhere($where);
        }

        $where_codes = [];
        if ($code != '') {
            foreach (explode(' ', addslashes($code)) as $_code) {
                $where_codes[] = "codes_gm.code LIKE '$_code%'";
            }
        }

        $where_keys = [];
        if ($keys != '') {
            foreach (explode(' ', addslashes($keys)) as $keyword) {
                $where_keys[] = "notes_gm.content LIKE '%$keyword%'";
            }
        }

        if (count($where_codes) && count($where_keys)) {
            $query->addWhere('((' . implode(' AND ', $where_keys) . ') OR (' . implode(' OR ', $where_codes) . '))');
        } elseif (count($where_keys)) {
            $query->addWhere('(' . implode(' AND ', $where_keys) . ')');
        } elseif (count($where_codes)) {
            $query->addWhere('(' . implode(' AND ', $where_codes) . ')');
        }

        if ($max_length) {
            $query->addWhere("CHAR_LENGTH(codes_gm.code) < $max_length");
        }

        if ($chapter) {
            $query->addLJoinClause('chapters_gm', 'chapters_gm.id = codes_gm.chapter_id');
            $query->addWhere("chapters_gm.parent_id = '$chapter'");
        }

        if ($category) {
            $query->addWhere("codes_gm.chapter_id = '$category'");
        }

        if ($sejour_type && $field_type) {
            $subquery = new CRequest();
            $subquery->addTable('codes_gm AS c2');
            $subquery->addColumn('code');
            $subquery->addWhere('c2.parent_id = codes_gm.id');
            $query->addWhere('NOT EXISTS (' . $subquery->makeSelect() . ')');
        }

        if ($user_favorites) {
            $favori               = new CFavoriCIM10();
            $favori->favoris_user = $user_favorites->_id;
            $favorites            = CMbArray::pluck($favori->loadMatchingList(), 'favoris_code');

            $where_clauses[] = "codes_gm.code " . CSQLDataSource::prepareIn($favorites);
        }

        $query->addOrder('codes_gm.id');
        $query->setLimit('0, 100');

        $codes   = [];
        $r       = $query->makeSelect();
        $results = $ds->loadList($query->makeSelect());

        if ($user_favorites) {
            if ($where) {
                $where = "($where) AND codes_gm.code " . CSQLDataSource::prepareNotIn($favorites);
            } else {
                $where = 'codes_gm.code ' . CSQLDataSource::prepareNotIn($favorites);
            }

            $results = array_merge(
                $results,
                self::findCodes(
                    $code,
                    $keys,
                    $chapter,
                    $category,
                    $max_length,
                    $where,
                    $version,
                    $sejour_type,
                    $field_type
                )
            );
        }

        return $results;
    }

    /**
     * Get the sub codes for the given code or category
     *
     * @param string $code    The code
     * @param string $lang    Langue
     * @param string $version La version de la base (oms ou atih)
     *
     * @return array
     */
    public static function getSubCodes($code, $lang = null, $version = null)
    {
        if (self::isCategory($code)) {
            $category = CCategoryCIM10GM::getByCode($code, CCodeCIM10::FULL);
            if ($category->chapter) {
                $codes = $category->_categories;
            } else {
                $codes = $category->_codes;
            }
        } else {
            $code  = CCodeCIM10GM::get($code, CCodeCIM10::FULL);
            $codes = $code->_descendants;
        }

        $results = [];
        foreach ($codes as $code) {
            $results[] = ['code' => $code->code, 'text' => $code->_preferred->content];
        }

        return $results;
    }

    /**
     * Return the code from the id
     *
     * @param int $id The id of the code
     *
     * @return null|string
     */
    public static function getCode($id)
    {
        $ds = self::getDS();

        $query  = "SELECT code
              FROM codes_gm
              WHERE id = ?1;";
        $result = $ds->loadResult($ds->prepare($query, $id));

        return $result;
    }

    /**
     * Return the id of the given code
     *
     * @param string $code The code
     *
     * @return int|null
     */
    public static function getId($code)
    {
        $ds = self::getDS();

        $query  = "SELECT id
              FROM codes_gm
              WHERE code = ?1;";
        $result = $ds->loadResult($ds->prepare($query, strtoupper($code)));

        return $result;
    }

    /**
     * Check if the code exists
     *
     * @param string $code The code
     *
     * @return bool
     */
    public static function codeExists($code)
    {
        $ds = self::getDS();

        $query  = "SELECT COUNT(id)
              FROM codes_gm
              WHERE code = ?1;";
        $result = $ds->loadResult($ds->prepare($query, strtoupper($code)));

        return !is_null($result);
    }

    /**
     * Return the name of the database field containing the CIM10 codes
     *
     * @param string $version The CIM10 version
     *
     * @return string
     */
    public static function getCodeField($version = null)
    {
        return 'codes_gm.code';
    }

    /**
     * Return the name of the database field containing the CIM10 code's id
     *
     * @param string $version The CIM10 version
     *
     * @return string
     */
    public static function getIdField($version = null)
    {
        return 'id';
    }

    /**
     * Return the current version and the next available version of the CIM10 database
     *
     * @return array (string current version, string next version)
     */
    public static function getDatabaseVersions()
    {
        return [
            "CIM10 GM 2018" => [
                [
                    "table_name" => "codes_gm",
                    "filters"    => [],
                ],
            ],
        ];
    }
}
