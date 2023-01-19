<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Formula;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * Class FormulaOperatorEnum
 *
 * @package Ox\Mediboard\Jfse\Domain\Formula
 */
final class FormulaOperatorEnum extends JfseEnum
{
    private const NO_OPERATOR = "0";
    private const SUBSTRACT   = "1";
    private const ADD         = "2";
    private const MULTIPLY    = "3";
    private const DIVIDE      = "4";
}
