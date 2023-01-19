<?php

/**
 * @package Mediboard\Patient\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Tests\Unit;

use Exception;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Tests\OxUnitTestCase;

class COperationTest extends OxUnitTestCase
{
    /**
     * @param COperation  $operation
     * @param string|null $expected
     *
     * @dataProvider getLibellesActesPrevusProvider
     */
    public function testGetLibellesActesPrevus(COperation $operation, ?string $expected = null): void
    {
        $actual = $operation->getLibellesActesPrevus();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function getLibellesActesPrevusProvider(): array
    {
        $chir = new CMediusers();
        $chir->_id = 1;

        $op_null          = new COperation();
        $op_null->libelle = null;
        $this->setChir($op_null, $chir);

        // Operation avec un libelle vide
        $op_empty_string          = new COperation();
        $op_empty_string->libelle = "";
        $this->setChir($op_empty_string, $chir);

        // Operation avec un libelle sans code ccam
        $op_without_code          = new COperation();
        $op_without_code->libelle = "Lorem Ipsum";
        $this->setChir($op_without_code, $chir);

        // Operation avec un libelle avec 1 code ccam
        $op_with_one_code             = new COperation();
        $op_with_one_code->libelle    = "Lorem Ipsum";
        $this->setChir($op_with_one_code, $chir);
        $op_with_one_code->codes_ccam = "AAFA001";

        // Operation avec un libelle avec 2 code ccam
        $op_with_two_codes             = new COperation();
        $op_with_two_codes->libelle    = "Lorem Ipsum";
        $this->setChir($op_with_two_codes, $chir);
        $op_with_two_codes->codes_ccam = "AAFA001|AAFA002";

        return [
            "label null"                => [$op_null, null],
            "empty string label"        => [$op_empty_string, ""],
            "operation label only"      => [$op_without_code, "Lorem Ipsum"],
            "operation label + 1 code"  => [$op_with_one_code, "Lorem Ipsum - AAFA001"],
            "operation label + 2 codes" => [$op_with_two_codes, "Lorem Ipsum - AAFA001 - AAFA002"],
        ];
    }

    protected function setChir(COperation $operation, CMediusers $chir): void
    {
        $operation->chir_id         = $chir->_id;
        $operation->_fwd['chir_id'] = $chir;
    }
}
