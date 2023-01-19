<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Class CSenderSOAP
 * Interoperability Sender SOAP
 */
class CSenderSOAP extends CInteropSender
{
    // DB Table key
    public $sender_soap_id;

    public $OID;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'sender_soap';
        $spec->key   = 'sender_soap_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|senders_soap";
        $props["user_id"]  .= " back|expediteur_soap";
        $props["OID"]      = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function loadRefsExchangesSources(bool $put_all_sources = false): array
    {
        $source_soap                                       = CExchangeSource::get(
            "$this->_guid",
            CSourceSOAP::TYPE,
            true,
            $this->_type_echange,
            false,
            $put_all_sources
        );
        $this->_ref_exchanges_sources[$source_soap->_guid] = $source_soap;

        return $this->_ref_exchanges_sources;
    }
}
