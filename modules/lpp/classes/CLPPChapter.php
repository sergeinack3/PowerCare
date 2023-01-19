<?php

/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp;

use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Lpp\Repository\LppChapterRepository;
use Ox\Mediboard\Lpp\Repository\LppCodeRepository;

/**
 * Represents a chapter of the LPP architecture
 */
class CLPPChapter extends CModelObject
{
    /** @var string The id of the chapter in the architecture */
    public $id;

    /** @var string The name of the chapter */
    public $name;

    /** @var integer The rank of the chapter in his level */
    public $rank;

    /** @var string The id of the parent of the chapter */
    public $parent_id;

    /** @var CLPPChapter The direct ancestor */
    public $_parent;

    /** @var CLPPChapter[] The descendants */
    public $_descendants;

    /** @var CLPPCode[] The LPP codes that descend from this chapter */
    public $_codes;

    /** @var array A conversion table from the db fields to the object fields */
    public static $db_fields = [
        'ID'      => 'id',
        'PARENT'  => 'parent_id',
        'INDEX'   => 'rank',
        'LIBELLE' => 'name',
    ];

    /**
     * CLPPChapter constructor.
     *
     * @param array $data The data returned from the database
     */
    public function __construct(array $data = [])
    {
        parent::__construct();

        foreach ($data as $_column => $_value) {
            if (array_key_exists($_column, self::$db_fields)) {
                $_field = self::$db_fields[$_column];

                $this->$_field = $_value;
            }
        }
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']        = 'str notNull';
        $props['name']      = 'str notNull';
        $props['rank']      = 'num notNull';
        $props['parent_id'] = 'str notNull';

        return $props;
    }

    /**
     * Load the parent of the chapter
     *
     * @return ?CLPPChapter
     */
    public function loadDirectAncestor(): ?self
    {
        if ($this->parent_id != 0) {
            $this->_parent = LppChapterRepository::getInstance()->loadChapter($this->parent_id);
        }

        return $this->_parent;
    }

    /**
     * Load all the ancestors of this chapter
     *
     * @return void
     */
    public function loadAncestors(): void
    {
        $this->loadDirectAncestor();

        if ($this->_parent) {
            $this->_parent->loadAncestors();
        }
    }

    /**
     * Load the direct descendants of this chapter
     *
     * @return CLPPChapter[]
     */
    public function loadDirectDescendants(): array
    {
        if (!$this->_descendants) {
            $this->_descendants = LppChapterRepository::getInstance()->loadChaptersFromParent($this->id);
        }

        return $this->_descendants;
    }

    /**
     * Load the LPP codes that descend from this chapter
     *
     * @return CLPPCode[]
     */
    public function loadCodes(): array
    {
        if (!$this->_codes) {
            $this->_codes = LppCodeRepository::getInstance()->search(null, null, $this->id);
        }

        return $this->_codes;
    }
}
