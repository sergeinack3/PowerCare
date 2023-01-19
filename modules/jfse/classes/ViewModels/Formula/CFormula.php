<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Formula;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CFormula
 *
 * @package Ox\Mediboard\Jfse\ViewModels\Formula
 */
class CFormula extends CJfseViewModel
{
    /** @var int */
    public $formula_id;

    /** @var float */
    public $pmss;

    /** @var string */
    public $prestation_number;

    /** @var string */
    public $formula_number;

    /** @var string */
    public $label;

    /** @var string */
    public $theoretical_calculation;

    /** @var bool */
    public $sts;

    /** @var array */
    public $parameters;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props                          = parent::getProps();
        $props["formula_id"]            = 'str';
        $props["pmss"]                  = 'float';
        $props["prestation_number"]     = 'str';
        $props["formula_number"]        = 'str';
        $props["label"]                 = 'str notNull';
        $props["theorical_calculation"] = 'str';
        $props["sts"]                   = 'bool';

        return $props;
    }

    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        /** @var Formula $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->parameters = [];
        foreach ($entity->getParameters() as $parameter) {
            $view_model->parameters[] = CFormulaParameter::getFromEntity($parameter);
        }

        return $view_model;
    }

    /**
     * @param Formula[] $entities
     *
     * @return self[]
     */
    public static function getListFromEntities(array $entities): array
    {
        $view_models = [];

        foreach ($entities as $entity) {
            $view_models[] = self::getFromEntity($entity);
        }

        return $view_models;
    }
}
