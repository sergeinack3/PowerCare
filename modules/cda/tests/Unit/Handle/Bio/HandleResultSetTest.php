<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit\Handle\Bio;

use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Cda\CCDAReport;
use Ox\Interop\Cda\Handle\CCDAMeta;
use Ox\Interop\Cda\Handle\Level3\ANS\CCDAHandleCRBio;
use Ox\Interop\Cda\Handle\Level3\ANS\CRepositoryCRBio;
use Ox\Interop\Cda\Tests\Unit\Handle\UnitTestHandle;
use Ox\Interop\Cda\Tests\Unit\UnitTestCDA;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Populate\Generators\CPatientGenerator;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CSenderFileSystem;

/**
 * Class HandleResultSetTest
 * @package Ox\Interop\Cda\Tests\Unit\Handle\Bio
 */
class HandleResultSetTest extends UnitTestHandle
{
    /** @var CInteropSender */
    public static $sender;

    /** @var string */
    public static $identifier;

    /** @var CPatient */
    public static $patient;

    /** @var CObservationResultSet */
    public static $result_set;

    /**
     * @return CObservationResultSet
     * @throws \Ox\Tests\TestsException
     */
    public static function getData(): array
    {
        $identifier = CMbSecurity::generateUUID();

        $sender           = new CSenderFileSystem();
        $sender->nom      = CMbSecurity::generateUUID();
        $sender->group_id = CGroups::loadCurrent()->_id;
        if ($msg = $sender->store()) {
            self::markTestSkipped($msg);
        }

        $patient = (new CPatientGenerator())->setForce(true)->generate();

        return [$identifier, $patient, $sender];
    }

    /**
     * @throws \Ox\Tests\TestsException
     */
    public static function initData(): void
    {
        [$identifier, $patient, $sender] = self::getData();

        self::$identifier = $identifier;
        self::$patient    = $patient;
        self::$sender     = $sender;
    }

    /**
     * @return CObservationResultSet
     * @throws \Ox\Tests\TestsException
     */
    public function getIdentifier(): string
    {
        if (!self::$identifier) {
            self::initData();
        }

        return self::$identifier;
    }

    /**
     * @return CObservationResultSet
     * @throws \Ox\Tests\TestsException
     */
    public function getSender(): CInteropSender
    {
        if (!self::$sender) {
            self::initData();
        }

        return self::$sender;
    }

    /**
     * @return CObservationResultSet
     * @throws \Ox\Tests\TestsException
     */
    public function getResultSet(): CObservationResultSet
    {
        if (!self::$result_set) {
            self::initData();
        }

        return self::$result_set;
    }

    /**
     * @return CPatient
     * @throws \Ox\Tests\TestsException
     */
    public static function getPatient(): CPatient
    {
        if (!self::$patient) {
            self::initData();
        }

        return self::$patient;
    }

    /**
     * @param UnitTestCDA         $context
     * @param string|null         $identifier
     * @param CInteropSender|null $sender
     * @param CPatient|null       $patient
     *
     * @return array<CRepositoryCRBio,CCDAHandleCRBio,CCDADomDocument>
     * @throws \Ox\Tests\TestsException
     */
    public static function getRepository(
        UnitTestCDA $context,
        string $identifier = null,
        CInteropSender $sender = null,
        CPatient $patient = null
    ): array {
        if (!$identifier || !$sender || !$patient) {
            [$identifier, $sender, $patient] = self::getData();
        }

        $cda_meta = new CCDAMeta();
        $cda_meta->patient_id = $patient->_id;
        $cda_meta->id = $identifier;

        $document = $context->getMockBuilder(CCDADomDocument::class)
            ->onlyMethods(['getSetId', 'getEffectiveTime'])
            ->getMock();
        $document->setInteropSender($sender);
        $document->method('getSetId')->willReturn($identifier);
        $document->method('getEffectiveTime')->willReturn(CMbDT::dateTime());

        $handle = $context->getMockBuilder(CCDAHandleCRBio::class)
            ->onlyMethods(['getMeta'])
            ->getMock();
        $handle->method('getMeta')->willReturn($cda_meta);
        $handle->setReport(new CCDAReport('test report'));
        $handle->setPatient($patient);
        $repository = new CRepositoryCRBio($handle, $document);

        return [$repository, $handle, $document];
    }

    /**
     * @return CRepositoryCRBio
     * @throws \Ox\Tests\TestsException
     */
    public function repository(): CRepositoryCRBio
    {
        [$repo] = self::getRepository(
            $this,
            $this->getIdentifier(),
            $this->getSender(),
            self::getPatient()
        );

        return $repo;
    }

    /**
     * @throws \Exception
     */
    public function testCreateResultSet(): void
    {
        $repository       = $this->repository();
        self::$result_set = $result_set = $repository->findOrCreateResultSet();

        $this->assertNotNull($result_set->_id, "");
    }

    /**
     * @depends      testCreateResultSet
     *
     * @throws \Exception
     */
    public function testFindResultSet(): void
    {
        $repository = $this->repository();
        $result_set = $repository->findOrCreateResultSet();

        $this->assertEquals($result_set->_id, self::$result_set->_id);
    }

    /**
     * @depends testCreateResultSet
     */
    public function testIdexExisted(): void
    {
        $result_set = new CObservationResultSet();
        $tag        = $result_set->getLaboTag(self::getSender());
        $idex       = CIdSante400::getMatch($result_set->_class, $tag, self::getIdentifier());

        $this->assertNotNull($idex->_id);
    }
}
