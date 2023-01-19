<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Interop\Eai\CEAIObjectHandler;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\System\CSourceFileSystem;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Destinataire de messages Hprim21
 * Class CDestinataireHprim21
 */
class CDestinataireHprim21 extends CInteropReceiver
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
    public $dest_hprim21_id;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'destinataire_hprim21';
        $spec->key      = 'dest_hprim21_id';
        $spec->messages = [
            "ADM" => ["ADM"],
            "REG" => ["REG"],
            "ORU" => ["ORU"],
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|destinataires_hprim21";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function getFormatObjectHandler(CEAIObjectHandler $objectHandler)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    function sendEvent($evenement, $object, $data = [], $headers = [], $message_return = false, $soapVar = false)
    {
        if (!parent::sendEvent($evenement, $object, $data, $headers, $message_return, $soapVar)) {
            return;
        }

        $evenement->_receiver = $this;
    }
}

