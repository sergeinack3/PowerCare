<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Interop\Cda\Documents\CCDADocumentCDA;
use Ox\Interop\Cda\Levels\Level1\ANS\CCDAANS;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ClinicalDocument;
use Ox\Tests\OxUnitTestCase;

class CDAPOCD_MT000040_ClinicalDocumentTest extends OxUnitTestCase
{
    /**
     * Test generate metadata
     *
     * @return void
     */
    public function testGenerateMetadata(): void
    {
        $cda_factory                  = new CCDAANS(new CMbObject());
        $cda_factory->id_cda          = '1.2.250.1.213.1.1.9';
        $cda_factory->realm_code      = 'FR';
        $cda_factory->langage         = 'fr-FR';
        $cda_factory->confidentialite = ['code' => 'TEST'];
        $cda_factory->date_creation   = CMbDT::dateTime();
        $cda_factory->version         = '10';
        $cda_factory->id_cda_lot      = "1.25.4";
        $cda_factory->nom             = 'CDA de test';
        $cda_factory->code            = ['code' => 'SYNTH'];

        $documentCDA = new CCDADocumentCDA($cda_factory);

        $document = new CCDAPOCD_MT000040_ClinicalDocument();
        $documentCDA->generateMetadata($document);

        $this->assertTrue($document->getId()->validate());
        $this->assertTrue($document->getRealmCode()->validate());
        $this->assertTrue($document->getLanguageCode()->validate());
        $this->assertTrue($document->getConfidentialityCode()->validate());
        $this->assertTrue($document->getEffectiveTime()->validate());
        $this->assertTrue($document->getVersionNumber()->validate());
        $this->assertTrue($document->getSetId()->validate());
        $this->assertTrue($document->getTitle()->validate());
        $this->assertTrue($document->getCode()->validate());
    }

    public function testGenerateTemplatesId(): void
    {
        $cda_factory               = new CCDAANS(new CMbObject());
        $cda_factory->templateId[] = $cda_factory->createTemplateID(
            "2.16.840.1.113883.2.8.2.1",
            "HL7 France"
        );
        $cda_factory->templateId[] = $cda_factory->createTemplateID(
            "1.2.250.1.213.1.1.1.1",
            "CI-SIS"
        );

        $documentCDA = new CCDADocumentCDA($cda_factory);

        $document = new CCDAPOCD_MT000040_ClinicalDocument();
        $documentCDA->generateTemplatesId($document);

        foreach ($document->getTemplateID() as $_template) {
            $this->assertTrue($_template->validate());
        }
    }
}
