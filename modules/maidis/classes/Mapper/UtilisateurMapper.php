<?php
/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\User;

/**
 * Description
 */
class UtilisateurMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $row['USER_ID'],
            'username'    => $row['USERNAME'],
        ];

        return User::fromState($map);
    }
}
