<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Board\ExamCompFinder;
use Ox\Mediboard\Board\Tests\Fixtures\TdbFixtures;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class ExamCompFinderTest extends OxUnitTestCase
{
    private CMediusers $user;

    public function examCompProvider(): array
    {
        return [
            "date exception" => ["Date is mandatory", new CMediusers(), null, null],
            "user exception" => ["User not found", new CMediusers(), CMbDT::date(), null],
        ];
    }

    /**
     * @throws TestsException
     */
    public function loadExamCompProvider(): array
    {
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        return [
            "with date sejour"    => [$user, CMbDT::date(), null],
            "with date operation" => [$user, null, CMbDT::date()],
        ];
    }

    /**
     * @param string     $expected_message
     * @param CMediusers $user
     * @param string     $date_min
     * @param string     $date_inter
     *
     * @return void
     * @throws CMbException
     * @dataProvider examCompProvider
     *
     */
    public function testConstructThrowsException(
        string $expected_message,
        CMediusers $user,
        ?string $date_min,
        ?string $date_inter
    ): void {
        $this->expectExceptionMessage($expected_message);

        new ExamCompFinder($user, $date_min, $date_inter);
    }

    /**
     * @throws Exception
     * @dataProvider loadExamCompProvider
     */
    public function testLoadExamsCompContainsConsultationExams(
        CMediusers $user,
        ?string $date_min,
        ?string $date_inter
    ): void {
        /** @var CConsultation $consult */
        $consult = $this->getObjectFromFixturesReference(CConsultation::class, TdbFixtures::REF_TDB_CONSULT);

        $exam_comp_finder = new ExamCompFinder($user, $date_min, $date_inter);
        $exam_comp_finder->loadExamensComplementaires();
        $actual = $exam_comp_finder->getExamensComplementaires();

        foreach (array_keys($consult->loadRefsExamsComp()) as $exam_id) {
            $this->assertArrayHasKey($exam_id, $actual);
        }
    }
}
