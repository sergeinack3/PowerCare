<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use Ox\Core\CMbString;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Patient;

/**
 * Description
 */
class PatientMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'     => $row['PATIENT_ID'],
            'nom'             => $row['LASTNAME'],
            'prenom'          => $row['FIRSTNAME'],
            'naissance'       => $this->convertDate($row['BIRTHDATE']),
            'nom_jeune_fille' => $row['MAIDENNAME'] ?? null,
            'sexe'            => $this->convertGender($row['GENDER']),
            'matricule'       => ($this->checkInsee($row['SSNB'])) ? $row['SSNB'] : null,
            'cp'              => $row['ZIPCODE'] ?? null,
            'adresse'         => $this->buildInfosFromMultipleFields(
                $this->sanitizeLine($row['LINE1']),
                $this->sanitizeLine($row['LINE2']),
                $this->sanitizeLine($row['LINE3'])
            ),
            'ville'           => $row['CITY'],
            'civilite'        => $this->convertCivility($row['CIVILITY']),
        ];

        return Patient::fromState($map);
    }

    private function convertGender(?string $gender): ?string
    {
        switch ($gender) {
            case 1:
                return 'm';
            case 2:
                return 'f';
            case 3:
            default:
                return 'u';
        }
    }

    private function convertCivility(?string $civility): ?string
    {
        switch (CMbString::lower($civility)) {
            case 'm':
                return 'm';
            case 'mme':
            case 'melle':
                return 'mme';
            case 'enfant':
            case 'bébé':
                return 'enf';
            default:
                return null;
        }
    }
}
