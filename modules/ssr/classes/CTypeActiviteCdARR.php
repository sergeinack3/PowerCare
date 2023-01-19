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
 * Catégorie d'activité CdARR
 */
class CTypeActiviteCdARR extends CCdARRObject
{
    public $code;
    public $libelle;
    public $libelle_court;

    static $cached = [];

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'type_activite';
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
        $props["code"]          = "str notNull length|4";
        $props["libelle"]       = "str notNull maxLength|50";
        $props["libelle_court"] = "str notNull maxLength|50";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view      = "($this->code) $this->libelle";
        $this->_shortview = "($this->code) $this->libelle_court";
    }

    /**
     * Get an instance from the code
     *
     * @param string $code Code CdARR
     *
     * @return CTypeActiviteCdARR
     **/
    static function get($code)
    {
        if (!$code) {
            return new self();
        }

        $cache = Cache::getCache(Cache::OUTER);

        if ($type = $cache->get("type_activite_$code")) {
            return $type;
        }

        $type = new self();
        $type->load($code);

        $cache->set("type_activite_$code", $type);

        return $type;
    }
}
