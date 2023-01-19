<?php

/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Context;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Contextual integration class, to integrate another page view inside Mediboard
 */
class CContextualIntegrationLocation extends CMbObject
{
    // DB Table key
    /** @var int */
    public $contextual_integration_location_id;

    // DB Fields
    /** @var int */
    public $integration_id;

    /** @var string */
    public $location;

    /** @var string */
    public $button_type;

    /** @var CContextualIntegration */
    public $_ref_integration;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "contextual_integration_location";
        $spec->key   = "contextual_integration_location_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                   = parent::getProps();
        $props["integration_id"] = "ref class|CContextualIntegration notNull back|integration_locations";
        $add_location            = CModule::getActive('oxCabinet') ? "|help" : "";
        $props["location"]       = "enum notNull list|patient_header" . $add_location;
        $props["button_type"]    = "enum notNull list|icon|button|button_text";

        return $props;
    }

    /**
     * @throws Exception
     * @return CContextualIntegration
     */
    public function loadRefIntegration(): CContextualIntegration
    {
        return $this->_ref_integration = $this->loadFwdRef("integration_id");
    }

    /**
     * Compte ou charge l'ensemble des intégrations à afficher dans l'aide
     *
     * @param bool $only_ids Load only ids
     * @throws Exception
     * @return CContextualIntegrationLocation[]|int
     */
    public static function loadContextHelp($only_ids = true)
    {
        $ljoin                           = array();
        $ljoin["contextual_integration"] = "contextual_integration.contextual_integration_id = contextual_integration_location.integration_id";

        $where                                             = array();
        $where["contextual_integration.active"]            = " = '1'";
        $where["contextual_integration.group_id"]          = " = '" . CGroups::loadCurrent()->_id . "'";
        $where["contextual_integration_location.location"] = " = 'help'";

        $location = new CContextualIntegrationLocation();

        if ($only_ids) {
            return $location->countList($where, null, $ljoin);
        } else {
            $locations = $location->loadList($where, null, null, "contextual_integration_location_id", $ljoin);

            foreach ($locations as $_location) {
                $_location->loadRefIntegration();
            }

            return $locations;
        }
    }
}
