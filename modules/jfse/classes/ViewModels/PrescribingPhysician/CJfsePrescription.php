<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\PrescribingPhysician;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Invoicing\Prescription;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianOriginEnum;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CJfsePrescription extends CJfseViewModel
{
    /** @var string */
    public $invoice_id;
    /** @var string */
    public $date;
    /** @var string */
    public $origin;
    /** @var CPrescribingPhysician */
    public $prescriber;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoice_id']          = 'str';
        $props['date']   = 'date notNull';
        $props['origin'] = PhysicianOriginEnum::getProp() . ' notNull';

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
        /** @var Prescription $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->prescriber = $entity->getPrescriber() ?
            CPrescribingPhysician::getFromEntity($entity->getPrescriber()) : new CPrescribingPhysician();

        return $view_model;
    }
}
