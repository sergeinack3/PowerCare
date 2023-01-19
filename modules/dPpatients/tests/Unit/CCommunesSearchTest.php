<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Unit;

use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Patients\CCommunesSearch;
use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Tests\OxUnitTestCase;

/**
 * Test for CommunesSearch
 */
class CCommunesSearchTest extends OxUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!CSQLDataSource::get('INSEE', true)) {
            $this->markTestSkipped('Database INSEE is missing');
        }
    }

    /**
     * @config dPpatients INSEE france 1
     * @config dPpatients INSEE suisse 1
     * @config dPpatients INSEE allemagne 0
     * @config dPpatients INSEE espagne 0
     * @config dPpatients INSEE portugal 0
     * @config dPpatients INSEE gb 0
     * @config dPpatients INSEE belgique 0
     */
    public function testAvailableCountry(): void
    {
        $search = new CCommunesSearch();
        $this->assertEquals(
            [
                CPaysInsee::NUMERIC_FRANCE => 'france',
                CPaysInsee::NUMERIC_SUISSE => 'suisse'
            ],
            $search->getAvailableCountries()
        );
    }

    /**
     * @dataProvider searchCommunesProvider
     */
    public function testSearchCommunes(
        string $needle,
        string $column,
        string $country,
        int $expected_count,
        int $numeric
    ): void {
        $search = new CCommunesSearch();
        $this->assertCount(
            $expected_count,
            $this->invokePrivateMethod($search, 'searchCommunes', $needle, $column, $country, $numeric)
        );
    }

    public function testBuildSelectFrance(): void
    {
        $search = new CCommunesSearch();
        $this->assertEquals(
            [
                'commune',
                'code_postal',
                'departement',
                'INSEE',
                'code_insee',
                'numerique',
                "'France' AS pays"
            ],
            $this->invokePrivateMethod($search, 'buildSelect', 'france')
        );
    }

    public function testBuildSelectOther(): void
    {
        $search = new CCommunesSearch();
        $this->assertEquals(
            [
                'commune',
                'code_postal',
                "'' AS departement",
                "'' AS INSEE",
                'code_insee',
                'numerique',
                "'Allemagne' AS pays"
            ],
            $this->invokePrivateMethod($search, 'buildSelect', 'allemagne')
        );
    }

    public function testPrepareNeedleOk(): void
    {
        $search = new CCommunesSearch();
        $this->assertEquals(
            'test%',
            $this->invokePrivateMethod($search, 'prepareNeedle', 'test', CCommunesSearch::COLUMN_CP)
        );
        $this->assertEquals(
            '%test%',
            $this->invokePrivateMethod(
                $search,
                'prepareNeedle',
                'test',
                CCommunesSearch::COLUMN_COMMUNE
            )
        );
    }

    public function testPrepareNeedleNonAllowedColumn(): void
    {
        $search = new CCommunesSearch();
        $this->expectExceptionMessage('CCommunesSearch-Error-Column col must be in array');
        $this->invokePrivateMethod($search, 'prepareNeedle', 'test', 'column');
    }

    public function testSortResult(): void
    {
        $matches = [
            ['code_postal' => '95000', 'commune' => 'Test1', "length" => 5],
            ['code_postal' => '17000', 'commune' => 'La Rochelle', "length" => 11],
            ['code_postal' => '95001', 'commune' => 'Test', "length" => 4],
            ['code_postal' => '95000', 'commune' => 'Test', "length" => 4],
            ['code_postal' => '95000', 'commune' => 'zzzzz', "length" => 5],
            ['code_postal' => '01700', 'commune' => 'First', "length" => 5],
        ];

        $expected = [
            ['code_postal' => '95000', 'commune' => 'Test', "length" => 4],
            ['code_postal' => '95001', 'commune' => 'Test', "length" => 4],
            ['code_postal' => '01700', 'commune' => 'First', "length" => 5],
            ['code_postal' => '95000', 'commune' => 'Test1', "length" => 5],
            ['code_postal' => '95000', 'commune' => 'zzzzz', "length" => 5],
            ['code_postal' => '17000', 'commune' => 'La Rochelle', "length" => 11],
        ];

        $search = new CCommunesSearch();
        $this->assertEquals(
            $expected,
            $this->invokePrivateMethod(
                $search,
                'sortResults',
                $matches,
                CCommunesSearch::COLUMN_COMMUNE
            )
        );
    }

    public function testSanitizeResult(): void
    {
        $matches = [
            ['departement' => 'charente maritime', 'commune' => 'LA ROCHELLE', 'pays' => 'france'],
            ['departement' => 'TEST DE DEpartement', 'commune' => 'UNE COMmuNe', 'pays' => 'suiSSE'],
            ['departement' => '', 'commune' => '', 'pays' => ''],
        ];

        $expected = [
            ['departement' => 'Charente Maritime', 'commune' => 'La Rochelle', 'pays' => 'France'],
            ['departement' => 'Test De Departement', 'commune' => 'Une Commune', 'pays' => 'Suisse'],
            ['departement' => '', 'commune' => '', 'pays' => ''],
        ];

        $search = new CCommunesSearch();
        $this->assertEquals($expected, $this->invokePrivateMethod($search, 'sanitizeResults', $matches));
    }

    /**
     * @config dPpatients INSEE france 1
     * @config dPpatients INSEE suisse 0
     * @config dPpatients INSEE allemagne 0
     * @config dPpatients INSEE espagne 0
     * @config dPpatients INSEE portugal 0
     * @config dPpatients INSEE gb 0
     * @config dPpatients INSEE belgique 0
     */
    public function testMatchOk(): void
    {
        $search = new CCommunesSearch();
        $this->assertCount(5, $search->match('Paris', CCommunesSearch::COLUMN_COMMUNE, 5));
    }

    public function testMatchEmpty(): void
    {
        $search = new CCommunesSearch();
        $this->assertEquals([], $search->match('AAAAAAAA', CCommunesSearch::COLUMN_COMMUNE, 10));
    }

    public function testMatchEquals(): void
    {
        $search         = new CCommunesSearch();
        $firstResearch  = $search->match('la motte', CCommunesSearch::COLUMN_COMMUNE, 5);
        $secondResearch = $search->match("La-motte", CCommunesSearch::COLUMN_COMMUNE, 5);

        $this->assertEquals($firstResearch, $secondResearch);
    }

    public function testMatchContainsSearch(): void
    {
        $search = new CCommunesSearch();
        $result = $search->match('Saint-Denis', CCommunesSearch::COLUMN_COMMUNE, 10);

        $this->assertContains("97400", array_column($result, "code_postal"));
    }

    public function testGetLimitedResult(): void
    {
        $matches = [
            'france'    => [['resultat1'], ['resultat2']],
            'suisse'    => [['resultat1'], ['resultat2'], ['resultat3']],
            'allemagne' => [],
        ];

        $expected = [['resultat1'], ['resultat2'], ['resultat1'], ['resultat2']];

        $search = new CCommunesSearch();
        $this->invokePrivateMethod($search, 'setLimit', 4);
        $matches = $this->invokePrivateMethod($search, 'getLimitedResult', $matches);
        $this->assertEquals($expected, $matches);
    }

    public function searchCommunesProvider(): array
    {
        return [
            'empty' => ['00000', 'code_postal', 'france', 0, CPaysInsee::NUMERIC_FRANCE],
            'result' => ['La Rochelle', 'commune', 'france', 2, CPaysInsee::NUMERIC_FRANCE]
        ];
    }
}
