<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Tests\Unit;

use Ox\Core\CPDOMySQLDataSource;
use Ox\Mediboard\Lpp\CLPPChapter;
use Ox\Mediboard\Lpp\CLPPCode;
use Ox\Mediboard\Lpp\CLPPDatedPricing;
use Ox\Mediboard\Lpp\Repository\LppChapterRepository;
use Ox\Mediboard\Lpp\Repository\LppCodeRepository;
use Ox\Mediboard\Lpp\Repository\LppPricingRepository;
use Ox\Tests\OxUnitTestCase;

class CLPPCodeTest extends OxUnitTestCase
{
    public function testLoadPricings(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $pricings_data = [
            0 => [
                'CODE_TIPS'  => '1189270',
                'DEBUTVALID' => '2021-07-01',
                'FINHISTO'   => null,
                'NAT_PREST'  => 'RJT',
                'ENTENTE'    => 'N',
                'ARRETE'     => '2020-11-26',
                'JO'         => '2020-12-05',
                'PUDEVIS'    => '0',
                'TARIF'      => '304.9',
                'MAJO_DOM1'  => '1.3',
                'MAJO_DOM2'  => '1.15',
                'MAJO_DOM3'  => '1.2',
                'MAJO_DOM4'  => '1.2',
                'QTE_MAX'    => '0',
                'MT_MAX'     => '0',
                'PUREGLEMEN' => '0',
                'PECP01'     => '00',
                'PECP02'     => '00',
                'PECP03'     => '00',
                'MAJO_DOM5'  => '1',
                'MAJO_DOM6'  => '1.36',
            ],
            1 => [
                'CODE_TIPS'  => '1189270',
                'DEBUTVALID' => '2003-09-08',
                'FINHISTO'   => '2021-06-30',
                'NAT_PREST'  => 'AAD',
                'ENTENTE'    => 'N',
                'ARRETE'     => '2003-06-26',
                'JO'         => '2003-09-06',
                'PUDEVIS'    => '0',
                'TARIF'      => '304.9',
                'MAJO_DOM1'  => '1.3',
                'MAJO_DOM2'  => '1.15',
                'MAJO_DOM3'  => '1.2',
                'MAJO_DOM4'  => '1.2',
                'QTE_MAX'    => '0',
                'MT_MAX'     => '0',
                'PUREGLEMEN' => '0',
                'PECP01'     => '00',
                'PECP02'     => '00',
                'PECP03'     => '00',
                'MAJO_DOM5'  => '1',
                'MAJO_DOM6'  => '1.36',
            ],
        ];

        /* Set the methods return values */
        $ds->method('loadList')->willReturn($pricings_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppPricingRepository::setDatasource($ds);

        $code     = new CLPPCode(['CODE_TIPS' => '1189270']);
        $pricings = $code->loadPricings();

        $expected = [
            '2021-07-01' => new CLPPDatedPricing($pricings_data[0]),
            '2003-09-08' => new CLPPDatedPricing($pricings_data[1]),
        ];

        $this->assertEquals($expected, $pricings);
    }

    public function testLoadLastPricing(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        $pricing_data = [
            'CODE_TIPS'  => '1189270',
            'DEBUTVALID' => '2021-07-01',
            'FINHISTO'   => null,
            'NAT_PREST'  => 'RJT',
            'ENTENTE'    => 'N',
            'ARRETE'     => '2020-11-26',
            'JO'         => '2020-12-05',
            'PUDEVIS'    => '0',
            'TARIF'      => '304.9',
            'MAJO_DOM1'  => '1.3',
            'MAJO_DOM2'  => '1.15',
            'MAJO_DOM3'  => '1.2',
            'MAJO_DOM4'  => '1.2',
            'QTE_MAX'    => '0',
            'MT_MAX'     => '0',
            'PUREGLEMEN' => '0',
            'PECP01'     => '00',
            'PECP02'     => '00',
            'PECP03'     => '00',
            'MAJO_DOM5'  => '1',
            'MAJO_DOM6'  => '1.36',
        ];

        /* Set the methods return values */
        $ds->method('loadHash')->willReturn($pricing_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppPricingRepository::setDatasource($ds);

        $code    = new CLPPCode(['CODE_TIPS' => '1189270']);
        $pricing = $code->loadLastPricing();

        $expected = new CLPPDatedPricing($pricing_data);

        $this->assertEquals($expected, $pricing);
    }

    public function testLoadCompatibilities(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $codes_data = [
            0 => [
                'CODE_TIPS'  => '3162476',
                'NOM_COURT'  => 'IMPLANT OPHTALMOLOGIQUE INTRA ORBITAIRE, BILLE REHABITABLE ENVELOPPEE',
                'RMO1'       => '',
                'RMO2'       => '',
                'RMO3'       => '',
                'RMO4'       => '',
                'RMO5'       => '',
                'DATE_FIN'   => '2005-03-01',
                'AGE_MAX'    => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'N',
                'ARBO1'      => '3',
                'ARBO2'      => '1',
                'ARBO3'      => '3',
                'ARBO4'      => '5',
                'ARBO5'      => '0',
                'ARBO6'      => '0',
                'ARBO7'      => '0',
                'ARBO8'      => '0',
                'ARBO9'      => '0',
                'ARBO10'     => '0',
                'PLACE'      => '3',
                'PROTHESE'   => '',
                'OLD_CODE'   => '301D05.3',
            ],
            1 => [
                'CODE_TIPS'  => '3120510',
                'NOM_COURT'  => 'IMPLANT OPHTALMOLOGIQUE INTRA ORBITAIRE, BILLE',
                'RMO1'       => '',
                'RMO2'       => '',
                'RMO3'       => '',
                'RMO4'       => '',
                'RMO5'       => '',
                'DATE_FIN'   => '2005-03-01',
                'AGE_MAX'    => '0',
                'TYPE_PREST' => 'A',
                'INDICATION' => 'N',
                'ARBO1'      => '3',
                'ARBO2'      => '1',
                'ARBO3'      => '3',
                'ARBO4'      => '5',
                'ARBO5'      => '0',
                'ARBO6'      => '0',
                'ARBO7'      => '0',
                'ARBO8'      => '0',
                'ARBO9'      => '0',
                'ARBO10'     => '0',
                'PLACE'      => '1',
                'PROTHESE'   => '',
                'OLD_CODE'   => '301D05.1',
            ],
        ];

        /* Set the methods return values */
        $ds->method('loadList')->willReturn($codes_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');


        LppCodeRepository::setDatasource($ds);

        $code  = new CLPPCode(['CODE_TIPS' => '3100179']);
        $codes = $code->loadCompatibilities();

        $expected = [];
        foreach ($codes_data as $code_data) {
            $expected[] = new CLPPCode($code_data);
        }

        $this->assertEquals($expected, $codes);
    }

    public function testLoadIncompatibilities(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadList', 'prepare'])
            ->getMock();

        $codes_data = [
            0 => [
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
            1 => [
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

        $code  = new CLPPCode(['CODE_TIPS' => '3204460']);
        $codes = $code->loadIncompatibilities();

        $expected = [];
        foreach ($codes_data as $code_data) {
            $expected[] = new CLPPCode($code_data);
        }

        $this->assertEquals($expected, $codes);
    }

    public function testGetParentId(): void
    {
        $code = new CLPPCode([
                                 'CODE_TIPS' => '3158724',
                                 'ARBO1'     => '3',
                                 'ARBO2'     => '10',
                                 'ARBO3'     => '13',
                                 'ARBO4'     => '11',
                                 'ARBO5'     => '15',
                                 'ARBO6'     => '14',
                                 'ARBO7'     => '12',
                                 'ARBO8'     => '0',
                                 'ARBO9'     => '0',
                                 'ARBO10'    => '0',
                             ]);

        $this->assertEquals('03ADBFEC', $code->getParentId());
    }

    public function testLoadParent(): void
    {
        $chapter_data = [
            'ID'      => '032212',
            'PARENT'  => '03221',
            'INDEX'   => '2',
            'LIBELLE' => 'Implants osseux de forme geometrique',
        ];

        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadHash')->will(
            $this->onConsecutiveCalls(
                false,
                $chapter_data
            )
        );
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        LppChapterRepository::setDatasource($ds);

        $chapter = ((new CLPPCode([
                                      'CODE_TIPS' => '3204460',
                                      'ARBO1'     => '3',
                                      'ARBO2'     => '10',
                                      'ARBO3'     => '13',
                                      'ARBO4'     => '11',
                                      'ARBO5'     => '15',
                                      'ARBO6'     => '14',
                                      'ARBO7'     => '12',
                                      'ARBO8'     => '0',
                                      'ARBO9'     => '0',
                                      'ARBO10'    => '0',
                                  ]))->loadParent());

        $this->assertEquals(new CLPPChapter($chapter_data), $chapter);
    }

    public function testLoadParentNull(): void
    {
        /* Prepare the mock of the datasource */
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds->method('loadHash')->willReturn(null);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds->method('prepare')->willReturn('');

        LppChapterRepository::setDatasource($ds);

        $chapter = ((new CLPPCode([
            'CODE_TIPS' => '3204460',
            'ARBO1'     => '3',
            'ARBO2'     => '10',
            'ARBO3'     => '13',
            'ARBO4'     => '11',
            'ARBO5'     => '15',
            'ARBO6'     => '14',
            'ARBO7'     => '12',
            'ARBO8'     => '0',
            'ARBO9'     => '0',
            'ARBO10'    => '0',
        ]))->loadParent());

        $this->assertNull($chapter);
    }

    public function testGetQualificatifsDepense(): void
    {
        $ds = $this->getMockBuilder(CPDOMySQLDataSource::class)->getMock();

        /* Prepare the mock of the datasource */
        $ds_sv = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds_sv->method('loadHash')->willReturn([
            'g' => '1',
            'f' => '0',
            'e' => '0',
            'd' => '0',
            'n' => '1',
            'a' => '0',
            'b' => '0',
        ]);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds_sv->method('prepare')->willReturn('');

        LppCodeRepository::setDatasources($ds, $ds_sv);

        $code                 = new CLPPCode(['CODE_TIPS' => '3204460']);
        $code->_last_pricing  = new CLPPDatedPricing(['CODE_TIPS' => '3204460', 'NAT_PREST' => 'DVO']);
        $forbidden_qualifiers = $code->getQualificatifsDepense();

        $this->assertEquals(["f", "e", "d", "a", "b"], $forbidden_qualifiers);
    }
}
