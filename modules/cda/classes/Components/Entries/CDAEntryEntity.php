<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\Rim\CCDARIMEntity;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * Class CDAEntryEntity
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
abstract class CDAEntryEntity extends CDAEntry
{
    /** @var CCDARIMEntity */
    protected $entry_content;

    /**
     * @return CCDARIMEntity
     */
    final public function build(): CCDAClasseBase
    {
        /** @var CCDARIMEntity $entry_content */
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
    protected function setCode(CCDARIMEntity $entry_content): void
    {
        // to implement in sub classes
    }

    /**
     * Set Status code on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setStatusCode(CCDARIMEntity $entry_content): void
    {
        // to implement in sub classes
    }
}

