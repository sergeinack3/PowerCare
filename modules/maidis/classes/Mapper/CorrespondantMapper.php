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

/**
 * Description
 */
class CorrespondantMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $row['PATIENTCORRESP_ID'],
            'medecin_id'  => $row['CORRESP_ID'],
            'patient_id'  => $row['PATIENT_ID'],
        ];

        return Correspondant::fromState($map);
    }
}
