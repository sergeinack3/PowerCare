<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use DateTimeImmutable;
use Exception;
use Ox\Core\CMbObjectSpec;

/**
 * Represents a Payment made to a practitioner by an insurance organism
 */
final class CJfsePayment extends CJfseDataModel
{
    /** @var ?int Primary key */
    public ?int $jfse_payment_id = null;

    /** @var ?string The id of payment, given by the JFSE */
    public ?string $jfse_id = null;

    /** @var int|null  */
    public ?int $jfse_user_id = null;

    /** @var ?string */
    public ?string $date = null;

    /** @var ?string */
    public ?string $label = null;

    /** @var ?string */
    public ?string $organism = null;

    /** @var ?float */
    public ?float $amount = null;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'jfse_payments';
        $spec->key   = 'jfse_payment_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['jfse_id'] = 'str notNull';
        $props['jfse_user_id'] = 'ref class|CJfseUser notNull back|jfse_payments';
        $props['date'] = 'date';
        $props['label'] = 'str';
        $props['organism'] = 'str';
        $props['amount'] = 'currency';

        return $props;
    }

    /**
     * Returns the date of the last payment received by the user
     *
     * @param CJfseUser $user
     *
     * @return DateTimeImmutable|null
     */
    public static function getLastPaymentDateForUser(CJfseUser $user): ?DateTimeImmutable
    {
        $payment = new self();

        $date = null;
        try {
            $payment->loadObject(
                ['jfse_payments.jfse_user_id' => " = {$user->jfse_user_id}"],
                'jfse_payments.date DESC',
            );

            if ($payment->_id && $payment->date) {
                $date = new DateTimeImmutable($payment->date);
            }
        } catch (Exception $e) {
        }

        return $date;
    }
}
