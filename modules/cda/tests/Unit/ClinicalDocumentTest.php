<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit;

use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ClinicalDocument;
use Ox\Tests\OxUnitTestCase;

class ClinicalDocumentTest extends OxUnitTestCase
{
    public function testSimplifiedDocument()
    {
        $document = new CCDAPOCD_MT000040_ClinicalDocument();
        $document->setTitle('Un document de test');
        $document->setTypeId();
        $dom = $document->toXML();

        $clinicalElements = $dom->getElementsByTagName('POCD_MT000040.ClinicalDocument');
        $this->assertEquals(1, $clinicalElements->length, "Test that there is only one clinical document");

        // create the expected document from XML string
        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<POCD_MT000040.ClinicalDocument><typeId root="2.16.840.1.113883.1.3" extension="POCD_HD000040"/><title>Un document de test</title></POCD_MT000040.ClinicalDocument>
';
        $this->assertEquals($dom->saveXML(), $expected);
    }
}
