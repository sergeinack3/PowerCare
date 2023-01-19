<?php
/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Class CSenderFTP
 * Interoperability Sender FTP
 */
class CSenderFTP extends CInteropSender
{
    // DB Table key
    public $sender_ftp_id;

    public $after_processing_action;

    /** @var bool */
    public $_delete_file = true;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'sender_ftp';
        $spec->key   = 'sender_ftp_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["group_id"]                .= " back|senders_ftp";
        $props["user_id"]                 .= " back|expediteur_ftp";
        $props["after_processing_action"] = "enum list|none|move|delete default|none";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function loadRefsExchangesSources(bool $put_all_sources = false): array
    {
        $source_ftp                                       = CExchangeSource::get(
            "$this->_guid",
            CSourceFTP::TYPE,
            true,
            $this->_type_echange,
            false,
            $put_all_sources
        );
        $this->_ref_exchanges_sources[$source_ftp->_guid] = $source_ftp;

        return $this->_ref_exchanges_sources;
    }

    /**
     * @inheritdoc
     */
    function read()
    {
    }
}
