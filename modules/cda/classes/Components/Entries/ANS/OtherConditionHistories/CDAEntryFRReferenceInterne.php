<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories\CDAEntryInternalReferences;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;

/**
 * Class CDAEntryFRReferenceInterne
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
class CDAEntryFRReferenceInterne extends CDAEntryInternalReferences
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.4.4.1';

    /** @var CCDACE */
    protected $code;

    /** @var string */
    protected $reference_id;

    /**
     * CDAEntryFRReferenceInterne constructor.
     *
     * @param CCDAFactory $factory
     * @param CCDACE      $code
     * @param string      $reference_id
     */
    public function __construct(CCDAFactory $factory, CCDACE $code, string $reference_id)
    {
        parent::__construct($factory);

        $this->code         = $code;
        $this->reference_id = $reference_id;
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     *
     * @return void
     */
    protected function buildContent(CCDAClasseCda $act): void
    {
        // not use
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     */
    protected function setCode(CCDARIMAct $act): void
    {
        $act->setCode($this->code);
    }

    /**
     * @param CCDAClasseCda $entry_content
     */
    protected function setId(CCDAClasseCda $entry_content): void
    {
        if (method_exists($entry_content, 'appendId')) {
            CCDADocTools::setId($entry_content, $this->reference_id);
        }
    }
}
