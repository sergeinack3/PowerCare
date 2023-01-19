<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use Ox\Core\CMbString;
use Ox\Import\Framework\Entity\Correspondant;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Mediboard\Galaxie\Import\Entity\GalaxieInfosPatient;

/**
 * Description
 */
class GalaxieInfosPatientMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'                => $row['PATIENTSOLDE_ID'],
            'patient_id'                 => $row['PATIENT_ID'],
            'situation'                  => $row['SOLDE'],
            'alerte_medicale'            => $row['ALERTE_MEDICALE'],
            'finess_etablissement_solde' => $row['FINESS_ETABLISSEMENT_SOLDE'],
        ];

        return GalaxieInfosPatient::fromState($map);
    }
}
