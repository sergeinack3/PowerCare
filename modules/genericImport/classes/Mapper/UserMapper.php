<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Mediusers\Import\OxPivotMediuser;

/**
 * Description
 */
class UserMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotMediuser::FIELD_ID),
            'username'    => $this->getValue($row, OxPivotMediuser::FIELD_NOM) . ' '
                . $this->getValue($row, OxPivotMediuser::FIELD_PRENOM),
            'first_name'  => $this->getValue($row, OxPivotMediuser::FIELD_NOM),
            'last_name'   => $this->getValue($row, OxPivotMediuser::FIELD_PRENOM),
        ];

        return User::fromState($map);
    }
}
