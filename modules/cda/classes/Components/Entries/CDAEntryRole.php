<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * Class CDAEntryRole
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
abstract class CDAEntryRole extends CDAEntry
{
    /** @var CCDARIMRole */
    protected $entry_content;

    /**
     * @return CCDARIMRole
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDARIMRole $entry_content */
        $entry_content =  parent::build();

        // Code
        $this->setCode($entry_content);

        // StatusCode
        $this->setStatusCode($entry_content);

        return $entry_content;
    }

    /**
     * Set code on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setCode(CCDARIMRole $entry_content): void
    {
        // to implement in sub classes
    }

    /**
     * Set Status code on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setStatusCode(CCDARIMRole $entry_content): void
    {
        // to implement in sub classes
    }
}

