<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\ReasonsForCare;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\IHE\ReasonsForCare\CDASectionReasonForReferral;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionFRRaisonRecommandationNonCode extends CDASectionReasonForReferral
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.127';

    /** @var string */
    protected $content;

    public function __construct(CCDAFactory $factory, string $content)
    {
        parent::__construct($factory);

        $this->content = $content;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '42349-1');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setText($section, $this->content);
    }

    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, "Motif d'hospitalisation");
    }
}
