<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use DateTime;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\File;

/**
 * Description
 */
class FileMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'     => $row['MI_ID'],
            'consultation_id' => $row['CONTACT_ID'],
            'file_name'       => 'Importation.txt',
            'file_date'       => $this->convertDateTime($row['MIDATE']) ?? new DateTime(),
            'file_type'       => 'text/plain',
            'author_id'       => $row['USER_ID'],
            'file_content'    => $row['MITEXT'],
        ];

        return File::fromState($map);
    }
}
