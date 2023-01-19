<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit\Strategy;

use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CSQLDataSource;
use Ox\Import\Framework\Adapter\MySqlAdapter;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Mapper\MapperBuilderInterface;
use Ox\Import\Framework\Mapper\MapperInterface;
use Ox\Import\Framework\Matcher\DefaultMatcher;
use Ox\Import\Framework\Persister\DefaultPersister;
use Ox\Import\Framework\Repository\GenericRepository;
use Ox\Import\Framework\Strategy\BFSStrategy;
use Ox\Import\Framework\Tests\Unit\GeneratorEntityTrait;
use Ox\Import\Framework\Transformer\DefaultTransformer;
use Ox\Import\Framework\Validator\DefaultValidator;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\OxUnitTestCase;

class BFSStrategyTest extends OxUnitTestCase
{
    use GeneratorEntityTrait;

    /**
     * @var BFSStrategy
     */
    private $strategy;
    /**
     * @var MapperBuilderInterface
     */
    private $mapper;
    /**
     * @var GenericRepository
     */
    private $repository;
    /**
     * @var DefaultValidator
     */
    private $validator;
    /**
     * @var DefaultTransformer
     */
    private $transformer;
    /**
     * @var DefaultMatcher
     */
    private $matcher;
    /**
     * @var DefaultPersister
     */
    private $persister;
    /**
     * @var CImportCampaign
     */
    private $campaign;
    /**
     * @var MySqlAdapter
     */
    private $adapter;
    /**
     * @var CSQLDataSource
     */
    private $ds;

    public function setUp(): void
    {
        $this->ds      = new CPDOMySQLDataSource();
        $this->adapter = new MySqlAdapter($this->ds);
        $this->mapper  = $this->createMock(MapperInterface::class);

        $this->repository = $this->createMock(GenericRepository::class);

        $this->validator = new DefaultValidator();

        $this->transformer = new DefaultTransformer();

        $this->matcher = new DefaultMatcher();

        $this->persister = new DefaultPersister();

        $this->campaign       = new CImportCampaign();
        $this->campaign->name = uniqid();
        $this->campaign->store();


        $this->strategy = new BFSStrategy(
            $this->repository,
            $this->validator,
            $this->transformer,
            $this->matcher,
            $this->persister,
            $this->campaign
        );
    }

    public function testImportOneUser(): void
    {
        $user = $this->generateExternalUser();

        $user_after = $this->invokePrivateMethod($this->strategy, 'importOne', $user);

        $this->assertInstanceOf(CUser::class, $user_after);
        $this->assertNotNull($user_after->_id);
    }

//    public function testImportOnePatient(): void
//    {
//        $this->markTestSkipped();
//        $patient = $this->generateExternalPatient();
//
//        $patient_after = $this->invokePrivateMethod($this->strategy, 'importOne', $patient);
//
//        $this->assertInstanceOf(CPatient::class, $patient_after);
//        $this->assertNotNull($patient_after->_id);
//    }

    /**
     * @throws \Ox\Tests\TestsException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     */
    public function testImportOneMedecin(): void
    {
        /**@var  Medecin $medecin */
        $medecin = $this->generateExternalMedecin();

        $medecin_after = $this->invokePrivateMethod($this->strategy, 'importOne', $medecin);

        $this->assertInstanceOf(CMedecin::class, $medecin_after);
        $this->assertNotNull($medecin_after->_id);
    }


    //    public function testImportOnePlageConsult(): void
    //    {
    ////         TODO : à refaire quand j'aurai compris comment referencer un autre objet
    //                $this->markTestSkipped("voir todo");
    //        /**@var  PlageConsult $plage_consult */
    //        $plage_consult = $this->generateExternalPlageConsult(true);
    //        /** @var User $user */
    //        $user = $this->generateExternalUser();
    //        $external_ref_user = new ExternalReference('utilisateur', $user->getExternalID(), true);
    //
    //        $this->repository->method('findInPoolById')->willReturn($user);
    //        $plage_consult_after = $this->invokePrivateMethod($this->strategy, 'importOne', $plage_consult);
    //
    //        $this->assertInstanceOf(CPlageconsult::class, $plage_consult_after);
    //        $this->assertNotNull($plage_consult_after->_id);
    //    }
    //
    //    public function testImportOneConsultation(): void
    //    {
    //        // TODO : à refaire quand j'aurai compris comment référencer un autre objet
    //                $this->markTestSkipped("voir todo");
    //        /**@var  Consultation $consultation */
    //        $consultation = $this->generateExternalConsultation();
    //
    //        $consultation_after = $this->invokePrivateMethod($this->strategy, 'importOne', $consultation);
    //
    //        $this->assertInstanceOf(CConsultation::class, $consultation_after);
    //        $this->assertNotNull($consultation_after->_id);
    //    }
    //
    //    public function testImportOneSejour(): void
    //    {
    //        // TODO : à refaire quand j'aurai compris comment référencer un autre objet
    //                $this->markTestSkipped("voir todo");
    //        /**@var  Sejour $sejour */
    //        $sejour = $this->generateExternalSejour();
    //
    //        $sejour_after = $this->invokePrivateMethod($this->strategy, 'importOne', $sejour);
    //
    //        $this->assertInstanceOf(CSejour::class, $sejour_after);
    //        $this->assertNotNull($sejour_after->_id);
    //    }
    //
    //    public function testImportOneFile(): void
    //    {
    //        // TODO : à refaire quand j'aurai compris comment référencer un autre objet
    //        $this->markTestSkipped("voir todo l-185");
    //        /**@var  File $file */
    //        $file = $this->generateExternalFileWithRefPatient();
    //        /** @var Patient $patient */
    //        $patient       = $this->generateExternalPatient();
    //        $patient_after = $this->invokePrivateMethod($this->strategy, 'importOne', $patient);
    //
    //        $this->strategy->addExternalReferenceToStash($patient, $patient_after);
    //        //        $file->setCustomRefEntities(
    //        //            function () {
    //        //                return [ExternalReference::getNotMandatoryFor(ExternalReference::PATIENT, $patient->getExternalID())];
    //        //            }
    //        //        );
    //
    //        $file_after = $this->invokePrivateMethod($this->strategy, 'importOne', $file);
    //
    //        $this->assertInstanceOf(CFile::class, $file_after);
    //        $this->assertNotNull($file_after->_id);
    //    }
}
