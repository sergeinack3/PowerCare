<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Jfse\Domain\Noemie\InvoiceSetStatusEnum;

/**
 * Represents a set of invoices, sent to the insurance by Jfse
 */
final class CJfseInvoiceSet extends CMbObject
{
    /** @var ?int Primary key */
    public ?int $jfse_invoice_set_id;

    /** @var ?string The id of set, given by the JFSE */
    public ?string $jfse_id;

    /** @var ?int */
    public ?int $jfse_user_id;

    /** @var ?int */
    public ?int $number;

    /** @var ?string */
    public ?string $date;

    /** @var ?string */
    public ?string $status;

    /** @var ?string If the set is rejected by the insurance, this field will contain the reason */
    public ?string $return_label;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'jfse_invoice_sets';
        $spec->key   = 'jfse_invoice_set_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['jfse_id']      = 'str notNull';
        $props['jfse_user_id'] = 'ref class|CJfseUser notNull back|jfse_sets';
        $props['number']       = 'num';
        $props['date']         = 'date';
        $props['status']       = 'enum list|accepted|rejected';
        $props['return_label'] = 'str';

        return $props;
    }
}
