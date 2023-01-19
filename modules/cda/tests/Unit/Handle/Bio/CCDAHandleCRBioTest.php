<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit\Handle\Bio;

use Ox\Core\CMbArray;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Cda\CCDAReport;
use Ox\Interop\Cda\Handle\Level3\ANS\CCDAHandleCRBio;
use Ox\Interop\Cda\Handle\Level3\ANS\CRepositoryCRBio;
use Ox\Interop\Cda\Tests\Unit\Handle\UnitTestHandle;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultBattery;
use Ox\Mediboard\ObservationResult\CObservationResultComment;
use Ox\Mediboard\ObservationResult\CObservationResultExamen;
use Ox\Mediboard\ObservationResult\CObservationResultIsolat;
use Ox\Mediboard\ObservationResult\CObservationResultPrelevement;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationResultSetComment;
use Ox\Mediboard\Patients\CPatient;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CCDAHandleCRBioTest extends UnitTestHandle
{
    /** @var string */
    private const CR_ELECTROPHORESE = 'electrophorese.xml';
    /** @var string */
    private const CR_BIO_AUTO_PRES = 'bio_auto_presentable.xml';

    /** @var string[] */
    private const DOCUMENT_NAMES = [
        self::CR_ELECTROPHORESE,
        self::CR_BIO_AUTO_PRES,
    ];

    /** @var string */
    private static $directory_path;

    /** @var array */
    private static $documents = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::getDocuments();
    }

    /**
     * @return string
     */
    private static function getDirectoryPath(): string
    {
        if (self::$directory_path) {
            return self::$directory_path;
        }

        $path = dirname(__DIR__, 3);
        $path = rtrim($path, '/') . '/Resources/Handle/Bio';

        return self::$directory_path = $path;
    }

    /**
     * @return array
     */
    private static function getDocuments(): array
    {
        if (self::$documents) {
            return self::$documents;
        }

        $documents = [];
        foreach (self::DOCUMENT_NAMES as $name) {
            $name             = rtrim($name, '.xml');
            $file_name        = self::getDirectoryPath() . '/' . "$name.xml";
            $documents[$name] = self::loadDocumentCDA($file_name, true);
        }

        return self::$documents = $documents;
    }

    /**
     * @param CCDADomDocument $document
     * @param                 $add_method
     *
     * @return CCDAHandleCRBio|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockHandler($add_method)
    {
        $methods = ['handleMetadata', 'handlePatient'];
        if ($add_method && !is_array($add_method)) {
            $add_method = [$add_method];
        }

        $methods = array_merge($methods, $add_method);

        // mock repository
        $repo = $this->getMockRepository();

        // mock handler, only handleComponent should preserved
        $handle = $this->getMockBuilder(CCDAHandleCRBio::class)
            ->setConstructorArgs([$repo])
            ->onlyMethods($methods)
            ->getMock();

        $patient = new CPatient();
        $patient->_id = 1;
        $handle->setPatient($patient);

        // create report
        $report = new CCDAReport('cda bio');

        $handle->setReport($report);

        return $handle;
    }

    /**
     * @return CRepositoryCRBio|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockRepository()
    {
        $repo = $this->createMock(CRepositoryCRBio::class);
        $repo->method('findOrCreateResultSet')->willReturn(new CObservationResultSet());
        $repo->method('handleExamen')->willReturn(new CObservationResultExamen());
        $repo->method('handleIsolat')->willReturn(new CObservationResultIsolat());
        $repo->method('handleBattery')->willReturn(new CObservationResultBattery());
        $repo->method('handleResult')->willReturn(new CObservationResult());
        $repo->method('handlePrelevement')->willReturn(new CObservationResultPrelevement());
        $repo->method('handleComment')->willReturn(new CObservationResultComment());
        $repo->method('handleResultSetComment')->willReturn(new CObservationResultSetComment());
        $repo->method('handleImage')->willReturn(new CFile());

        return $repo;
    }

    /**
     * @param string $name
     *
     * @return CCDADomDocument
     */
    public function getDocument(string $name): CCDADomDocument
    {
        $name = rtrim($name, '.xml');

        if (!$document = CMbArray::get(self::getDocuments(), $name)) {
            throw new FileNotFoundException("File ('$name') not found");
        }

        return $document;
    }

    public function providerCountChapter(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 3],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 1],
        ];
    }

    /**
     * @dataProvider providerCountChapter
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountChapter(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleChapter';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountSubChapter(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 2],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 4],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountSubChapter
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountSubChapter(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleSubChapter';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountExamen(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 4],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 4],
        ];
    }


    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountExamen
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountExamen(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleResultsExam';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountIsolats(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 2],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountIsolats
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountIsolats(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleIsolat';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountBatteries(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 6],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 1],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountBatteries
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountBatteries(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleBattery';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountPrelevement(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 2],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountPrelevement
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountPrelevement(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handlePrelevement';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountImage(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 1],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountImage
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountImage(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleImage';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountComment(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 3],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountComment
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountComment(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleComment';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountSetComment(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 2],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountSetComment
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountSetComment(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleResultSetComment';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountResult(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 44],
            self::CR_BIO_AUTO_PRES => [self::CR_BIO_AUTO_PRES, 11],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountResult
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountResult(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleResult';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountParticipantValidator(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 2],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountParticipantValidator
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountParticipantValidator(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleParticipantValidator';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountParticipantResponsible(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountParticipantResponsible
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountParticipantResponsible(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleParticipantResponsible';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountParticipantAutomate(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountParticipantAutomate
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountParticipantAutomate(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleParticipantAutomate';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountParticipantAuthor(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountParticipantAuthor
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountParticipantAuthor(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleParticipantAuthor';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }

    /**
     * @return array[]
     */
    public function providerCountLaboExecutant(): array
    {
        return [
            self::CR_ELECTROPHORESE => [self::CR_ELECTROPHORESE, 0],
        ];
    }

    /**
     * @param string $name
     * @param int    $expected_count
     *
     * @dataProvider providerCountLaboExecutant
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    public function testCountLaboExecutant(string $name, int $expected_count): void
    {
        $document = $this->getDocument($name);
        $method   = 'handleLaboExecutant';

        $handler = $this->getMockHandler($method);

        $handler->expects($this->exactly($expected_count))->method($method);

        $handler->handle($document);
    }
}
