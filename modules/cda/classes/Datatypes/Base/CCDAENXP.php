<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityNamePartType;

/**
 * A character string token representing a part of a name.
 * May have a type code signifying the role of the part in
 * the whole entity name, and a qualifier code for more detail
 * about the name part type. Typical name parts for person
 * names are given names, and family names, titles, etc.
 */
class CCDAENXP extends CCDAST
{

    /**
     * Indicates whether the name part is a given name, family
     * name, prefix, suffix, etc.
     *
     * @var CCDAEntityNamePartType
     */
    public $partType;

    /**
     * The qualifier is a set of codes each of which specifies
     * a certain subcategory of the name part in addition to
     * the main name part type. For example, a given name may
     * be flagged as a nickname, a family name may be a
     * pseudonym or a name of public records.
     *
     * @var CCDAset_EntityNamePartQualifier
     */
    public $qualifier;

    /**
     * Getter partType
     *
     * @return CCDAEntityNamePartType
     */
    public function getPartType()
    {
        return $this->partType;
    }

    /**
     * Setter partType
     *
     * @param String $partType String
     *
     * @return void
     */
    public function setPartType($partType)
    {
        if (!$partType) {
            $this->partType = null;

            return;
        }
        $partT = new CCDAEntityNamePartType();
        $partT->setData($partType);
        $this->partType = $partT;
    }

    /**
     * Getter qualifier
     *
     * @return CCDAset_EntityNamePartQualifier
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * Setter qualifier
     *
     * @param String[] $qualifier String[]
     *
     * @return void
     */
    public function setQualifier($qualifier)
    {
        $setEnti = new CCDAset_EntityNamePartQualifier();
        foreach ($qualifier as $_qualifier) {
            $setEnti->addData($_qualifier);
        }
        $this->qualifier = $setEnti;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props              = parent::getProps();
        $props["partType"]  = "CCDAEntityNamePartType xml|attribute";
        $props["qualifier"] = "CCDAset_EntityNamePartQualifier xml|attribute";

        return $props;
    }
}
