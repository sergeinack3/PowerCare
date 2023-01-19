<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\PrescribingPhysician;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianTypeEnum;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CPrescribingPhysician extends CJfseViewModel
{
    /** @var int */
    public $id;
    /** @var string */
    public $first_name;
    /** @var string */
    public $last_name;
    /** @var string */
    public $invoicing_number;
    /** @var string */
    public $speciality;
    /** @var string */
    public $type;
    /** @var int */
    public $national_id;
    /** @var string */
    public $structure_id;

    /** @var string */
    public $speciality_label;
    /** @var string */
    public $type_label;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']               = 'num';
        $props['first_name']       = 'str maxLength|25 notNull';
        $props['last_name']        = 'str maxLength|25 notNull';
        $props['invoicing_number'] = 'str length|9 notNull';
        $props['speciality']       = 'str maxLength|2 notNull';
        $props['type']             = PhysicianTypeEnum::getProp();
        $props['national_id']      = 'str length|11';
        $props['structure_id']     = 'str maxLength|14';

        return $props;
    }

    public function __toString(): string
    {
        return strtoupper($this->last_name) . ' ' . $this->first_name;
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
        /** @var CPrescribingPhysician $entity */
        $view_model = parent::getFromEntity($entity);

        /* The type must be converted to a string for the mb_field to work as intended */
        $view_model->type = "{$view_model->type}";

        return $view_model;
    }
}
