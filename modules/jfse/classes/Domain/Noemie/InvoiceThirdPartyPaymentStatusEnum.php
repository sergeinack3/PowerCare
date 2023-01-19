<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * Contains the different possible statuses for the invoice third party payment
 *
 * @method static static PAID()
 * @method static static PENDING()
 * @method static static REJECTED()
 * @method static static ANOMALY()
 */
class InvoiceThirdPartyPaymentStatusEnum extends JfseEnum
{
    protected const PAID = 'P';

    protected const PENDING = 'C';

    protected const REJECTED = 'R';

    /** @var string Used when a payment is made for an invoice, but the AMO and AMC amount do not match the ones computed in the invoice */
    protected const ANOMALY = 'A';
}
