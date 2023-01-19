<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network;

/**
 * The Echo message family
 */
class CEcho extends CDicomMessage
{
    /** @var string[] Events */
    public static $evenements = [
        "C-Echo-RQ"  => "CDicomMessageCEchoRQ",
        "C-Echo-RSP" => "CDicomMessageCEchoRSP",
    ];

    /**
     * The constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->type = "Echo";

        parent::__construct();
    }

    /**
     * Retrieve events list of data format
     *
     * @return string[] Events list
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }
}
