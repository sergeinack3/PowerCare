<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network;

/**
 * The Find message family
 */
class CFind extends CDicomMessage
{
    /** @var string[] Events */
    public static $evenements = [
        "C-Find-RQ"        => "CDicomMessageCFindRQ",
        "C-Find-RSP"       => "CDicomMessageCFindRSP",
        "C-Cancel-Find-RQ" => "CDicomMessageCCancelFindRQ",
        "Datas"            => "CDicomMessageCFindData",
    ];

    /**
     * The constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->type = "Find";

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
