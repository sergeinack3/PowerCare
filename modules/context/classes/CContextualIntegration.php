<?php

/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Context;

use Exception;
use DOMDocument;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Contextual integration class, to integrate another page view inside Mediboard
 */
class CContextualIntegration extends CMbObject
{
    /** @var array */
    public static $patterns = array(
        "user",
        "ip",
        "ipp",
        "nda",
    );

    // DB Table key
    /** @var int */
    public $contextual_integration_id;

    // DB Fields
    /** @var bool */
    public $active;

    /** @var int */
    public $group_id;

    /** @var string */
    public $url;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var string */
    public $icon_url;

    /** @var string */
    public $display_mode;

    /** @var string */
    public $icon_name;

    /** @var string */
    public $_url;

    /** @var CContextualIntegrationLocation[] */
    public $_ref_locations;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "contextual_integration";
        $spec->key   = "contextual_integration_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                 = parent::getProps();
        $props["active"]       = "bool default|0";
        $props["group_id"]     = "ref class|CGroups notNull back|contextual_integrations";
        $props["url"]          = "url notNull";
        $props["title"]        = "str notNull";
        $props["description"]  = "text";
        $props["icon_url"]     = "str";
        $props["icon_name"]    = "str";
        $props["display_mode"] = "enum list|modal|popup|current_tab|new_tab"; //"enum list|modal|popup|tooltip|current_tab|new_tab|none"

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view = $this->title;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        if (!$this->_id) {
            if (!$this->group_id) {
                $this->group_id = CGroups::loadCurrent()->_id;
            }
        }

        return parent::store();
    }

    /**
     * @throws Exception
     * @return CContextualIntegrationLocation[]
     */
    public function loadRefsLocations(): array
    {
        return $this->_ref_locations = $this->loadBackRefs("integration_locations");
    }

    /**
     * Makes the URL from variopus informations
     * @param CMbObject $object Object to make the URL for
     * @throws Exception
     * @return string
     */
    public function makeURL(CMbObject $object): string
    {
        if ($this->_url) {
            return $this->_url;
        }

        $values = array_fill_keys(self::$patterns, null);

        $values["user"] = CUser::get()->user_username;
        $values["ip"]   = CAppUI::$instance->ip;

        if ($object instanceof IPatientRelated) {
            $patient = $object->loadRelPatient();
            $patient->loadIPP();
            $values["ipp"] = $patient->_IPP;
        }

        if ($object instanceof CSejour) {
            $object->loadNDA();
            $values["nda"] = $object->_NDA;
        }

        $url = $this->url;
        foreach ($values as $_from => $_value) {
            $url = str_replace("%$_from%", urlencode($_value), $url);
        }

        return $this->_url = $url;
    }

    /**
     * Permet de retourner la liste des icônes FontAwesome
     *
     * @return array
     */
    public static function iconList(): array
    {
        $icons = array();

        $fa_solid = __DIR__ . "/../../../style/mediboard_ext/vendor/fonts/font-awesome/webfonts/fa-solid-900.svg";
        $svg = new DOMDocument();
        $svg->load($fa_solid);
        foreach ($svg->getElementsByTagName('glyph') as $glyph) {
            $icon = $glyph->attributes->item(0)->value;
            $icons[$icon] = "fas";
        }

        $fa_brand = __DIR__ . "/../../../style/mediboard_ext/vendor/fonts/font-awesome/webfonts/fa-brands-400.svg";
        $svg = new DOMDocument();
        $svg->load($fa_brand);
        foreach ($svg->getElementsByTagName('glyph') as $glyph) {
            $icon = $glyph->attributes->item(0)->value;
            $icons[$icon] = "fab";
        }

        ksort($icons);

        return $icons;
    }
}
