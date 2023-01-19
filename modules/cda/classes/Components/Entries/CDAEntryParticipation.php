<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;

/**
 * Class CDAEntryParticipation
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
abstract class CDAEntryParticipation extends CDAEntry
{
    /** @var CCDARIMParticipation */
    protected $entry_content;

    /**
     * @return CCDARIMParticipation
     */
    final public function build(): CCDAClasseBase
    {
        /** @var CCDARIMParticipation $entry_content */
        $entry_content =  parent::build();

        return $entry_content;
    }
}

