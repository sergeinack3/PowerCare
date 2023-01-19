<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Import;

use DOMElement;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CStoredObject;
use Ox\Core\Import\CMbObjectExport;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Tests\Fixtures\ObjectExportFixtures;
use Ox\Mediboard\System\CContentHTML;
use Ox\Tests\OxUnitTestCase;

class CMbObjectExportTest extends OxUnitTestCase
{
    private $patient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        CFile::registerPrivateDirectory();
    }

    /**
     * @param int|string $expected_id
     * @dataProvider getObjectTargetForExportProvider
     */
    public function testGetObjectTargetForExport(
        CDocumentItem $object,
        string $expected_class,
        $expected_id
    ): void {
        $this->assertEquals([$expected_class, $expected_id], CMbObjectExport::getObjectTargetForExport($object));
    }

    public function getObjectTargetForExportProvider(): array
    {
        $presc_file = $this->getObjectFromFixturesReference(
            CFile::class,
            ObjectExportFixtures::EXPORT_TAG_PRESCRIPTION_FILE
        );
        $sejour     = $presc_file->loadFwdRef('object_id')->loadFwdRef('object_id');

        $facture_file    = $this->getObjectFromFixturesReference(
            CFile::class,
            ObjectExportFixtures::EXPORT_TAG_FACTURE_FILE
        );
        $patient_facture = $facture_file->loadFwdRef('object_id')->loadFwdRef('patient_id');

        $devis_file = $this->getObjectFromFixturesReference(
            CFile::class,
            ObjectExportFixtures::EXPORT_TAG_DEVIS_FILE
        );
        $consult    = $devis_file->loadFwdRef('object_id')->loadFwdRef('codable_id');

        $patient_file = $this->getObjectFromFixturesReference(
            CFile::class,
            ObjectExportFixtures::EXPORT_TAG_PATIENT_FILE
        );
        $patient      = $patient_file->loadFwdRef('object_id');

        return [
            'prescription' => [$presc_file, $sejour->_class, $sejour->_id],
            'facture'      => [$facture_file, $patient_facture->_class, $patient_facture->_id],
            'devis'        => [$devis_file, $consult->_class, $consult->_id],
            'patient'      => [$patient_file, $patient->_class, $patient->_id],
        ];
    }

    public function testConstructNoPerm(): void
    {
        $patient  = $this->getPatient();
        $old_perm = CPermObject::$users_cache[CMediusers::get()->_id]['CPatient'][$patient->_id] ?? null;

        CPermObject::$users_cache[CMediusers::get()->_id]['CPatient'][$patient->_id] = 0;

        $this->expectExceptionObject(new CMbException("Permission denied"));

        new CMbObjectExport($patient);

        if ($old_perm) {
            CPermObject::$users_cache[CMediusers::get()->_id]['CPatient'][$patient->_id] = $old_perm;
        }
    }

    /**
     * @dataProvider canExportObjectProvider
     */
    public function testCanExportObject(
        CMbObjectExport $export,
        CStoredObject $object,
        int $depth,
        bool $expected
    ): void {
        $this->assertEquals($expected, $this->invokePrivateMethod($export, 'canExportObject', $object, $depth));
    }

    public function testCreateObjectNode(): void
    {
        $doc     = new CMbXMLDocument();
        $patient = $this->getPatient();

        $expected_node = $doc->createElement('object');
        $expected_node->setAttribute('id', $patient->_guid);
        $expected_node->setAttribute('class', $patient->_class);
        $expected_node->setIdAttribute('id', true);

        $node = $this->invokePrivateMethod(new CMbObjectExport(), 'createObjectNode', $doc, $patient);


        $this->assertEquals($expected_node, $node);
    }

    public function testAddFwRefFieldNotARef(): void
    {
        $doc     = new CMbXMLDocument();
        $patient = $this->getPatient();

        $export = new CMbObjectExport();

        $node = $this->invokePrivateMethod($export, 'createObjectNode', $doc, $patient);

        $expected_node = clone $node;

        $this->invokePrivateMethod($export, 'addFwRefField', $node, $patient, 'nom', 1);

        $this->assertEquals($expected_node, $node);
    }

    public function testAddFwRefFieldOk(): void
    {
        $doc = new CMbXMLDocument();

        $patient = $this->getPatient();

        $file               = new CFile();
        $file->object_class = $patient->_class;
        $file->object_id    = $patient->_id;
        $file->file_name    = 'toto';
        $file->file_date    = CMbDT::dateTime();
        $file->setContent('test');
        $file->updateFormFields();
        $file->fillFields();
        $this->storeOrFailed($file);


        $export = new CMbObjectExport();
        $export->setForwardRefsTree(['CFile' => ['object_id']]);

        /** @var DOMElement $node */
        $node = $this->invokePrivateMethod($export, 'createObjectNode', $doc, $file);

        $this->assertFalse($node->hasAttribute('object_id'));

        $this->invokePrivateMethod($export, 'addFwRefField', $node, $file, 'object_id', 1);

        $this->assertTrue($node->hasAttribute('object_id'));
        $this->assertEquals($file->object_class . '-' . $file->object_id, $node->getAttribute('object_id'));
    }

    public function testAddScalarFieldEmptyValue(): void
    {
        /** @var CFile $file */
        $file = new CFile();

        $export               = new CMbObjectExport();
        $export->empty_values = false;

        $doc = new CMbXMLDocument();
        /** @var DOMElement $node */
        $node = $this->invokePrivateMethod($export, 'createObjectNode', $doc, $file);

        $this->assertFalse($node->hasChildNodes());

        $this->invokePrivateMethod($export, 'addScalarField', $doc, $node, $file, 'file_name', null);

        $this->assertFalse($node->hasChildNodes());

        $export->empty_values = true;

        $this->invokePrivateMethod($export, 'addScalarField', $doc, $node, $file, 'file_name', null);

        $this->assertTrue($node->hasChildNodes());

        $this->assertEquals('file_name', $node->firstChild->getAttribute('name'));
        $this->assertEquals(null, $node->firstChild->nodeValue);
    }

    public function testAddScalarFieldContentHtml(): void
    {
        $content = new CContentHTML();

        $export               = new CMbObjectExport();
        $export->empty_values = false;

        $doc = new CMbXMLDocument();
        /** @var DOMElement $node */
        $node = $this->invokePrivateMethod($export, 'createObjectNode', $doc, $content);

        $this->assertFalse($node->hasChildNodes());

        $this->invokePrivateMethod(
            $export,
            'addScalarField',
            $doc,
            $node,
            $content,
            'content',
            'Le lorem ipsum & est, en imprimerie& titre provisoire pour c&alibrer une m"ise en page'
        );

        $this->assertTrue($node->hasChildNodes());
        $this->assertEquals('content', $node->firstChild->getAttribute('name'));
        $this->assertEquals(
            'Le lorem ipsum & est, en imprimerie& titre provisoire pour c&alibrer une m"ise en page',
            $node->firstChild->nodeValue
        );
    }

    public function testAddScalarFieldOk(): void
    {
        $patient = $this->getPatient();

        $file               = new CFile();
        $file->object_class = $patient->_class;
        $file->object_id    = $patient->_id;
        $file->file_name    = 'toto';
        $file->file_date    = CMbDT::dateTime();
        $file->setContent('test');
        $file->updateFormFields();
        $file->fillFields();
        $this->storeOrFailed($file);

        $export               = new CMbObjectExport();
        $export->empty_values = false;

        $doc = new CMbXMLDocument();
        /** @var DOMElement $node */
        $node = $this->invokePrivateMethod($export, 'createObjectNode', $doc, $file);
        $this->assertFalse($node->hasChildNodes());

        $this->invokePrivateMethod($export, 'addScalarField', $doc, $node, $file, 'object_class', 'CPatient');

        $this->assertTrue($node->hasChildNodes());
        $this->assertEquals('object_class', $node->firstChild->getAttribute('name'));
        $this->assertEquals($file->object_class, $node->firstChild->nodeValue);
    }

    public function testAddBackRefNoInTree(): void
    {
        $pat = $this->getPatient();

        $export      = new CMbObjectExport($pat, ['CPatient' => []]);
        $export->doc = new CMbXMLDocument();

        $this->assertFalse($export->doc->hasChildNodes());

        $this->invokePrivateMethod($export, 'addBackRef', $pat, 'consultations', 1);

        $this->assertFalse($export->doc->hasChildNodes());
    }

    public function testAddBackRefOk(): void
    {
        $consultations = [
            $this->createConsult(),
            $this->createConsult(),
            $this->createConsult(),
            $this->createConsult(),
            $this->createConsult(),
        ];

        $consult_guids = CMbArray::pluck($consultations, '_guid');

        $patient = $this->getPatient();

        $object_cache                  = CStoredObject::$useObjectCache;
        CStoredObject::$useObjectCache = true;

        $patient->_count['consultations'] = 5;
        $patient->_back['consultations']  = $consultations;

        $export = new CMbObjectExport($patient, ['CPatient' => ['consultations']]);

        // Must create a doc with a root node before trying to export data
        $export->doc = new CMbXMLDocument();
        $root        = $export->doc->createElement("mediboard-export");
        $export->doc->appendChild($root);

        $this->assertFalse($export->doc->firstChild->hasChildNodes());

        $this->invokePrivateMethod($export, 'addBackRef', $patient, 'consultations', 2);

        $this->assertTrue($export->doc->firstChild->hasChildNodes());
        $this->assertCount(5, $export->doc->firstChild->childNodes);

        /** @var DOMElement $child */
        foreach ($export->doc->firstChild->childNodes as $child) {
            $this->assertTrue(in_array($child->getAttribute('id'), $consult_guids));
        }

        CStoredObject::$useObjectCache = $object_cache;
    }

    public function canExportObjectProvider(): array
    {
        $pat2 = CPatient::getSampleObject(null, true);
        $this->storeOrFailed($pat2);

        $pat  = new CPatient();
        $pats = [
            $this->getPatient(),
            $pat2,
        ];

        $pat_with_id       = array_pop($pats);
        $other_pat_with_id = array_pop($pats);

        $export = new CMbObjectExport($pat);

        $export_filter = new CMbObjectExport($pat);
        $export_filter->setFilterCallback(
            function (CStoredObject $object) use ($pat_with_id) {
                if ($object instanceof CPatient && $object->_id === $pat_with_id->_id) {
                    return false;
                }

                return true;
            }
        );


        return [
            'depth_is_zero'             => [$export, $pat, 0, false],
            'object_with_no_id'         => [$export, $pat, 1, false],
            'filter_callback_is_not_ok' => [$export_filter, $pat, 1, false],
            'exportable_with_no_filter' => [$export, $pat_with_id, 1, true],
            'exportable_with_filter'    => [$export_filter, $other_pat_with_id, 1, true],
        ];
    }

    private function getPatient(): CPatient
    {
        if (!$this->patient) {
            $patient = CPatient::getSampleObject(null, true);
            $this->storeOrFailed($patient);

            $this->patient = $patient;
        }

        return $this->patient;
    }

    private function createConsult(): CConsultation
    {
        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = CMediusers::get()->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->loadMatchingObjectEsc();
        if (!$plage_consult->_id) {
            $plage_consult->freq  = '00:15:00';
            $plage_consult->debut = '08:00:00';
            $plage_consult->fin   = '20:00:00';
            $this->storeOrFailed($plage_consult);
        }

        $patient = CPatient::getSampleObject(null, true);
        $this->storeOrFailed($patient);

        $consult = new CConsultation();
        $consult->plageconsult_id = $plage_consult->_id;
        $consult->patient_id = $patient->_id;
        $consult->heure = '08:00:00';
        $consult->chrono = 16;
        $this->storeOrFailed($consult);

        return $consult;
    }
}
