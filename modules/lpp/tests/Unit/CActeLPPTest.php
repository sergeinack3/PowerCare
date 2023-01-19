<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp\Tests\Unit;

use Ox\Core\CPDOMySQLDataSource;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\Lpp\CLPPCode;
use Ox\Mediboard\Lpp\CLPPDatedPricing;
use Ox\Mediboard\Lpp\Repository\LppCodeRepository;
use Ox\Mediboard\Lpp\Repository\LppPricingRepository;
use Ox\Tests\OxUnitTestCase;

class CActeLPPTest extends OxUnitTestCase
{
    public function testMakeFullCode(): void
    {
        $act                      = new CActeLPP();
        $act->code                = '2140455';
        $act->quantite            = '2';
        $act->code_prestation     = 'DVO';
        $act->type_prestation     = 'A';
        $act->montant_base        = 14.43;
        $act->montant_depassement = '-4.43';

        $this->assertEquals('2-2140455-DVO-A-14.43-*4.43', $act->makeFullCode());
    }

    public function testSetFullCode(): void
    {
        $code_data = [
            'CODE_TIPS'  => '2140455',
            'NOM_COURT'  => 'ORTHESE PLANTAIRE, AU-DESSUS DU 37',
            'RMO1'       => '',
            'RMO2'       => '',
            'RMO3'       => '',
            'RMO4'       => '',
            'RMO5'       => '',
            'DATE_FIN'   => null,
            'AGE_MAX'    => '0',
            'TYPE_PREST' => 'A',
            'INDICATION' => 'O',
            'ARBO1'      => '2',
            'ARBO2'      => '1',
            'ARBO3'      => '1',
            'ARBO4'      => '0',
            'ARBO5'      => '0',
            'ARBO6'      => '0',
            'ARBO7'      => '0',
            'ARBO8'      => '0',
            'ARBO9'      => '0',
            'ARBO10'     => '0',
            'PLACE'      => '6',
            'PROTHESE'   => '',
            'OLD_CODE'   => '201B00.1',
        ];

        $pricing_data = [
            'CODE_TIPS'  => '2140455',
            'DEBUTVALID' => '2009-08-01',
            'FINHISTO'   => null,
            'NAT_PREST'  => 'DVO',
            'ENTENTE'    => 'N',
            'ARRETE'     => '2009-07-13',
            'JO'         => '2009-07-31',
            'PUDEVIS'    => '0',
            'TARIF'      => '14.43',
            'MAJO_DOM1'  => '1.3',
            'MAJO_DOM2'  => '1.15',
            'MAJO_DOM3'  => '1.2',
            'MAJO_DOM4'  => '1.4',
            'QTE_MAX'    => '0',
            'MT_MAX'     => '0',
            'PUREGLEMEN' => '0',
            'PECP01'     => '00',
            'PECP02'     => '00',
            'PECP03'     => '00',
            'MAJO_DOM5'  => '1',
            'MAJO_DOM6'  => '1.36',
        ];

        $expected                             = new CActeLPP();
        $expected->code                       = '2140455';
        $expected->quantite                   = '2';
        $expected->code_prestation            = 'DVO';
        $expected->type_prestation            = 'A';
        $expected->montant_base               = '14.43';
        $expected->montant_depassement        = '11.14';
        $expected->montant_final              = 28.86;
        $expected->_code_lpp                  = new CLPPCode($code_data);
        $expected->_code_lpp->_last_pricing   = new CLPPDatedPricing($pricing_data);
        $expected->_dep                       = 0;
        $expected->_unauthorized_qual_depense = ["f", "e", "d", "a", "b"];
        $expected->_code_lpp->_unauthorized_expense_qualifying = $expected->_unauthorized_qual_depense;
        $expected->_montant_facture           = 40.00;
        $expected->_guid = 'CActeLPP-';
        $expected->_view = 'CActeLPP ';
        $expected->_shortview = '#';

        /* Prepare the mock of the datasource */
        $ds_code = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds_code->method('loadHash')->willReturn($code_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds_code->method('prepare')->willReturn('');

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

        /* Prepare the mock of the datasource */
        $ds_price = $this->getMockBuilder(CPDOMySQLDataSource::class)
            ->onlyMethods(['loadHash', 'prepare'])
            ->getMock();

        /* Set the methods return values */
        $ds_price->method('loadHash')->willReturn($pricing_data);
        /* We must mock the prepare method because otherwise an error will be thrown */
        $ds_price->method('prepare')->willReturn('');


        LppCodeRepository::setDatasources($ds_code, $ds_sv);
        LppPricingRepository::setDatasource($ds_price);

        $actual = new CActeLPP();
        $actual->setFullCode('2-2140455-DVO-A-14.43-11.14');

        $this->assertEquals($expected, $actual);
    }
}
