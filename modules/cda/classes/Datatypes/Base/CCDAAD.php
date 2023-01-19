<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * Mailing and home or office addresses. A sequence of
 * address parts, such as street or post office Box, city,
 * postal code, country, etc.
 *
 * @property $delimiter
 * @property $country
 * @property $state
 * @property $county
 * @property $city
 * @property $postalCode
 * @property $streetAddressLine
 * @property $houseNumber
 * @property $houseNumberNumeric
 * @property $direction
 * @property $streetName
 * @property $streetNameBase
 * @property $streetNameType
 * @property $additionalLocator
 * @property $unitID
 * @property $unitType
 * @property $careOf
 * @property $censusTract
 * @property $deliveryAddressLine
 * @property $deliveryInstallationType
 * @property $deliveryInstallationArea
 * @property $deliveryInstallationQualifier
 * @property $deliveryMode
 * @property $deliveryModeIdentifier
 * @property $buildingNumberSuffix
 * @property $postBox
 * @property $precinct
 * @property $useablePeriod
 */
class CCDAAD extends CCDAANY
{

    public $delimiter                     = [];
    public $country                       = [];
    public $state                         = [];
    public $county                        = [];
    public $city                          = [];
    public $postalCode                    = [];
    public $streetAddressLine             = [];
    public $houseNumber                   = [];
    public $houseNumberNumeric            = [];
    public $direction                     = [];
    public $streetName                    = [];
    public $streetNameBase                = [];
    public $streetNameType                = [];
    public $additionalLocator             = [];
    public $unitID                        = [];
    public $unitType                      = [];
    public $careOf                        = [];
    public $censusTract                   = [];
    public $deliveryAddressLine           = [];
    public $deliveryInstallationType      = [];
    public $deliveryInstallationArea      = [];
    public $deliveryInstallationQualifier = [];
    public $deliveryMode                  = [];
    public $deliveryModeIdentifier        = [];
    public $buildingNumberSuffix          = [];
    public $postBox                       = [];
    public $precinct                      = [];

    /**
     * A General Timing Specification (GTS) specifying the
     * periods of time during which the address can be used.
     * This is used to specify different addresses for
     * different times of the year or to refer to historical
     * addresses.
     *
     * @var array
     */
    public $useablePeriod = [];

    /**
     * A set of codes advising a system or user which address
     * in a set of like addresses to select for a given purpose.
     *
     * @var CCDAset_PostalAddressUse
     */
    public $use;

    /**
     * A boolean value specifying whether the order of the
     * address parts is known or not. While the address parts
     * are always a Sequence, the order in which they are
     * presented may or may not be known. Where this matters, the
     * isNotOrdered property can be used to convey this
     * information.
     *
     * @var CCDA_base_bl
     */
    public $isNotOrdered;

    /**
     * Getter isNotOrdered
     *
     * @return CCDA_base_bl
     */
    public function getIsNotOrdered()
    {
        return $this->isNotOrdered;
    }

    /**
     * Setter isNotOrdered
     *
     * @param String $isNotOrdered String
     *
     * @return void
     */
    public function setIsNotOrdered($isNotOrdered)
    {
        if (!$isNotOrdered) {
            $this->isNotOrdered = null;

            return;
        }
        $isNotOrd = new CCDA_base_bl;
        $isNotOrd->setData($isNotOrdered);
        $this->isNotOrdered = $isNotOrd;
    }

    /**
     * Getter use
     *
     * @return CCDAset_PostalAddressUse
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
        $setPost = new CCDAset_PostalAddressUse();
        foreach ($use as $_use) {
            $setPost->addData($_use);
        }
        $this->use = $setPost;
    }

    /**
     * retourne le tableau du champ spécifié
     *
     * @param String $name String
     *
     * @return mixed
     */
    function get($name)
    {
        return $this->$name;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                                  = parent::getProps();
        $props["delimiter"]                     = "CCDAadxp_delimiter xml|element";
        $props["country"]                       = "CCDAadxp_country xml|element";
        $props["state"]                         = "CCDAadxp_state xml|element";
        $props["county"]                        = "CCDAadxp_county xml|element";
        $props["city"]                          = "CCDAadxp_city xml|element";
        $props["postalCode"]                    = "CCDAadxp_postalCode xml|element";
        $props["streetAddressLine"]             = "CCDAadxp_streetAddressLine xml|element";
        $props["houseNumber"]                   = "CCDAadxp_houseNumber xml|element";
        $props["houseNumberNumeric"]            = "CCDAadxp_houseNumberNumeric xml|element";
        $props["direction"]                     = "CCDAadxp_direction xml|element";
        $props["streetName"]                    = "CCDAadxp_streetName xml|element";
        $props["streetNameBase"]                = "CCDAadxp_streetNameBase xml|element";
        $props["streetNameType"]                = "CCDAadxp_streetNameType xml|element";
        $props["additionalLocator"]             = "CCDAadxp_additionalLocator xml|element";
        $props["unitID"]                        = "CCDAadxp_unitID xml|element";
        $props["unitType"]                      = "CCDAadxp_unitType xml|element";
        $props["careOf"]                        = "CCDAadxp_careOf xml|element";
        $props["censusTract"]                   = "CCDAadxp_censusTract xml|element";
        $props["deliveryAddressLine"]           = "CCDAadxp_deliveryAddressLine xml|element";
        $props["deliveryInstallationType"]      = "CCDAadxp_deliveryInstallationType xml|element";
        $props["deliveryInstallationArea"]      = "CCDAadxp_deliveryInstallationArea xml|element";
        $props["deliveryInstallationQualifier"] = "CCDAadxp_deliveryInstallationQualifier xml|element";
        $props["deliveryMode"]                  = "CCDAadxp_deliveryMode xml|element";
        $props["deliveryModeIdentifier"]        = "CCDAadxp_deliveryModeIdentifier xml|element";
        $props["buildingNumberSuffix"]          = "CCDAadxp_buildingNumberSuffix xml|element";
        $props["postBox"]                       = "CCDAadxp_postBox xml|element";
        $props["precinct"]                      = "CCDAadxp_precinct xml|element xml|element";
        $props["useablePeriod"]                 = "CCDASXCM_TS xml|element";
        $props["use"]                           = "CCDAset_PostalAddressUse xml|attribute";
        $props["isNotOrdered"]                  = "CCDA_base_bl xml|attribute";
        $props["data"]                          = "str xml|data";

        return $props;
    }

    /**
     * Ajoute l'instance dans le champ spécifié
     *
     * @param String $name  String
     * @param mixed  $value mixed
     *
     * @return void
     */
    function append($name, $value)
    {
        array_push($this->$name, $value);
    }

    /**
     * Efface le tableau du champ spécifié
     *
     * @param String $name String
     *
     * @return void
     */
    function resetListdata($name)
    {
        $this->$name = [];
    }
}
