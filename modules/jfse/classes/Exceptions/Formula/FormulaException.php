<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions\Formula;

use Ox\Mediboard\Jfse\Exceptions\JfseException;

/**
 * Class FormulaException
 *
 * @package Ox\Mediboard\Jfse\Exceptions\Formula
 */
class FormulaException extends JfseException
{
    /**
     * FormulaException constructor.
     *
     * @param string         $name
     * @param string         $locale
     * @param array          $locale_args
     * @param int            $code
     * @param Throwable|null $previous
     */
    final public function __construct(
        string $name,
        string $locale,
        array $locale_args = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($name, $locale, $locale_args, $code, $previous);
    }


    /**
     * @return static
     */
    public static function invalidFormulaId(): self
    {
        return new static('Invalid Formula Id', 'CFormula-invalid formula id');
    }

    /**
     * @return static
     */
    public static function invalidFormulaFormFields(): self
    {
        return new static('Invalid Formula Form Fields', 'CFormula-invalid formula form fields');
    }
}
