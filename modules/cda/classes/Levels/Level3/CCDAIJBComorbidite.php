<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level3;

use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Handle\Level3\CCDAHandleIJBComorbidite;

/**
 * Class CCDAANS
 *
 * @package Ox\Interop\Cda\Levels\Level3
 */
class CCDAIJBComorbidite extends CCDALevel3
{
    /** @var string */
    public const TYPE = self::TYPE_IJB_COMORBIDITE;

    /** @var string */
    public const TYPE_DOC = '2.25.299518904337880959076241620201932965147^1.6C';

    public const OID = '2.25.299518904337880959076241620201932965147.2.1';

    /**
     * @return CCDAHandleIJBComorbidite
     */
    public function getHandle(): ?CCDAHandle
    {
        return new CCDAHandleIJBComorbidite();
    }
}
