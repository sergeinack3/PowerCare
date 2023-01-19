<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Invoicing\Complement;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CComplement extends CJfseViewModel
{
    /** @var string */
    public $type;

    /** @var int */
    public $amo_third_party_payment;

    /** @var float */
    public $pec_amount;

    /** @var float */
    public $total;

    /** @var float */
    public $amo_total;

    /** @var float */
    public $patient_total;

    /** @var float */
    public $amount_owed_amo;

    /** @var CComplementAct[] */
    public $acts;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['type'] = 'enum list|smg|at';
        $props['amo_third_party_payment'] = 'num';
        $props['pec_amount'] = 'currency';
        $props['total'] = 'currency';
        $props['amo_total'] = 'currency';
        $props['patient_total'] = 'currency';
        $props['amount_owed_amo'] = 'currency';

        return $props;
    }

    /**
     * Create a new view model and sets its properties from the given entity
     *
     * @param AbstractEntity $entity
     *
     * @return static|null
     */
    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        /** @var Complement $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->acts = [];
        foreach ($entity->getActs() as $act) {
            $view_model->acts[] = CComplementAct::getFromEntity($act);
        }

        return $view_model;
    }
}
