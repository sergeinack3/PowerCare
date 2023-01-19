<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Rim\CCDARIMRoleLink;

/**
 * Class CDAEntryRoleLink
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
abstract class CDAEntryRoleLink extends CDAEntry
{
    /** @var CCDARIMRoleLink */
    protected $entry_content;

    /**
     * @return CCDARIMAct
     */
    final public function build(): CCDAClasseBase
    {
        /** @var CCDARIMAct $entry_content */
        $entry_content =  parent::build();

        return $entry_content;
    }
}

