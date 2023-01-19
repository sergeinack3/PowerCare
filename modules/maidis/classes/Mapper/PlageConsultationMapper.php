<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use DateTime;
use Ox\Core\CMbString;
use Ox\Import\Framework\Entity\Correspondant;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\PlageConsult;

/**
 * Description
 */
class PlageConsultationMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $row['CONTACT_ID'],
            'chir_id'     => $row['USER_ID'],
            'date'        => $this->convertDateTime($row['BEGINDATETIME']),
            'freq'        => DateTime::createFromFormat('H:i:s', '00:15:00'),
            'debut'       => DateTime::createFromFormat(
                'H:i:s',
                ($this->configuration['plage_consult_start_hour'] ?? '09:00:00')
            ),
            'fin'         => DateTime::createFromFormat(
                'H:i:s',
                ($this->configuration['plage_consult_end_hour'] ?? '18:00:00')
            ),
        ];

        return PlageConsult::fromState($map);
    }
}
