<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\Cache;

/**
 * Hierarchie CsARR
 */
class CHierarchieCsARR extends CCsARRObject
{
    public $code;
    public $libelle;

    /** @var self[] */
    public $_ref_parent_hierarchies;
    /** @var self[] */
    public $_ref_child_hierarchies;
    /** @var CActiviteCsARR */
    public $_ref_activites;
    /** @var CNoteHierarchieCsARR */
    public $_ref_notes_hierarchies;

    static $cached = [];

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'hierarchie';
        $spec->key   = 'code';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        // DB Fields
        $props["code"]    = "str notNull length|11 seekable";
        $props["libelle"] = "str notNull seekable";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view      = $this->code;
        $this->_shortview = $this->code;
    }

    /**
     * Charge les hiérarchies CsARR parentes
     *
     * @return self[]
     */
    function loadRefsParentHierarchies()
    {
        // Codes des hiérarchies intermédiaires
        $parts = explode(".", $this->code);
        array_pop($parts);
        $codes = [];
        foreach ($parts as $_part) {
            $codes[] = count($codes) ? end($codes) . ".$_part" : $_part;
        }

        // Chargement des hiérarchies intermédiaires
        $hierarchie  = new self;
        $hierarchies = $hierarchie->loadAll($codes);

        return $this->_ref_parent_hierarchies = $hierarchies;
    }

    /**
     * Charge les hiérarchies CsARR filles
     *
     * @return self[]
     */
    function loadRefsChildHierarchies()
    {
        $where["code"] = "LIKE '$this->code.__'";
        $hierarchie    = new self;
        $hierarchies   = $hierarchie->loadList($where);

        return $this->_ref_child_hierarchies = $hierarchies;
    }

    /**
     * Charge les activités de la hiérarchie
     *
     * @return CActiviteCsARR[]
     */
    function loadRefsActivites()
    {
        $activite             = new CActiviteCsARR();
        $activite->hierarchie = $this->code;
        $activite             = $activite->loadMatchingList();

        return $this->_ref_activites = $activite;
    }

    /**
     * Charge les notes de la hiérarchie
     *
     * @return array
     */
    function loadRefsNotesHierarchies()
    {
        $note             = new CNoteHierarchieCsARR;
        $note->hierarchie = $this->code;
        $notes            = [];
        foreach ($note->loadMatchingList("ordre") as $_note) {
            $notes[$_note->typenote][$_note->ordre] = $_note;
        }

        return $this->_ref_notes_hierarchies = $notes;
    }

    /**
     * Get an instance from the code
     *
     * @param string $code Code
     *
     * @return self
     **/
    static function get($code)
    {
        if (!$code) {
            return new self();
        }

        $cache = Cache::getCache(Cache::OUTER);

        if ($hierarchie = $cache->get("hierarchie_$code")) {
            return $hierarchie;
        }

        $hierarchie = new self();
        $hierarchie->load($code);

        $cache->set("hierarchie_$code", $hierarchie);

        return $hierarchie;
    }

    /**
     * Returns the list of the first level chapters of the CsARR
     *
     * @return CHierarchieCsARR[]
     */
    public static function getChapters()
    {
        $cache = new Cache('CHierarchieCsARR.getChapters', null, Cache::INNER);

        if ($cache->exists()) {
            $chapters = $cache->get();
        } else {
            $chapter  = new self;
            $chapters = $chapter->loadList(['code' => " LIKE '__'"], 'code ASC', null, 'code');
            $cache->put($chapters);
        }

        return $chapters;
    }
}
