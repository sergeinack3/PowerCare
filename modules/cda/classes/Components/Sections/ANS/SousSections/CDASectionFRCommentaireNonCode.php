<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\SousSections;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionFRCommentaireNonCode extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.73';

    /** @var string */
    protected $content;

    /**
     * CDASectionFRCommentaireNonCode constructor.
     *
     * @param CCDAFactory $factory
     * @param string      $content
     */
    public function __construct(CCDAFactory $factory, string $content)
    {
        parent::__construct($factory);
        
        // Conformity IHE
        $this->addTemplateIds('1.3.6.1.4.1.19376.1.4.1.2.16');

        // Conformity CDA
        $this->addTemplateIds('2.16.840.1.113883.10.12.201');

        $this->content = $content;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '55112-7');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setText($section, $this->content);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Autres informations');
    }
}
