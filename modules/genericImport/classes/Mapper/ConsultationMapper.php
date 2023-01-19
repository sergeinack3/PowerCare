<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Cabinet\Import\OxPivotConsultation;

/**
 * Description
 */
class ConsultationMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'      => $this->getValue($row, OxPivotConsultation::FIELD_ID),
            'heure'            => $this->getValue($row, OxPivotConsultation::FIELD_HEURE)
                ? $this->convertToDateTime($row[OxPivotConsultation::FIELD_HEURE]) : null,
            'duree'            => $this->getValue($row, OxPivotConsultation::FIELD_DUREE)
                ? $this->convertToDuration($row[OxPivotConsultation::FIELD_DUREE]) : 1,
            'motif'            => $this->getValue($row, OxPivotConsultation::FIELD_MOTIF),
            'rques'            => $this->getValue($row, OxPivotConsultation::FIELD_REMARQUES),
            'examen'           => $this->getValue($row, OxPivotConsultation::FIELD_EXAMEN),
            'traitement'       => $this->getValue($row, OxPivotConsultation::FIELD_TRAITEMENT),
            'histoire_maladie' => $this->getValue($row, OxPivotConsultation::FIELD_HISTOIRE_MALADIE),
            'conclusion'       => $this->getValue($row, OxPivotConsultation::FIELD_CONCLUSION),
            'resultats'        => $this->getValue($row, OxPivotConsultation::FIELD_RESULTATS),
            'plageconsult_id'  => $this->getValue($row, OxPivotConsultation::FIELD_ID),
            'patient_id'       => $this->getValue($row, OxPivotConsultation::FIELD_PATIENT),
        ];

        return Consultation::fromState($map);
    }
}
