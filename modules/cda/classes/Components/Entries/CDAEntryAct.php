<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * Class CDAEntryAct
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
abstract class CDAEntryAct extends CDAEntry
{
    /** @var CCDARIMAct */
    protected $entry_content;

    /**
     * @return CCDARIMAct
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDARIMAct $entry_content */
        $entry_content =  parent::build();

        // Code
        $this->setCode($entry_content);

        // Text
        $this->setText($entry_content);

        // Title
        $this->setTitle($entry_content);

        // StatusCode
        $this->setStatusCode($entry_content);

        return $entry_content;
    }

    /**
     * Set code on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setCode(CCDARIMAct $entry_content): void
    {
        // to implement in sub classes
    }

    /**
     * Set Title on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setTitle(CCDARIMAct $entry_content): void
    {
        // to implement in sub classes
    }

    /**
     * Set Status code on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setStatusCode(CCDARIMAct $entry_content): void
    {
        // to implement in sub classes
    }

    /**
     * Set Text on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setText(CCDARIMAct $entry_content): void
    {
        // to implement in sub classes
    }
}

