<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;

/**
 * Class FormulaOperandMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
class FormulaOperandMapper extends AbstractMapper
{
    public static function getOperandsFromResponse(Response $response): array
    {
        $operands = [];
        $data     = CMbArray::get($response->getContent(), 'lst', []);
        foreach ($data as $operand) {
            $operands[] =
                [
                    "code"  => intval(CMbArray::get($operand, "code")),
                    "label" => CMbArray::get($operand, "libelle"),
                ];
        }

        return $operands;
    }
}
