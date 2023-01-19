<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\CarePath;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathStatusEnum;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CCarePath extends CJfseViewModel
{
    /** @var string */
    public $invoice_id;
    /** @var string */
    public $indicator;
    /** @var string */
    public $install_date;
    /** @var string */
    public $poor_md_zone_install_date;
    /** @var int */
    public $declaration;
    /** @var int */
    public $status;
    /** @var CCarePathDoctor */
    public $doctor;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoice_id']                = 'str notNull';
        $props['indicator']                 = CarePathEnum::getProp() . ' notNull';
        $props['install_date']              = 'date';
        $props['poor_md_zone_install_date'] = 'date';
        $props['declaration']               = 'bool';
        $props['status']                    = CarePathStatusEnum::getProp();

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
        /** @var CarePath $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->doctor = CCarePathDoctor::getFromEntity($entity->getDoctor());

        return $view_model;
    }
}
