<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\UserManagement;

use Ox\Mediboard\Jfse\Domain\UserManagement\EmployeeCard;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CEmployeeCard extends CJfseViewModel
{
    /** @var int */
    public $id;

    /** @var int */
    public $establishment_id;

    /** @var string */
    public $name;

    /** @var string */
    public $invoicing_number;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']               = 'num';
        $props['establishment_id'] = 'num';
        $props['name']             = 'str';
        $props['invoicing_number'] = 'str';

        return $props;
    }

    /**
     * @param EmployeeCard[] $employee_cards
     *
     * @return CJfseUserView[]
     */
    public static function getFromEmployeeCards(array $employee_cards): array
    {
        $views = [];
        foreach ($employee_cards as $employee_card) {
            $views[] = CEmployeeCard::getFromEntity($employee_card);
        }

        return $views;
    }
}
