<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;

/**
 * Class CDAEntryActRelationShip
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
abstract class CDAEntryActRelationShip extends CDAEntry
{
    /** @var CCDARIMActRelationship */
    protected $entry_content;

    /**
     * @return CCDARIMActRelationship
     */
    final public function build(): CCDAClasseBase
    {
        /** @var CCDARIMActRelationship $entry_content */
        $entry_content =  parent::build();

        return $entry_content;
    }
}

