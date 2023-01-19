<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tests\Unit\Repository;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Interop\Eai\Repository\Exceptions\SejourRepositoryException;
use Ox\Interop\Eai\Repository\SejourRepository;
use Ox\Interop\Eai\Tests\Fixtures\Repository\SejourRepositoryFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class SejourRepositoryTest extends OxUnitTestCase
{
    /** @var Cache */
    public static $cache;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // force tag nda
        $group_id = CGroups::loadCurrent()->_id;
        self::$cache = new Cache('CSejour.getTagNDA', [$group_id, 'tag_dossier'], Cache::INNER);
        self::$cache->put(SejourRepositoryFixtures::TAG_NDA);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // remove forcing tag nda
        self::$cache->rem();
    }

    /**
     * @return void
     * @throws TestsException
     */
    public function testSejourNDA(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);
        $NDA    = $sejour->loadNDA();

        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_ONLY_NDA))
            ->setNDA($NDA, $sejour->getTagNDA())
            ->find();

        $this->assertNotNull($sejour_found);
        $this->assertEquals($sejour->_id, $sejour_found->_id);
    }

    public function providerSejourWithBadNDA(): array
    {
        return [
            'With empty NDA' => [null],
            'With NDA which non-exist' => [CMbSecurity::generateUUID() . uniqid()],
        ];
    }

    /**
     * @param string|null $NDA
     *
     * @dataProvider providerSejourWithBadNDA
     * @return void
     * @throws Exception
     */
    public function testSejourWithBadNDA(?string $NDA): void
    {
        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_ONLY_NDA))
            ->setNDA($NDA, CSejour::getTagNDA())
            ->find();

        $this->assertNull($sejour_found);
    }

    public function testSejourWithDate(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);

        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_ONLY_DATE))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour(SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE)
            ->setGroupId(CGroups::loadCurrent()->_id)
            ->setPraticienId($sejour->praticien_id)
            ->find();

        $this->assertNotNull($sejour_found);
        $this->assertEquals($sejour->_id, $sejour_found->_id);
    }

    public function testSejourWithDateExtended(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);
        $date   = CMbDT::dateTime("- 1 DAY", SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE);

        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_ONLY_DATE_EXTENDED))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour($date)
            ->setGroupId(CGroups::loadCurrent()->_id)
            ->setPraticienId($sejour->praticien_id)
            ->find();

        $this->assertNotNull($sejour_found);
        $this->assertEquals($sejour->_id, $sejour_found->_id);
    }


    public function testSejourWithBestStrategy(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);
        $date   = CMbDT::dateTime("- 1 DAY", SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE);

        $sejour_found = (new SejourRepository())
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour($date)
            ->setGroupId(CGroups::loadCurrent()->_id)
            ->setPraticienId($sejour->praticien_id)
            ->find();

        $this->assertNotNull($sejour_found);
        $this->assertEquals($sejour->_id, $sejour_found->_id);
    }

    public function testSejourWithDateOutOfBound(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);
        $date   = CMbDT::dateTime("- 1 DAY", SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE);

        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_ONLY_DATE))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour($date)
            ->setGroupId(CGroups::loadCurrent()->_id)
            ->setPraticienId($sejour->praticien_id)
            ->find();

        $this->assertNull($sejour_found);
    }

    public function testSejourWithDateFailedAndFallbackDateExtended(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);
        $date   = CMbDT::dateTime("- 1 DAY", SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE);

        $sejour_found = (new SejourRepository(
            SejourRepository::STRATEGY_ONLY_DATE,
            SejourRepository::STRATEGY_ONLY_DATE_EXTENDED
        ))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour($date)
            ->setGroupId(CGroups::loadCurrent()->_id)
            ->setPraticienId($sejour->praticien_id)
            ->find();

        $this->assertNotNull($sejour_found);
        $this->assertEquals($sejour->_id, $sejour_found->_id);
    }

    public function testSejourWithrObjectID(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);

        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_RESOURCE_ID))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setObjectId($sejour->_id)
            ->find();

        $this->assertNotNull($sejour_found);
        $this->assertEquals($sejour->_id, $sejour_found->_id);
    }

    public function testCurrentSejourPatient(): void
    {
        /** @var CPatient $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);

        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_CURRENT_SEJOUR))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour(SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE)
            ->setGroupId(CGroups::loadCurrent()->_id)
            ->find();

        $this->assertNotNull($sejour_found);
        $this->assertEquals($sejour->_id, $sejour_found->_id);
    }

    public function testCurrentSejourPatientWithWrongPraticien(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);

        $sejour_found = (new SejourRepository(SejourRepository::STRATEGY_CURRENT_SEJOUR))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour(SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE)
            ->setGroupId(CGroups::loadCurrent()->_id)
            ->setPraticienId(9999999) // praticien which doesn't exist
            ->find();


        $this->assertNull($sejour_found);
    }

    public function testSejourWithDateWithMultipleSejour(): void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_MULTIPLE_SEJOUR);

        $this->expectException(SejourRepositoryException::class);
        $this->expectExceptionCode(SejourRepositoryException::MULTIPLE_SEJOUR_FOUND);

        (new SejourRepository(SejourRepository::STRATEGY_ONLY_DATE_EXTENDED))
            ->setParameter(SejourRepository::PARAMETER_DATE_BEFORE, CMbDT::dateTime('-2 DAYS', $sejour->entree))
            ->setParameter(SejourRepository::PARAMETER_DATE_AFTER, CMbDT::dateTime('+1 DAYS', $sejour->entree))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setDateSejour(SejourRepositoryFixtures::PRIMARY_SEJOUR_DATE_ENTREE)
            ->find();
    }

    public function testDivergenceBetweenPatientFoundedAndPatientSejour():void
    {
        /** @var CSejour $sejour */
        $sejour = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_MULTIPLE_SEJOUR);
        /** @var CSejour $sejour_other */
        $sejour_other = $this->getObjectFromFixturesReference(CSejour::class, SejourRepositoryFixtures::REF_SEJOUR);

        $this->expectException(SejourRepositoryException::class);
        $this->expectExceptionCode(SejourRepositoryException::PATIENT_DIVERGENCE_FOUND);

        (new SejourRepository(SejourRepository::STRATEGY_ONLY_NDA))
            ->setPatient(CPatient::find($sejour->patient_id))
            ->setNDA($sejour_other->loadNDA(), $sejour_other::getTagNDA())
            ->find();
    }
}
