<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Forms\Converter\ExObjectPDFConverter;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class ExObjectPDFConverterTest extends OxUnitTestCase
{
    /** @var string */
    private const HTML_SOURCE = 'ex_object.html';

    /** @var int */
    private static $ex_class_id;

    /**
     * @throws CMbModelNotFoundException
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::removeExTables(self::$ex_class_id);
    }

    /**
     * @param int|null $ex_class_id
     *
     * @throws CMbModelNotFoundException
     */
    private static function removeExTables(?int $ex_class_id = null): void
    {
        if (!$ex_class_id) {
            return;
        }

        $ex_class = CExClass::findOrFail($ex_class_id);

        $ds         = $ex_class->getDS();
        $table_name = $ex_class->getTableName();

        $ds->exec("DROP TABLE `{$table_name}`");
    }

    /**
     * @return CExObject
     * @throws TestsException
     */
    private function getExObject(): CExObject
    {
        static $ex_object = null;

        if ($ex_object instanceof CExObject) {
            return $ex_object;
        }

        $object = $this->getObject();

        $ex_class = $this->getExClass();

        $ex_object               = new CExObject($ex_class->_id);
        $ex_object->object_class = $object->_class;
        $ex_object->object_id    = $object->_id;
        $ex_object->group_id     = CGroups::loadCurrent()->_id;

        if ($msg = $ex_object->store()) {
            $this->fail($msg);
        }

        return $ex_object;
    }

    /**
     * @return CExClass
     * @throws Exception
     */
    private function getExClass(): CExClass
    {
        static $ex_class = null;

        if ($ex_class instanceof CExClass) {
            return $ex_class;
        }

        $ex_class       = new CExClass();
        $ex_class->name = 'ex_class_test_pdf';

        if ($msg = $ex_class->store()) {
            $this->fail($msg);
        }

        self::$ex_class_id = $ex_class->_id;

        return $ex_class;
    }

    /**
     * @return CMbObject
     * @throws TestsException
     */
    private function getObject(): CMbObject
    {
        static $object = null;

        if ($object instanceof CMbObject) {
            return $object;
        }

        $patient = CPatient::getSampleObject();
        $this->storeOrFailed($patient);

        $sejour                = CSejour::getSampleObject();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = CMediusers::get()->_id;
        $sejour->group_id      = CGroups::loadCurrent()->_id;
        $this->storeOrFailed($sejour);

        return $object = $sejour;
    }

    /**
     * @return ExObjectPDFConverter
     * @throws CMbException
     * @throws TestsException
     */
    private function getConverter(): ExObjectPDFConverter
    {
        static $converter = null;

        if ($converter instanceof ExObjectPDFConverter) {
            return $converter;
        }

        return $converter = new ExObjectPDFConverter($this->getExObject(), $this->getObject());
    }

    public function test_converter_checks_ex_object_validity(): void
    {
        $ex_object = new CExObject($this->getExClass()->_id);

        $this->expectExceptionMessage('ExObjectPDFConverter-error-Valid CExObject and CMbObject must be provided');
        new ExObjectPDFConverter($ex_object, $this->getObject());
    }

    public function test_converter_checks_context_object_validity(): void
    {
        $this->expectExceptionMessage('ExObjectPDFConverter-error-Valid CExObject and CMbObject must be provided');
        new ExObjectPDFConverter($this->getExObject(), new CSejour());
    }

    public function test_converter_refuses_windows_platform(): void
    {
        // Get mock with isWindows method, without constructor call
        $mock = $this->getMockBuilder(ExObjectPDFConverter::class)
            ->setMethods(['isWindows'])
            ->disableOriginalConstructor()
            ->getMock();

        // Tell mock that isWindows method will *always* return true
        $mock->expects($this->any())
            ->method('isWindows')
            ->willReturn(true);

        $this->expectExceptionMessage('ExObjectPDFConverter-error-Windows platform not supported');

        // Explicit call to constructor
        $mock->__construct($this->getExObject(), $this->getObject());
    }

    public function test_binary_converter_exists(): void
    {
        $converter = $this->getConverter();

        $command = $this->invokePrivateMethod($converter, 'getBinaryCommand');

        $this->assertNotNull($command);
        $this->assertFileExists($command);
    }

    /**
     * @depends test_binary_converter_exists
     * @throws TestsException
     */
    public function test_it_converts(): void
    {
        $html_source =
            dirname(__DIR__, 4) . DIRECTORY_SEPARATOR
            . 'core' . DIRECTORY_SEPARATOR
            . 'tests' . DIRECTORY_SEPARATOR
            . 'Resources' . DIRECTORY_SEPARATOR
            . self::HTML_SOURCE;

        $this->assertFileExists($html_source);

        $html_content = file_get_contents($html_source);
        $this->assertNotEmpty($html_content);

        $mock = $this->getMockBuilder(ExObjectPDFConverter::class)
            ->setConstructorArgs([$this->getExObject(), $this->getObject()])
            ->setMethods(['getHTMLSource'])
            ->getMock();

        $mock->method('getHTMLSource')->willReturn($html_content);

        $pdf_content = $mock->convert();
        $this->assertStringStartsWith('%PDF', $pdf_content);
    }
}
