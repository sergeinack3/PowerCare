<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\System\CExchangeSource;

/**
 * A Dicom sender
 */
class CDicomSender extends CInteropSender
{

    /**
     * Table Key
     *
     * @var integer
     */
    public $dicom_sender_id = null;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "dicom_sender";
        $spec->key   = "dicom_sender_id";

        return $spec;
    }

    /**
     * @inheritDoc
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|dicom_sender";
        $props["user_id"]  .= " back|dicom_sender";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function loadRefsExchangesSources(bool $put_all_sources = false): array
    {
        $source_dicom                                       = CExchangeSource::get(
            "$this->_guid",
            "dicom",
            true,
            $this->_type_echange,
            false,
            $put_all_sources
        );
        $this->_ref_exchanges_sources[$source_dicom->_guid] = $source_dicom;

        return $this->_ref_exchanges_sources;
    }
}
