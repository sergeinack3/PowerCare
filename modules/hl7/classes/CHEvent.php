<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use DOMDocument;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\System\CUserLog;

/**
 * Class CHEvent
 */
abstract class CHEvent implements IShortNameAutoloadable
{
    /** @var array */
    public $msg_codes = array();

    /** @var string */
    public $version;

    public $event_type;

    /** @var CUserLog */
    public $last_log;

    /** @var CMbObject */
    public $object;

    /** @var CReceiverHL7v2 */
    public $_receiver;

    /**
     * Handle input message
     *
     * @param String $msg message
     *
     * @return DOMDocument
     */
    abstract function handle($msg);

    /**
     * Build message
     *
     * @param CMbObject $object Object to use
     *
     * @return void|string|null
     */
    abstract function build($object);

    /**
     * Apply rules
     *
     * @param string $msg
     *
     * @return string
     */
    public function applySequences(string $msg, CInteropActor $actor): string
    {
        return $msg;
    }
}
