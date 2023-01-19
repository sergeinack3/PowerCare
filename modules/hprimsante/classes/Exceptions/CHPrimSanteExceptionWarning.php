<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Exceptions;

use Ox\Core\CMbException;
use Ox\Interop\Hprimsante\CExchangeHprimSante;
use Ox\Interop\Hprimsante\CHPrimSanteError;

class CHPrimSanteExceptionWarning extends CMbException
{
    /** @var string */
    private $type_error = 'P';

    /** @var string */
    private $code_error;

    /** @var string[] */
    private $address;

    /** @var string */
    private $field;

    /** @var string */
    private $comment;

    /**
     * CHL7v2ExceptionWarning constructor.
     *
     * @param string      $type_error
     * @param string      $code_error
     * @param string[]    $address
     * @param string|null $field
     * @param string|null $comment
     */
    public function __construct(
        string $type_error,
        string $code_error,
        array $address = [],
        string $field = null,
        string $comment = null
    ) {
        $text = "[CHPrimSanteEvent, type<$type_error>, error<$code_error>]";
        parent::__construct($text);

        $this->type_error = $type_error;
        $this->code_error = $code_error;
        $this->address    = $address;
        $this->comment    = $comment;
        $this->field      = $field;
    }

    /**
     * @param CExchangeHprimSante $exchange
     *
     * @return CHPrimSanteError
     */
    public function getHprimError(CExchangeHprimSante $exchange): CHPrimSanteError
    {
        return new CHPrimSanteError(
            $exchange,
            $this->type_error,
            $this->code_error,
            $this->address,
            $this->field,
            $this->comment
        );
    }
}
