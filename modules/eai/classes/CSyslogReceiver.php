<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Interop\Hl7\CSourceMLLP;

/**
 * Syslog Receiver
 */
class CSyslogReceiver extends CInteropReceiver
{
    /** @var array Sources supportées par un destinataire */
    public static $supported_sources = [
        CSourceMLLP::TYPE
    ];

    /** @var integer Primary key */
    public $syslog_receiver_id;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = "syslog_receiver";
        $spec->key      = "syslog_receiver_id";
        $spec->messages = [
            "iti" => ["CSyslogITI"],
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|destinataires_syslog";

        return $props;
    }
}
