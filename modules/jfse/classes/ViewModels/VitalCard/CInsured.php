<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CInsured extends CJfseViewModel
{
    /** @var string */
    public $nir;

    /** @var string */
    public $nir_key;

    /** @var string */
    public $last_name;

    /** @var string */
    public $first_name;

    /** @var string */
    public $birth_name;

    /** @var string */
    public $regime_code;

    /** @var string */
    public $regime_label;

    /** @var string */
    public $managing_fund;

    /** @var string */
    public $managing_center;

    /** @var string */
    public $managing_code;

    /** @var string */
    public $_amo_organism;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['nir'] = 'str';
        $props['nir_key'] = 'str';
        $props['last_name'] = 'str';
        $props['first_name'] = 'str';
        $props['birth_name'] = 'str';
        $props['regime_code'] = 'str';
        $props['regime_label'] = 'str';
        $props['managing_fund'] = 'str';
        $props['managing_center'] = 'str';
        $props['managing_code'] = 'str';

        return $props;
    }

    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        $view_model = parent::getFromEntity($entity);
        $view_model->_amo_organism =
            $view_model->regime_code . $view_model->managing_fund . $view_model->managing_center;

        return $view_model;
    }
}
