<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Gm;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Mediboard\Cim10\Atih\CCIM10CategoryATIH;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Represents a category (main chapter or sub chapter) of the CIM10 GM version
 */
class CCategoryCIM10GM implements IShortNameAutoloadable
{
    /** @var int The id of the category */
    public $id;

    /** @var string The shortened code of the category */
    public $code;

    /** @var string The code of the category */
    public $code_long;

    /** @var int The id of the parent category */
    public $parent_id;

    /** @var bool Indicate if the category is a main CIM10 chapter or not */
    public $chapter;

    /** @var string The name of the category */
    public $libelle;

    /** @var CNoteCIM10GM The note containing the name of the chapter */
    public $_preferred;

    /** @var CNoteCIM10GM[] The coding hints notes */
    public $_coding_hints;

    /** @var CNoteCIM10GM[] The exclusions notes */
    public $_exclusions;

    /** @var CNoteCIM10GM[] The inclusions notes */
    public $_inclusions;

    /** @var CNoteCIM10GM[] The notes containing introduction texts */
    public $_introductions;

    /** @var CNoteCIM10GM A simple note */
    public $_note;

    /** @var CNoteCIM10GM A note containing simple text */
    public $_text;

    /** @var CCategoryCIM10GM The parent category */
    public $_parent;

    /** @var CCategoryCIM10GM[] The children categories */
    public $_categories;

    /** @var CCodeCIM10GM[] The descendants codes */
    public $_codes;

    /** @var int The load level of the object */
    public $_load_level;

    /** @var bool Indicate if the code is a category */
    public $_is_category;

    /* Compatibility with the fields of CCodeCIM10 */
    public $occurrences;
    public $_favoris_id;
    public $_ref_favori;

    /**
     * CCategoryCIM10GM constructor.
     *
     * @param int $id    The id of the category
     * @param int $level Indicate if the code data must be loaded
     */
    public function __construct($id = null, $level = CCodeCIM10::LITE)
    {
        $this->id = $id;

        if ($level == CCodeCIM10::LITE) {
            $this->loadLite();
        } else {
            $this->load();
        }

        $this->_load_level = $level;
    }

    /**
     * Lod the category's data
     *
     * @return bool
     */
    public function loadLite()
    {
        if (!$this->id) {
            return false;
        }

        $ds     = CCodeCIM10::getDS();
        $result = $ds->exec($ds->prepare("SELECT * FROM chapters_gm WHERE id = ?1;", $this->id));

        if (!$result) {
            return false;
        }

        $this->map($ds->fetchAssoc($result));

        if ($this->parent_id) {
            $this->chapter = false;
        } else {
            $this->chapter = true;
        }

        $this->_preferred = CNoteCIM10GM::getSingleFor($this, 'preferred');

        if ($this->_preferred) {
            $this->libelle = $this->_preferred->content;
        }

        return true;
    }

    /**
     * Load the category data and it's references
     *
     * @return bool
     */
    public function load()
    {
        if (!$this->loadLite()) {
            return false;
        }

        $this->loadNotes();
        $this->loadParent();
        $this->loadCategories();
        $this->loadChildren();

        return true;
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
     * Load the notes
     *
     * @return void
     */
    public function loadNotes()
    {
        $this->_note          = CNoteCIM10GM::getSingleFor($this, 'note');
        $this->_text          = CNoteCIM10GM::getSingleFor($this, 'text');
        $this->_coding_hints  = CNoteCIM10GM::getFor($this, 'coding-hint');
        $this->_exclusions    = CNoteCIM10GM::getFor($this, 'exclusion');
        $this->_inclusions    = CNoteCIM10GM::getFor($this, 'inclusion');
        $this->_introductions = CNoteCIM10GM::getFor($this, 'introduction');
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
        $this->_categories = [];

        $ds = CCodeCIM10::getDS();

        $results = $ds->loadList($ds->prepare("SELECT id FROM chapters_gm WHERE parent_id = ?1;", $this->id));
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
        $this->_codes = [];

        if ($this->chapter) {
            return;
        }

        $ds = CCodeCIM10::getDS();

        $query   = "SELECT code FROM codes_gm WHERE chapter_id = ?1 AND CHAR_LENGTH(code) = 3;";
        $results = $ds->loadList($ds->prepare($query, $this->id));
        foreach ($results as $result) {
            $this->_codes[] = CCodeCIM10::get($result['code'], CCodeCIM10::LITE);
        }
    }

    /**
     * Get the Category from the cache or load it
     *
     * @param int $id    The category's id
     * @param int $level The level of loading (LITE : just the code and the parent, others : The children codes and
     *                   categories)
     *
     * @return CCategoryCIM10GM
     */
    public static function get($id, $level = CCodeCIM10::LITE)
    {
        $cache = new Cache('CCategoryCIM10GM.get', $id, Cache::INNER_OUTER);

        if ($cache->exists()) {
            $category = $cache->get();
        } else {
            $category = new self($id, $level);
            $cache->put($category, true);
        }

        /* If the object was loaded with the lite level, and we ask a greater load level,
        we lod it full and update the cache */
        if ($level !== CCodeCIM10::LITE && $category->_load_level === CCodeCIM10::LITE) {
            $category->loadNotes();
            $category->loadParent();
            $category->loadCategories();
            $category->loadChildren();
            $category->_load_level = $level;
            $cache->put($category, true);
        }

        return $category;
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
        $ds = CCodeCIM10::getDS();

        $query  = "SELECT code
              FROM chapters_gm
              WHERE id = ?1;";
        $result = $ds->loadResult($ds->prepare($query, $id));

        return $result;
    }

    /**
     * Get the Category from the cache or load it
     *
     * @param string $code  The category's id
     * @param int    $level The level of loading (LITE : just the code and the parent, others : The children codes and
     *                      categories)
     *
     * @return CCategoryCIM10GM
     */
    public static function getByCode($code, $level = CCodeCIM10::LITE)
    {
        $ds = CCodeCIM10::getDS();

        $result = $ds->loadHash($ds->prepare("SELECT id FROM chapters_gm WHERE code = ?1;", $code));

        if (!$result || !array_key_exists('id', $result)) {
            return new self();
        }

        $category = self::get($result['id'], $level);

        return $category;
    }

    /**
     * Load all the chapters
     *
     * @param int $level The level of loading (LITE : just the code and the parent, others : The children codes and
     *                   categories)
     *
     * @return CCIM10CategoryATIH[]
     */
    public static function getChapters($level = CCodeCIM10::LITE)
    {
        $cache = new Cache('CCategoryCIM10GM.getChapters', [$level], Cache::INNER_OUTER);

        if ($cache->exists()) {
            $chapters = $cache->get();
        } else {
            $ds       = CCodeCIM10::getDS();
            $chapters = [];

            $results = $ds->loadList("SELECT id FROM chapters_gm WHERE parent_id IS NULL;");
            foreach ($results as $result) {
                $chapters[] = self::get($result['id'], $level);
            }

            $cache->put($chapters, true);
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
