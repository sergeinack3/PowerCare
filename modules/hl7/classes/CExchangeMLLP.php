<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CModelObject;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * MLLP exchange
 */
class CExchangeMLLP extends CExchangeTransportLayer
{
    public $exchange_mllp_id;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'exchange_mllp';
        $spec->key      = 'exchange_mllp_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["source_class"] = "enum list|CSourceMLLP";
        $props["source_id"]    .= " cascade back|exchange_mllp";

        return $props;
    }
}
