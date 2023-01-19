<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\MDM;

use Ox\Core\CMbObject;
use Ox\Mediboard\Files\CDocumentItem;

/**
 * Class CHL7v2EventMDMT10
 * T10 - Document Replacement Notification with Content
 */
class CHL7v2EventMDMT10 extends CHL7v2EventMDM implements CHL7EventMDMT10
{
    public $code = "T10";

    /** @var string */
    public $struct_code = "T10";

    /**
     * Construct
     *
     * @param string $i18n i18n
     *
     * @return CHL7v2EventMDMT02
     */
    function __construct($i18n = null)
    {
        parent::__construct($i18n);
    }

    /**
     * Build T02 event
     *
     * @param CMbObject $object object
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($object)
    {
        /** @var CDocumentItem $object */
        parent::build($object);
    }
}
