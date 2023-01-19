<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use DateTime;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Cabinet\Import\OxPivotConsultation;

/**
 * Description
 */
class PlageConsultationMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $this->getValue($row, OxPivotConsultation::FIELD_ID),
            'date'        => $this->getValue($row, OxPivotConsultation::FIELD_DATE)
                ? $this->convertToDateTime($row[OxPivotConsultation::FIELD_DATE]) : null,
            'freq'        => new DateTime('00:15:00'),
            'debut'       => new DateTime($this->configuration['plageconsult_heure_debut']),
            'fin'         => new DateTime($this->configuration['plageconsult_heure_fin']),
            'libelle'     => 'Importation',
            'chir_id'     => $this->getValue($row, OxPivotConsultation::FIELD_PRATICIEN),
        ];

        return PlageConsult::fromState($map);
    }
}
