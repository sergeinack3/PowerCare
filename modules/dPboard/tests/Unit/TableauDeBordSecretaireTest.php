<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Board\TableauDeBordSecretaire;
use Ox\Mediboard\Board\Tests\Fixtures\TdbFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class TableauDeBordSecretaireTest extends OxUnitTestCase
{
    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testGetPraticiensReturn(): void
    {
        $chir = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        $tdb = new TableauDeBordSecretaire();

        $tdb->loadPraticiensTdb([$chir->_id]);

        $actual = $tdb->getPraticiens();

        $this->assertArrayHasKey($chir->_id, $actual);
    }

    /**
     * @param array      $expected
     * @param CMediusers $user
     *
     * @return void
     * @throws Exception
     * @dataProvider totalDocumentsProvider
     * @config dPcabinet CConsultation fix_doc_edit 1
     */
    public function testGetTotalDocumentsReturnExpected(array $expected, CMediusers $user): void
    {
        $tdb = new TableauDeBordSecretaire();

        $tdb->loadChirsDocumentsFromDate([$user->_id], CMbDT::date("-1 WEEK"));

        $actual = $tdb->getTotalDocumentsByStatus();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws TestsException
     * @throws Exception
     * @config dPcabinet CConsultation fix_doc_edit 1
     */
    public function testGetDocumentByStatusNotEmpty(): void
    {
        $chir = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        $tdb = new TableauDeBordSecretaire();

        $tdb->loadChirsDocumentsFromDate([$chir->_id], CMbDT::date("-1 WEEK"));

        $actual = $tdb->getDocumentsByStatus();

        foreach ($actual as $status) {
            $this->assertNotEmpty($status);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetTotalDocumentsWithouChirIdReturnExpected(): void
    {
        $tdb = new TableauDeBordSecretaire();

        $expected = [
            "attente_validation_praticien" => 0,
            "a_corriger"                   => 0,
            "envoye"                       => 0,
            "a_envoyer"                    => 0,
        ];

        $tdb->loadChirsDocumentsFromDate([], CMbDT::date("-1 HOUR"));

        $actual = $tdb->getTotalDocumentsByStatus();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws TestsException
     */
    public function totalDocumentsProvider(): array
    {
        $chir     = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);
        $expected = [
            "attente_validation_praticien" => 1,
            "a_corriger"                   => 2,
            "envoye"                       => 1,
            "a_envoyer"                    => 1,
        ];

        return [
            [$expected, $chir],
        ];
    }
}
