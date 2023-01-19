<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Voc\CCDAProbabilityDistributionType;

/**
 * CCDAPPD_PQ class
 */
class CCDAPPD_PQ extends CCDAPQ
{

    /**
     * The primary measure of variance/uncertainty of the
     * value (the square root of the sum of the squares of
     * the differences between all data points and the mean).
     * The standard deviation is used to normalize the data
     * for computing the distribution function. Applications
     * that cannot deal with probability distributions can
     * still get an idea about the confidence level by looking
     * at the standard deviation.
     *
     * @var CCDAPQ
     */
    public $standardDeviation;

    /**
     * A code specifying the type of probability distribution.
     * Possible values are as shown in the attached table.
     * The NULL value (unknown) for the type code indicates
     * that the probability distribution type is unknown. In
     * that case, the standard deviation has the meaning of an
     * informal guess.
     *
     * @var CCDAProbabilityDistributionType
     */
    public $distributionType;

    /**
     * retourne le nom du type CDA
     *
     * @return string
     */
    function getNameClass()
    {
        $name = CClassMap::getSN($this);
        $name = substr($name, 4);

        return $name;
    }

    /**
     * Getter DistributionType
     *
     * @return CCDAProbabilityDistributionType
     */
    public function getDistributionType()
    {
        return $this->distributionType;
    }

    /**
     * Setter distributionType
     *
     * @param String $distributionType String
     *
     * @return void
     */
    public function setDistributionType($distributionType)
    {
        if (!$distributionType) {
            $this->distributionType = null;

            return;
        }
        $proba = new CCDAProbabilityDistributionType();
        $proba->setData($distributionType);
        $this->distributionType = $proba;
    }

    /**
     * Getter standardDeviation
     *
     * @return CCDAPQ
     */
    public function getStandardDeviation()
    {
        return $this->standardDeviation;
    }

    /**
     * Setter standardDeviation
     *
     * @param CCDAPQ $standardDeviation \CCDAPQ
     *
     * @return void
     */
    public function setStandardDeviation($standardDeviation)
    {
        $this->standardDeviation = $standardDeviation;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["standardDeviation"] = "CCDAPQ xml|element max|1";
        $props["distributionType"]  = "CCDAProbabilityDistributionType xml|attribute";

        return $props;
    }
}
