<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Tests\Unit;

use Ox\Core\CPDOMySQLDataSource;
use Ox\Mediboard\Lpp\CLPPChapter;
use Ox\Mediboard\Lpp\CLPPCode;
use Ox\Mediboard\Lpp\Repository\LppChapterRepository;
use Ox\Mediboard\Lpp\Repository\LppCodeRepository;
use Ox\Tests\OxUnitTestCase;

class CLPPChapterTest extends OxUnitTestCase
{
    public function testLoadAncestors(): void
    {
        $chapter_data = [
            'ID' => '0245',
            'PARENT' => '024',
            'INDEX' => '5',
            'LIBELLE' => 'Protheses respiratoires pour tracheostomie',
        ];

        $ancestor_1_data = [
            'ID' => '024',
            'PARENT' => '02',
            'INDEX' => '4',
            'LIBELLE' => 'Protheses externes non orthopediques',
        ];

        $ancestor_2_data = [
            'ID' => '02',
            'PARENT' => '0',
            'INDEX' => '2',
            'LIBELLE' => 'Ortheses et protheses externes',
        ];

        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadHash')->will(
            $this->onConsecutiveCalls(
                $ancestor_1_data,
                $ancestor_2_data
            )
        );
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        LppChapterRepository::setDatasource($ds);

        $parent = new CLPPChapter($ancestor_1_data);
        $parent->_parent = new CLPPChapter($ancestor_2_data);

        $expected = new CLPPChapter($chapter_data);
        $expected->_parent = $parent;

        $actual = new CLPPChapter($chapter_data);
        $actual->loadAncestors();

        $this->assertEquals($expected, $actual);
    }

    public function testLoadDirectDescendants(): void
    {
        $chapters_data = [
            0 => [
                'ID' => '01',
                'PARENT' => '0',
                'INDEX' => '1',
                'LIBELLE' => 'DM pour traitements, aides a la vie, aliments et pansements',
            ],
            1 => [
                'ID' => '02',
                'PARENT' => '0',
                'INDEX' => '2',
                'LIBELLE' => 'Ortheses et protheses externes',
            ],
            2 => [
                'ID' => '03',
                'PARENT' => '0',
                'INDEX' => '3',
                'LIBELLE' => 'DMI, implants et greffons tissulaires d\'origine humaine',
            ],
            3 => [
                'ID' => '04',
                'PARENT' => '0',
                'INDEX' => '4',
                'LIBELLE' => 'Vehicules pour handicapes physiques',
            ],
        ];

        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadList')->willReturn($chapters_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        LppChapterRepository::setDatasource($ds);

        $expected = [];
        foreach ($chapters_data as $chapter_data) {
            $expected[] = new CLPPChapter($chapter_data);
        }

        $this->assertEquals($expected, (new CLPPChapter(['ID' => '0']))->loadDirectDescendants());
    }

    public function testLoadCodes(): void
    {
        $codes_data = [
            [
                'CODE_TIPS' => '1227770',
                'NOM_COURT' => 'ESCARRES (SUR)MATELAS PNEUMATIQUE, S/CLASSE II, ROHO, MATTRESS',
                'RMO1' => '',
                'RMO2' => '',
                'RMO3' => '',
                'RMO4' => '',
                'RMO5' => '',
                'DATE_FIN' => '2011-10-10',
                'AGE_MAX' => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'O',
                'ARBO1' => '1',
                'ARBO2' => '2',
                'ARBO3' => '1',
                'ARBO4' => '2',
                'ARBO5' => '3',
                'ARBO6' => '2',
                'ARBO7' => '1',
                'ARBO8' => '0',
                'ARBO9' => '0',
                'ARBO10' => '0',
                'PLACE' => '1',
                'PROTHESE' => '',
                'OLD_CODE' => '101A04.31',
            ],
            [
                'CODE_TIPS' => '1296103',
                'NOM_COURT' => 'ESCARRES, MATELAS AIR MOTORISE, MEDIDEV, SENTRY 1200, FORFAIT LOCATION JOUR',
                'RMO1' => '',
                'RMO2' => '',
                'RMO3' => '',
                'RMO4' => '',
                'RMO5' => '',
                'DATE_FIN' => '2020-01-31',
                'AGE_MAX' => '0',
                'TYPE_PREST' => 'L',
                'INDICATION' => 'O',
                'ARBO1' => '1',
                'ARBO2' => '2',
                'ARBO3' => '1',
                'ARBO4' => '2',
                'ARBO5' => '3',
                'ARBO6' => '2',
                'ARBO7' => '1',
                'ARBO8' => '0',
                'ARBO9' => '0',
                'ARBO10' => '0',
                'PLACE' => '2',
                'PROTHESE' => '',
                'OLD_CODE' => '',
            ],
            [
                'CODE_TIPS' => '1289652',
                'NOM_COURT' => 'ESCARRES, MATELAS AIR MOTORISE, MEDIDEV, SENTRY 1200, FORFAIT LIVRAISON SANS LIT',
                'RMO1' => '',
                'RMO2' => '',
                'RMO3' => '',
                'RMO4' => '',
                'RMO5' => '',
                'DATE_FIN' => '2020-01-31',
                'AGE_MAX' => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'O',
                'ARBO1' => '1',
                'ARBO2' => '2',
                'ARBO3' => '1',
                'ARBO4' => '2',
                'ARBO5' => '3',
                'ARBO6' => '2',
                'ARBO7' => '1',
                'ARBO8' => '0',
                'ARBO9' => '0',
                'ARBO10' => '0',
                'PLACE' => '3',
                'PROTHESE' => '',
                'OLD_CODE' => '',
            ],
            [
                'CODE_TIPS' => '1231285',
                'NOM_COURT' => 'ESCARRES, MATELAS AIR MOTORISE,MEDIDEV,SENTRY 1200,FORFAIT LIVRAISON AVEC LIT',
                'RMO1' => '',
                'RMO2' => '',
                'RMO3' => '',
                'RMO4' => '',
                'RMO5' => '',
                'DATE_FIN' => '2020-01-31',
                'AGE_MAX' => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'O',
                'ARBO1' => '1',
                'ARBO2' => '2',
                'ARBO3' => '1',
                'ARBO4' => '2',
                'ARBO5' => '3',
                'ARBO6' => '2',
                'ARBO7' => '1',
                'ARBO8' => '0',
                'ARBO9' => '0',
                'ARBO10' => '0',
                'PLACE' => '4',
                'PROTHESE' => '',
                'OLD_CODE' => '',
            ],
        ];

        $chapter_data = [
            'ID' => '01212321',
            'PARENT' => '0121232',
            'INDEX' => '1',
            'LIBELLE' => 'Matelas ou surmatelas pneumatiques',
        ];

        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadList')->willReturn($codes_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        LppCodeRepository::setDatasource($ds);

        $expected = [];
        foreach ($codes_data as $code_data) {
            $expected[] = new CLPPCode($code_data);
        }

        $this->assertEquals($expected, (new CLPPChapter($chapter_data))->loadCodes());
    }
}
