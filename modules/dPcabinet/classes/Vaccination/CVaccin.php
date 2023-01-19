<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Vaccination;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Vaccine object
 */
class CVaccin implements IShortNameAutoloadable
{
    public $type;
    public $shortname;
    public $longname;
    public $color;
    public $recall;
    public $repeat_recall;

    /**
     * CVaccin constructor.
     *
     * @param string $type
     * @param string $shortname
     * @param string $longname
     * @param string $color
     * @param array $recall
     * @param null $repeat_recall
     */
    public function __construct($type = "", $shortname = "", $longname = "", $color = "#000", $recall = array(), $repeat_recall = null)
    {
        $this->type = $type;
        $this->shortname = $shortname;
        $this->longname = $longname;
        $this->color = $color;
        $this->recall = $recall;
        $this->repeat_recall = $repeat_recall;
    }
}
