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
 * Intervenant d'activité CdARR
 */
class CIntervenantCdARR extends CCdARRObject
{
    public $code;
    public $libelle;

    static $cached = [];

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'intervenant';
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
        $props["code"]    = "str notNull length|2";
        $props["libelle"] = "str notNull maxLength|50";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view      = "$this->code - " . str_replace("?", "É", utf8_decode($this->libelle));
        $this->_shortview = $this->code;
    }

    /**
     * Get an instance from the code
     *
     * @param string $code Code
     *
     * @return CIntervenantCdARR
     **/
    static function get($code)
    {
        if (!$code) {
            return new self();
        }

        $cache = Cache::getCache(Cache::OUTER);

        if ($intervenant = $cache->get("intervenant_$code")) {
            return $intervenant;
        }

        $intervenant = new self();
        $intervenant->load($code);

        $cache->set("intervenant_$code", $intervenant);

        return $intervenant;
    }
}
