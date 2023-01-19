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

/**
 * Description
 */
class ConsultationMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'     => $row['OBJECT_ID'],
            'plageconsult_id' => $row['CONTACT_ID'],
            'patient_id'      => $row['PATIENT_ID'],
            'rques'           => $this->buildInfosFromMultipleFields($row['NOTES'], $row['CONFIDENTIALNOTES']),
            'motif'           => $row['MOTIF'] ?? 'importation',
            'examen'          => $this->buildInfosFromMultipleFields($row['REPORTEDSYMPTOM'], $row['OBSERVEDSYMPTOM']),
            'conclusion'      => $row['CONCLUSION'] ?? null,
            'resultats'       => $row['ACTIONS'] ?? null,
            'heure'           => $this->convertDateTime($row['BEGINDATETIME'])
                ?? DateTime::createFromFormat('H:i:s', '09:00:00'),
            'duree'           => 1,
        ];

        return Consultation::fromState($map);
    }
}
