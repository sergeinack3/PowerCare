<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Atih;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CCIM10CategoryATIH implements IShortNameAutoloadable
{
    /** @var int L'id de la catégorie */
    public $id;

    /** @var string Le code de la catégorie */
    public $code;

    /** @var string Le libellé de la catégorie */
    public $libelle;

    /** @var bool Indique si la catégorie est un chapitre principal ou non */
    public $chapter;

    /** @var int L'id deu parent (0 pour les chapitres) */
    public $parent_id;

    /** @var CCIM10CategoryATIH Le chapitre parent */
    public $_parent;

    /** @var CCIM10CategoryATIH[] Les categories du chapitre */
    public $_categories;

    /** @var CCodeCIM10ATIH[] Les codes fils de la catégorie */
    public $_children;

    /** @var int The loading level */
    public $_load_level;

    /** @var bool Indicate if the code is a category */
    public $_is_category;

    /* Compatibility with the fields of CCodeCIM10 */
    public $occurrences;
    public $_favoris_id;
    public $_ref_favori;

    /**
     * CCIM10CategoryATIH constructor.
     *
     * @param integer $id    The id of the category
     * @param string  $code  The code of the category
     * @param int     $level The loading level
     */
    public function __construct($id = null, $code = null, $level = CCodeCIM10::LITE)
    {
        $this->id   = $id;
        $this->code = $code;

        $this->load();
        $this->loadParent();

        if ($level != CCodeCIM10::LITE) {
            $this->loadCategories();
            $this->loadChildren();
        }

        $this->_load_level = $level;
    }

    /**
     * Chargement des données
     *
     * @return bool
     */
    public function load()
    {
        if (!$this->id && !$this->code) {
            return false;
        }

        $ds = CCodeCIM10::getDS();

        if ($this->id) {
            $query = "SELECT * FROM chapters_atih WHERE id = ?1";
            $value = $this->id;
        } else {
            $query = "SELECT * FROM chapters_atih WHERE code = ?1";
            $value = $this->code;
        }
        $result = $ds->exec($ds->prepare($query, $value));

        if (!$result) {
            return false;
        }

        $fetched_data = $ds->fetchAssoc($result);
        if ($fetched_data !== false) {
            $this->map($fetched_data);
        }

        if ($this->parent_id) {
            $this->chapter = false;
        } else {
            $this->chapter = true;
        }

        return true;
    }

    /**
     * Load the parent chapter
     *
     * @return void
     */
    public function loadParent()
    {
        if ($this->parent_id) {
            $this->_parent = self::get($this->parent_id);
        }
    }

    /**
     * Charge les catégories du chapitre
     *
     * @return void
     */
    public function loadCategories()
    {
        if (!$this->chapter) {
            return;
        }

        $ds = CCodeCIM10::getDS();

        $this->_categories = [];

        $results = $ds->loadList("SELECT id FROM chapters_atih WHERE parent_id = $this->id;");
        foreach ($results as $result) {
            $this->_categories[] = self::get($result['id']);
        }
    }

    /**
     * Load the codes belonging to the category
     *
     * @return void
     */
    public function loadChildren()
    {
        if ($this->chapter) {
            return;
        }

        $ds = CCodeCIM10::getDS();

        $this->_children = [];

        $results = $ds->loadList(
            "SELECT code FROM codes_atih WHERE category_id = $this->id AND CHAR_LENGTH(code) = 3;"
        );
        foreach ($results as $result) {
            $this->_children[] = CCodeCIM10::get($result['code'], CCodeCIM10::LITE);
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
        if (!$data) {
            return;
        }

        foreach ($data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->$field = $value;
            }
        }
    }

    /**
     * Get the Category from the cache or load it
     *
     * @param int $id    The category's id
     * @param int $level Le niveau de chargement
     *
     * @return CCIM10CategoryATIH
     */
    public static function get($id, $level = CCodeCIM10::LITE)
    {
        $cache = new Cache('CCIM10CategoryATIH.get', $id, Cache::INNER_OUTER);

        $category = new self;
        if ($cache->exists()) {
            $category = $cache->get();
        } else {
            $category = new self($id, null, $level);
            $cache->put($category, true);
        }

        if (!$category) {
            $category = new self;
        } elseif ($level !== CCodeCIM10::LITE && $category->_load_level === CCodeCIM10::LITE) {
            $category->loadCategories();
            $category->loadChildren();
            $category->_load_level = $level;
            $cache->put($category, true);
        }

        return $category;
    }

    /**
     * Get the Category from the cache or load it
     *
     * @param string $code  The category's id
     * @param int    $level Le niveau de chargement
     *
     * @return CCIM10CategoryATIH
     */
    public static function getByCode($code, $level = CCodeCIM10::LITE)
    {
        $cache = new Cache('CCIM10CategoryATIH.getByCode', $code, Cache::INNER_OUTER);

        $category = new self;
        if ($cache->exists()) {
            $category = $cache->get();
        } else {
            $category = new self(null, $code, $level);
            $cache->put($category, true);
        }

        if (!$category) {
            $category = new self;
        } elseif ($level !== CCodeCIM10::LITE && $category->_load_level === CCodeCIM10::LITE) {
            $category->loadCategories();
            $category->loadChildren();
            $category->_load_level = $level;
            $cache->put($category, true);
        }

        return $category;
    }

    /**
     * Load all the chapters
     *
     * @param int $level The loading level
     *
     * @return CCIM10CategoryATIH[]
     */
    public static function getChapters($level = CCodeCIM10::LITE)
    {
        $ds       = CCodeCIM10::getDS();
        $chapters = [];

        $results = $ds->loadList("SELECT id FROM chapters_atih WHERE parent_id = 0;");
        foreach ($results as $result) {
            $chapters[] = self::get($result['id'], $level);
        }

        return $chapters;
    }

    /**
     * Check if the code is a favori for the given user
     *
     * @param CMediusers $user The user
     *
     * @return bool
     */
    public function isFavori($user)
    {
        return false;
    }
}
