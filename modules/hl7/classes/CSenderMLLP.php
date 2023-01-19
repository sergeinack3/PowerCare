<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Class CSenderMLLP
 * Interoperability Sender MLLP
 */
class CSenderMLLP extends CInteropSender
{
    // DB Table key
    public $sender_mllp_id;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'sender_mllp';
        $spec->key   = 'sender_mllp_id';

        return $spec;
    }

    /**
     * @inheritDoc
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|senders_mllp";
        $props["user_id"]  .= " back|expediteur_mllp";

        return $props;
    }

    /**
     * @see parent::read
     */
    function read()
    {
        $this->loadRefsExchangesSources();
    }

    /**
     * @inheritdoc
     */
    public function loadRefsExchangesSources(bool $put_all_sources = false): array
    {
        $source_mllp                                       = CExchangeSource::get(
            "$this->_guid",
            "mllp",
            true,
            $this->_type_echange,
            false,
            $put_all_sources
        );
        $this->_ref_exchanges_sources[$source_mllp->_guid] = $source_mllp;

        return $this->_ref_exchanges_sources;
    }
}
