<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * An enumeration for the statuses of an Invoice
 *
 * @method static static PENDING()
 * @method static static VALIDATED()
 * @method static static SENT()
 * @method static static ACCEPTED()
 * @method static static REJECTED()
 * @method static static PAID()
 * @method static static PAYMENT_REJECTED()
 * @method static static NO_ACK_NEEDED()
 */
final class InvoiceStatusEnum extends JfseEnum
{
    /** @var string The invoice is being created */
    private const PENDING = 'pending';

    /** @var string The invoice has been validated by Jfse*/
    private const VALIDATED = 'validated';

    /** @var string The invoice has been sent to the insurance */
    private const SENT = 'sent';

    /** @var string The insurance has received a positive acknowledgement by the insurance */
    private const ACCEPTED = 'accepted';

    /** @var string The insurance has been rejected by the insurance */
    private const REJECTED = 'rejected';

    /** @var string The practitioner has received a payment by the insurance for this invoice */
    private const PAID = 'paid';

    /** @var string The payment by the insurance for this invoice has been rejected */
    private const PAYMENT_REJECTED = 'payment_rejected';

    /** @var string The invoice will not receive any acknowledgement by the insurance, usually for the degraded mode */
    private const NO_ACK_NEEDED = 'no_ack_needed';
}
