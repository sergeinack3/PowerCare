<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Lpp\CLPPCode;
use Ox\Mediboard\Lpp\Exceptions\LppDatabaseException;
use Ox\Mediboard\Lpp\Repository\LppCodeRepository;
use Ox\Tests\OxUnitTestCase;

class LppCodeRepositoryTest extends OxUnitTestCase
{
    public function testLoad(): void
    {
        $code_data = [
            'CODE_TIPS'  => '3158724',
            'NOM_COURT'  => 'IMPLANT OSSEUX ANATOMIQUE, CHIRURGIE NON ORTHOPEDIQUE, DEPUY, CONDUIT R',
            'RMO1'       => '',
            'RMO2'       => '',
            'RMO3'       => '',
            'RMO4'       => '',
            'RMO5'       => '',
            'DATE_FIN'   => '2013-07-24',
            'AGE_MAX'    => '0',
            'TYPE_PREST' => 'A',
            'INDICATION' => 'O',
            'ARBO1'      => '3',
            'ARBO2'      => '1',
            'ARBO3'      => '2',
            'ARBO4'      => '4',
            'ARBO5'      => '1',
            'ARBO6'      => '0',
            'ARBO7'      => '0',
            'ARBO8'      => '0',
            'ARBO9'      => '0',
            'ARBO10'     => '0',
            'PLACE'      => '111',
            'PROTHESE'   => '',
            'OLD_CODE'   => '301E04.32',
        ];

        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $ds->method('loadHash')->willReturn($code_data);
        $ds->method('prepare')->willReturn('');

        LppCodeRepository::setDatasource($ds);

        $this->assertEquals(new CLPPCode($code_data), LppCodeRepository::getInstance()->load('3158724'));
    }

    public function testLoadIsNull(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $ds->method('loadHash')->willReturn(null);
        $ds->method('prepare')->willReturn('');

        LppCodeRepository::setDatasource($ds);

        $this->assertNull(LppCodeRepository::getInstance()->load('3158724'));
    }
    public function testGetCodeQuery(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $code = '3158724';

        $expected = "SELECT *
FROM `fiche`
WHERE (`CODE_TIPS` = '{$code}')";

        $this->assertEquals($expected, LppCodeRepository::getInstance()->getCodeQuery($code)->makeSelect());
    }

    public function testLoadFromParent(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $codes_data = [
            [
                'CODE_TIPS'  => '3158368',
                'NOM_COURT'  => 'IMPLANT OSSEUX GEOMETRIQUE, > 15CM3, M.I.L, CERAMIL',
                'RMO1'       => '',
                'RMO2'       => '',
                'RMO3'       => '',
                'RMO4'       => '',
                'RMO5'       => '',
                'DATE_FIN'   => '2005-11-28',
                'AGE_MAX'    => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'O',
                'ARBO1'      => '3',
                'ARBO2'      => '1',
                'ARBO3'      => '2',
                'ARBO4'      => '4',
                'ARBO5'      => '2',
                'ARBO6'      => '3',
                'ARBO7'      => '0',
                'ARBO8'      => '0',
                'ARBO9'      => '0',
                'ARBO10'     => '0',
                'PLACE'      => '3',
                'PROTHESE'   => '',
                'OLD_CODE'   => '301E04.23',
            ],
            [
                'CODE_TIPS'  => '3158724',
                'NOM_COURT'  => 'IMPLANT OSSEUX ANATOMIQUE, CHIRURGIE NON ORTHOPEDIQUE, DEPUY, CONDUIT R',
                'RMO1'       => '',
                'RMO2'       => '',
                'RMO3'       => '',
                'RMO4'       => '',
                'RMO5'       => '',
                'DATE_FIN'   => '2013-07-24',
                'AGE_MAX'    => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'O',
                'ARBO1'      => '3',
                'ARBO2'      => '1',
                'ARBO3'      => '2',
                'ARBO4'      => '4',
                'ARBO5'      => '1',
                'ARBO6'      => '0',
                'ARBO7'      => '0',
                'ARBO8'      => '0',
                'ARBO9'      => '0',
                'ARBO10'     => '0',
                'PLACE'      => '111',
                'PROTHESE'   => '',
                'OLD_CODE'   => '301E04.32',
            ],
        ];

        /* Set the methods return values */
        $ds->method('loadList')->willReturn($codes_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppCodeRepository::setDatasource($ds);

        $expected = [];
        foreach ($codes_data as $code_data) {
            $expected[] = new CLPPCode($code_data);
        }

        $this->assertEquals($expected, LppCodeRepository::getInstance()->loadFromParent('01254545'));
    }

    public function testGetCodesFromParentQuery(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $parent_id = '056AFDCEB';

        $expected = "SELECT *
FROM `fiche`
WHERE (`ARBO1` = '5')
AND (`ARBO2` = '6')
AND (`ARBO3` = '10')
AND (`ARBO4` = '16')
AND (`ARBO5` = '14')
AND (`ARBO6` = '13')
AND (`ARBO7` = '15')
AND (`ARBO8` = '11')";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getCodesFromParentQuery($parent_id)->makeSelect()
        );
    }

    public function testSearch(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $codes_data = [
            [
                'CODE_TIPS'  => '3158368',
                'NOM_COURT'  => 'IMPLANT OSSEUX GEOMETRIQUE, > 15CM3, M.I.L, CERAMIL',
                'RMO1'       => '',
                'RMO2'       => '',
                'RMO3'       => '',
                'RMO4'       => '',
                'RMO5'       => '',
                'DATE_FIN'   => '2005-11-28',
                'AGE_MAX'    => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'O',
                'ARBO1'      => '3',
                'ARBO2'      => '1',
                'ARBO3'      => '2',
                'ARBO4'      => '4',
                'ARBO5'      => '2',
                'ARBO6'      => '3',
                'ARBO7'      => '0',
                'ARBO8'      => '0',
                'ARBO9'      => '0',
                'ARBO10'     => '0',
                'PLACE'      => '3',
                'PROTHESE'   => '',
                'OLD_CODE'   => '301E04.23',
            ],
            [
                'CODE_TIPS'  => '3158724',
                'NOM_COURT'  => 'IMPLANT OSSEUX ANATOMIQUE, CHIRURGIE NON ORTHOPEDIQUE, DEPUY, CONDUIT R',
                'RMO1'       => '',
                'RMO2'       => '',
                'RMO3'       => '',
                'RMO4'       => '',
                'RMO5'       => '',
                'DATE_FIN'   => '2013-07-24',
                'AGE_MAX'    => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'O',
                'ARBO1'      => '3',
                'ARBO2'      => '1',
                'ARBO3'      => '2',
                'ARBO4'      => '4',
                'ARBO5'      => '1',
                'ARBO6'      => '0',
                'ARBO7'      => '0',
                'ARBO8'      => '0',
                'ARBO9'      => '0',
                'ARBO10'     => '0',
                'PLACE'      => '111',
                'PROTHESE'   => '',
                'OLD_CODE'   => '301E04.32',
            ],
        ];

        /* Set the methods return values */
        $ds->method('loadList')->willReturn($codes_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppCodeRepository::setDatasource($ds);

        $expected = [];
        foreach ($codes_data as $code_data) {
            $expected[] = new CLPPCode($code_data);
        }

        $this->assertEquals($expected, LppCodeRepository::getInstance()->search('01254545'));
    }

    public function testGetSearchQueryWithCodeOnly(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $code = '1125';

        $expected = "SELECT *
FROM `fiche`
WHERE (`CODE_TIPS` LIKE '{$code}%')
ORDER BY `ARBO1` ASC, `ARBO2` ASC, `ARBO3` ASC, `ARBO4` ASC, `ARBO5` ASC, `ARBO6` ASC,"
            . " `ARBO7` ASC, `ARBO8` ASC, `ARBO9` ASC, `ARBO10` ASC, `PLACE` ASC";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getSearchQuery($code)->makeSelect()
        );
    }

    public function testGetSearchQueryWithTextOnly(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));

        $expected = "SELECT *
FROM `fiche`
WHERE (`NOM_COURT` LIKE '%ORTHESE%')
ORDER BY `ARBO1` ASC, `ARBO2` ASC, `ARBO3` ASC, `ARBO4` ASC, `ARBO5` ASC, `ARBO6` ASC,"
            . " `ARBO7` ASC, `ARBO8` ASC, `ARBO9` ASC, `ARBO10` ASC, `PLACE` ASC";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getSearchQuery(null, 'orthese')->makeSelect()
        );
    }

    public function testGetSearchQueryWithChapterOnly(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));

        $expected = "SELECT *
FROM `fiche`
WHERE (`ARBO1` = '2')
AND (`ARBO2` = '3')
AND (`ARBO3` = '6')
AND (`ARBO4` = '1')
ORDER BY `ARBO1` ASC, `ARBO2` ASC, `ARBO3` ASC, `ARBO4` ASC, `ARBO5` ASC, `ARBO6` ASC,"
            . " `ARBO7` ASC, `ARBO8` ASC, `ARBO9` ASC, `ARBO10` ASC, `PLACE` ASC
LIMIT 50, 100";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getSearchQuery(null, null, '02361', null, 50, 100)->makeSelect()
        );
    }

    public function testGetSearchQueryWithTextAndCode(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $code = '1125';

        $expected = "SELECT *
FROM `fiche`
WHERE (`CODE_TIPS` LIKE '{$code}%' OR `NOM_COURT` LIKE '%ORTHESE%')
ORDER BY `ARBO1` ASC, `ARBO2` ASC, `ARBO3` ASC, `ARBO4` ASC, `ARBO5` ASC, `ARBO6` ASC,"
            . " `ARBO7` ASC, `ARBO8` ASC, `ARBO9` ASC, `ARBO10` ASC, `PLACE` ASC";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getSearchQuery($code, 'orthese')->makeSelect()
        );
    }

    public function testGetSearchQueryWithDate(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $code = '1125';
        $date = CMbDT::date();

        $expected = "SELECT *
FROM `fiche`
WHERE (`CODE_TIPS` LIKE '{$code}%')
AND (`DATE_FIN` IS NULL OR `DATE_FIN` >= '{$date}')
ORDER BY `ARBO1` ASC, `ARBO2` ASC, `ARBO3` ASC, `ARBO4` ASC, `ARBO5` ASC, `ARBO6` ASC,"
            . " `ARBO7` ASC, `ARBO8` ASC, `ARBO9` ASC, `ARBO10` ASC, `PLACE` ASC";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getSearchQuery($code, null, null, $date)->makeSelect()
        );
    }

    public function testCount(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadResult', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadResult')->willReturn(2);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppCodeRepository::setDatasource($ds);

        $this->assertEquals(2, LppCodeRepository::getInstance()->count('01254545'));
    }

    public function testCountWithDatabaseError(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadResult', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadResult')->willThrowException(new Exception());
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-database_error/');


        LppCodeRepository::setDatasource($ds);
        LppCodeRepository::getInstance()->count('01254545');
    }

    public function testCountWithInvalidResult(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadResult', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadResult')->willReturn(false);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppCodeRepository::setDatasource($ds);

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-invalid_request_result/');

        LppCodeRepository::getInstance()->count('01254545');
    }

    public function testGetCountQuery(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $code = '1125';

        $expected = "SELECT COUNT(*) AS `total`
FROM `fiche`
WHERE (`CODE_TIPS` LIKE '{$code}%')";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getCountQuery($code)->makeSelectCount()
        );
    }

    public function testGetCompatibleCodesQuery(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $code = '1125';

        $expected = "SELECT `fiche`.*
FROM `fiche`
RIGHT JOIN `comp` ON `fiche`.`CODE_TIPS` = `comp`.`CODE2`
WHERE (`comp`.`CODE1` = '{$code}')";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getCompatibleCodesQuery($code)->makeSelect()
        );
    }

    public function testGetIncompatibleCodesQuery(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $code = '1125';

        $expected = "SELECT `fiche`.*
FROM `fiche`
RIGHT JOIN `incomp` ON `fiche`.`CODE_TIPS` = `incomp`.`CODE2`
WHERE (`incomp`.`CODE1` = '{$code}')";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getIncompatibleCodesQuery($code)->makeSelect()
        );
    }

    public function testGetAllowedPrestationCodesForSpeciality(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $prestations_data = [
            [
                'code_prestation' => 'DVO',
            ],
            [
                'code_prestation' => 'PA',
            ],
        ];

        /* Set the methods return values */
        $ds->method('loadList')->willReturn($prestations_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppCodeRepository::setDatasource($ds);

        $expected = ['DVO', 'PA'];

        $this->assertEquals($expected, LppCodeRepository::getInstance()->getAllowedPrestationCodesForSpeciality(27));
    }

    public function testGetAllowedPrestationCodesQuery(): void
    {
        LppCodeRepository::setDatasource(CSQLDataSource::get('std'));
        $speciality = 27;

        $expected = "SELECT code_prestation
FROM `code_prestation_to_specialite`
WHERE (`specialite` = '{$speciality}')";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getAllowedPrestationCodesQuery($speciality)->makeSelect()
        );
    }

    public function testGetExpenseQualifiersForCodeDatabaseError(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadHash')->willThrowException(new Exception());
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-database_error/');


        LppCodeRepository::setDatasources($ds, $ds);
        LppCodeRepository::getInstance()->getExpenseQualifiersForCode('01254545');
    }

    public function testGetExpenseQualifiersForCodeInvalidError(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadHash')->willReturn(false);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        $this->expectException(LppDatabaseException::class);
        $this->expectErrorMessageMatches('/LppDatabaseException-error-invalid_request_result/');

        LppCodeRepository::setDatasources($ds, $ds);
        LppCodeRepository::getInstance()->getExpenseQualifiersForCode('01254545');
    }

    public function testGetExpenseQualifiersForCodeQuery(): void
    {
        LppCodeRepository::setDatasources(CSQLDataSource::get('std'), CSQLDataSource::get('std'));
        $code = '01254545';

        $expected = "SELECT g,
f,
e,
d,
n,
a,
b
FROM `t7`
WHERE (`code` = '{$code}')";

        $this->assertEquals(
            $expected,
            LppCodeRepository::getInstance()->getExpenseQualifiersForCodeQuery($code)->makeSelect()
        );
    }
}
