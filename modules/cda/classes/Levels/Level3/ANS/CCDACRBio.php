<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level3\ANS;


use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Handle\Level3\ANS\CCDAHandleCRBio;

/**
 * Class CCDALdlEes
 *
 * @package Ox\Interop\Cda\Levels\Level3\ANS
 */
class CCDACRBio extends CCDAANS
{
    /** @var string */
    public const TYPE = self::TYPE_CR_BIO;

    /** @var string */
    public const TYPE_DOC = '2.16.840.1.113883.6.1^11502-2';

    /**
     * @return CCDAHandle|null
     */
    public function getHandle(): ?CCDAHandle
    {
        return new CCDAHandleCRBio();
    }
}
