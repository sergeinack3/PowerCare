<?php

/**
 * @package Mediboard\Maidis
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Maidis\Mapper;

use Ox\Core\CMbString;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\Medecin;

/**
 * Description
 */
class MedecinMapper extends AbstractMaidisMapper
{

    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id' => $row['CORRESP_ID'],
            'nom'         => $row['NAME'],
            'prenom'      => $row['FIRSTNAME'],
            'titre'       => $this->convertTitle($row['TITLE']),
            'disciplines' => $row['SPECIALIZATION'] ?? null,
            'rpps'        => $row['RPPSCODE'] ?? null,
            'adeli'       => $row['EXTERNALCODE'] ?? null,

        ];

        return Medecin::fromState($map);
    }

    private function convertTitle(?string $title): ?string
    {
        if (!$title) {
            return null;
        }

        switch (CMbString::lower($title)) {
            case 'docteur':
            case 'dr':
            case 'de':
            case 'dentiste':
            case 'medecin':
            case 'chef de service':
            case 'chirurgien':
            case 'chef service':
            case 'gynecologue':
            case 'dorteur':
            case 'docteuc':
            case 'docteurt':
                return 'dr';

            case 'mr':
            case 'monsieur':
                return 'mr';

            case 'mme':
            case 'madame':
                return 'mme';

            case 'professeur':
                return 'pr';

            case 'resp. admin':
            case 'clinique':
            case 'cardiologue':
            case 'hopital':
            case 'diététicienne':
            case 'responsable adm.':
            case 'maire':
            case 'agent d\'acceuil':
            case 'resp. administratif':
            default:
                return null;
        }
    }
}
