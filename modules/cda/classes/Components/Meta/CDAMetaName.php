<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Meta;

use Ox\Core\CPerson;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_family;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_given;
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;

class CDAMetaName extends CDAMeta
{
    /** @var array */
    public const OPTIONS_DEFAULTS = [];

    /** @var CPerson */
    protected $person;

    /**
     * CDAMetaName constructor.
     *
     * @param CCDAFactory $factory
     * @param CPerson     $person
     * @param array       $override_options
     */
    public function __construct(CCDAFactory $factory, CPerson $person, array $override_options = [])
    {
        parent::__construct($factory);

        $this->content = new CCDAPN();
        $this->person  = $person;
        $this->options = $this->mergeOptions($override_options);
    }

    /**
     * @return CCDAPN
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDAPN $name */
        $name = parent::build();

        $enxp = new CCDA_en_family();
        $enxp->setData($this->person->_p_last_name);
        $name->append("family", $enxp);

        $enxp = new CCDA_en_given();
        $enxp->setData($this->person->_p_first_name);
        $name->append("given", $enxp);

        return $name;
    }
}
