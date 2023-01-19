<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\Domain\Formula\Parameter;

/**
 * Class FormulaMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
class FormulaMapper extends AbstractMapper
{
    /**
     * @param array $response
     *
     * @return Formula[]
     */
    public static function getFormulasFromResponse(array $response): array
    {
        $formulas = [];
        $data     = CMbArray::get($response, 'lstFormules', []);
        foreach ($data as $formula) {
            $formulas[$formula['noFormule']] = self::arrayToFormula($formula);
        }

        return $formulas;
    }

    public static function getFormulaFromResponse(array $response): Formula
    {
        return self::arrayToFormula($response);
    }

    public static function arrayToFormulas(array $data): array
    {
        return array_map(
            function (array $row): Formula {
                return FormulaMapper::arrayToFormula($row);
            },
            $data["lstFormules"]
        );
    }

    public static function arrayToFormula(array $row): Formula
    {
        $data = [
            "formula_id"              => CMbArray::get($row, 'idFormule'),
            "pmss"                    => CMbArray::get($row, "pmss"),
            "prestation_number"       => CMbArray::get($row, "noPrestationAttachee"),
            "formula_number"          => CMbArray::get($row, "noFormule"),
            "label"                   => CMbArray::get($row, "libelle"),
            "theoretical_calculation" => CMbArray::get($row, "calculTheorique"),
            "sts"                     => CMbArray::get($row, "sts"),
            "parameters"              => [],
        ];

        if (array_key_exists('lstParametres', $row) && is_array($row['lstParametres'])) {
            $data['parameters'] = self::arrayToParameters($row['lstParametres']);
        }

        return Formula::hydrate($data);
    }

    private static function arrayToParameters(array $data): array
    {
        return array_map(
            function (array $row): Parameter {
                return self::arrayToParameter($row);
            },
            $data
        );
    }

    private static function arrayToParameter(array $row): Parameter
    {
        return Parameter::hydrate(
            [
                "number" => $row["numero"],
                "label"  => $row["libelle"],
                "type"   => $row["type"],
                "value"  => (float)$row["valeur"],
            ]
        );
    }

    public static function makeArrayFromFormula(Formula $formula): array
    {
        $data = [
            'noPrestationAttachee' => '',
            'noFormule' => $formula->getFormulaNumber() ?? '',
            'libelle' => $formula->getLabel() ?? '',
            'calculTheorique' => $formula->getTheoreticalCalculation() ?? '',
        ];

        if ($formula->getParameters() && count($formula->getParameters())) {
            $parameters = [];
            foreach ($formula->getParameters() as $parameter) {
                $parameters[] = self::makeArrayFromParameter($parameter);
            }

            $data['lstParametres'] = $parameters;
        }

        return $data;
    }

    private static function makeArrayFromParameter(Parameter $parameter): array
    {
        return [
            'numero' => $parameter->getNumber(),
            'libelle' => $parameter->getLabel(),
            'type' => $parameter->getType(),
            'valeur' => $parameter->getValue(),
        ];
    }
}
