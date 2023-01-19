<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A concept qualifier code with optionally named role.
 * Both qualifier role and value codes must be defined by
 * the coding system.  For example, if SNOMED RT defines a
 * concept "leg", a role relation "has-laterality", and
 * another concept "left", the concept role relation allows
 * to add the qualifier "has-laterality: left" to a primary
 * code "leg" to construct the meaning "left leg".
 */
class CCDACR extends CCDAANY
{

    /**
     * Specifies the manner in which the concept role value
     * contributes to the meaning of a code phrase.  For
     * example, if SNOMED RT defines a concept "leg", a role
     * relation "has-laterality", and another concept "left",
     * the concept role relation allows to add the qualifier
     * "has-laterality: left" to a primary code "leg" to
     * construct the meaning "left leg".  In this example
     * "has-laterality" is the CR.name.
     *
     * @var CCDACV
     */
    public $name;

    /**
     * The concept that modifies the primary code of a code
     * phrase through the role relation.  For example, if
     * SNOMED RT defines a concept "leg", a role relation
     * "has-laterality", and another concept "left", the
     * concept role relation allows adding the qualifier
     * "has-laterality: left" to a primary code "leg" to
     * construct the meaning "left leg".  In this example
     * "left" is the CR.value.
     *
     * @var CCDACD
     */
    public $value;

    /**
     * Indicates if the sense of the role name is inverted.
     * This can be used in cases where the underlying code
     * system defines inversion but does not provide reciprocal
     * pairs of role names. By default, inverted is false.
     *
     * @var CCDA_base_bn
     */
    public $inverted;

    /**
     * Getter Inverted
     *
     * @return CCDA_base_bn
     */
    public function getInverted()
    {
        return $this->inverted;
    }

    /**
     * Setter inverted
     *
     * @param String $inverted String
     *
     * @return void
     */
    public function setInverted($inverted)
    {
        if (!$inverted) {
            $this->inverted = null;

            return;
        }
        $invert = new CCDA_base_bn();
        $invert->setData($inverted);
        $this->inverted = $invert;
    }

    /**
     * Getter Name
     *
     * @return CCDACV
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter Name
     *
     * @param CCDACV $name \CCDACV
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter Value
     *
     * @return CCDACD
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Setter Value
     *
     * @param CCDACD $value \CCDACD
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["name"]     = "CCDACV xml|element max|1";
        $props["value"]    = "CCDACD xml|element max|1";
        $props["inverted"] = "CCDA_base_bn xml|attribute default|false";

        return $props;
    }
}
