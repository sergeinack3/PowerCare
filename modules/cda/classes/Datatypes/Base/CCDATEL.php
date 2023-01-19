<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A telephone number (voice or fax), e-mail address, or
 * other locator for a resource (information or service)
 * mediated by telecommunication equipment. The address
 * is specified as a Universal Resource Locator (URL)
 * qualified by time specification and use codes that help
 * in deciding which address to use for a given time and
 * purpose.
 */
class CCDATEL extends CCDAURL
{

    /**
     * Specifies the periods of time during which the
     * telecommunication address can be used.  For a
     * telephone number, this can indicate the time of day
     * in which the party can be reached on that telephone.
     * For a web address, it may specify a time range in
     * which the web content is promised to be available
     * under the given address.
     *
     * @var CCDASXCM_TS
     */
    public $useablePeriod;

    /**
     * One or more codes advising a system or user which
     * telecommunication address in a set of like addresses
     * to select for a given telecommunication need.
     *
     * @var CCDAset_TelecommunicationAddressUse
     */
    public $use;

    /**
     * Getter use
     *
     * @return CCDAset_TelecommunicationAddressUse
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * Setter use
     *
     * @param String[] $use String[]
     *
     * @return void
     */
    public function setUse($use)
    {
        $setTel = new CCDAset_TelecommunicationAddressUse();
        foreach ($use as $_use) {
            $setTel->addData($_use);
        }
        $this->use = $setTel;
    }

    /**
     * Getter useablePeriod
     *
     * @return CCDASXCM_TS
     */
    public function getUseablePeriod()
    {
        return $this->useablePeriod;
    }

    /**
     * Setter useablePeriod
     *
     * @param CCDASXCM_TS $useablePeriod CCDASXCM_TS
     *
     * @return void
     */
    public function setUseablePeriod($useablePeriod)
    {
        $this->useablePeriod = $useablePeriod;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $props["useablePeriod"] = "CCDASXCM_TS xml|element";
        $props["use"]           = "CCDAset_TelecommunicationAddressUse xml|attribute";

        return $props;
    }
}
