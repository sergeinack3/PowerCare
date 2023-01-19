<?php
/**
 * @package Mediboard\cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Core\CMbObjectSpec;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\System\CSourceFileSystem;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Class CReceiverCDA
 * Receiver CDA
 */
class CReceiverCDA extends CInteropReceiver
{
    /** @var array Sources supportées par un destinataire */
    public static $supported_sources = [
        CSourceFTP::TYPE,
        CSourceSFTP::TYPE,
        CSourceSOAP::TYPE,
        CSourceHTTP::TYPE,
        CSourceFileSystem::TYPE,
    ];

    // DB Table key
    /** @var int */
    public $receiver_cda_id;

    /**
     * Initialize object specification
     *
     * @return CMbObjectSpec
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'receiver_cda';
        $spec->key      = 'receiver_cda_id';
        $spec->messages = [
            "CDA" => ["CCDAEvent"],
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|destinataires_cda";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function sendEvent($event, $object, $data = [], $headers = [], $message_return = false, $soapVar = false)
    {
        if (!parent::sendEvent($event, $object, $data, $headers, $message_return)) {
            return null;
        }

        $event->_receiver = $this;
    }
}
