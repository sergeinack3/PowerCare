<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Exceptions\V2;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Exception;

class CHL7v2ExceptionError extends CHL7v2Exception
{
    /** @var CHL7Acknowledgment */
    protected $ack;
    /** @var CExchangeHL7v2 */
    protected $exchange_hl7v2;
    /** @var array */
    protected $mb_error_codes;
    /** @var CMbObject */
    protected $object;
    /** @var string */
    protected $force_ack;

    /**
     * CHL7v2ExceptionError constructor.
     *
     * @param     $id
     * @param int $code
     */
    public function __construct($id, $code = 0)
    {
        parent::__construct($id, $code);
    }

    /**
     * @param CHL7Acknowledgment $ack
     * @param                    $mb_error_codes
     * @param null               $comments
     * @param CMbObject|null     $mbObject
     *
     * @return self
     */
    public static function ackAR(CExchangeHL7v2 $exchange_hl7v2, CHL7Acknowledgment $ack, $mb_error_codes, CMbObject $mbObject = null): self
    {
        $exception                 = new self(self::INVALID_ACK_APPLICATION_REJECT, '207');
        $exception->exchange_hl7v2 = $exchange_hl7v2;
        $exception->ack            = $ack;
        $exception->mb_error_codes = $exception->getError($mb_error_codes);
        $exception->object         = $mbObject;

        return $exception;
    }

    /**
     * @param CExchangeHL7v2     $exchange_HL7v2
     * @param CHL7Acknowledgment $ack
     * @param                    $mb_error_codes
     * @param null               $comments
     * @param CMbObject|null     $mbObject
     *
     * @return self
     */
    public static function ackAE(CExchangeHL7v2 $exchange_hl7v2, CHL7Acknowledgment $ack, $mb_error_codes, CMbObject $mbObject = null): self
    {
        $exception                 = new self(self::INVALID_ACK_APPLICATION_ERROR, '0');
        $exception->exchange_hl7v2 = $exchange_hl7v2;
        $exception->ack            = $ack;
        $exception->mb_error_codes = $exception->getError($mb_error_codes);
        $exception->object         = $mbObject;

        return $exception;
    }


    /**
     * @param $mb_errors
     *
     * @return array
     */
    protected function getError($mb_errors): array
    {
        if (!is_array($mb_errors)) {
            $mb_errors = [$mb_errors => ''];
        }

        $errors = [];
        foreach ($mb_errors as $code => $comment) {
            $errors[] = [
                'code' => $code,
                'type' => 'E',
                'comments' => $comment ?: null
            ];
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getAck(array $codes = []): ?string
    {
        if ($this->force_ack) {
            return $this->force_ack;
        }

        foreach ($codes as $index => $code) {
            if (!is_array($code)) {
                $code = $this->getError($code);
                $codes[$index] = $code;
            }
        }
        $codes = array_merge($codes, $this->mb_error_codes);

        $msg_ack = null;
        if ($this->id === self::INVALID_ACK_APPLICATION_ERROR) {
            $msg_ack = $this->exchange_hl7v2->setAckAE($this->ack, $codes, null, $this->object);
        } elseif ($this->id === self::INVALID_ACK_APPLICATION_REJECT) {
            $msg_ack = $this->exchange_hl7v2->setAckAR($this->ack, $codes, null, $this->object);
        }

        return $msg_ack;
    }

    /**
     * @param string $code
     * @param string $comment
     *
     * @return CHL7v2ExceptionError
     */
    public function setComment(string $code, string $comment): self
    {
        foreach ($this->mb_error_codes as $index => $error) {
            if (CMbArray::get($error, 'code') === $code) {
                $error['comments']            = $comment;
                $this->mb_error_codes[$index] = $error;

                return $this;
            }
        }

        return $this;
    }

    /**
     * @param string $ack
     *
     * @return CHL7v2ExceptionError
     */
    public static function setAckAR(string $ack): self
    {
        $exception = new self(self::INVALID_ACK_APPLICATION_REJECT, '207');
        $exception->force_ack = $ack;

        return $exception;
    }
}
