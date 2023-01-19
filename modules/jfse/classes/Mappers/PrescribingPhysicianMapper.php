<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;

class PrescribingPhysicianMapper extends AbstractMapper
{
    public function getPrescribingPhysiciansFromArray(array $physicians_array): array
    {
        return $this->makePhysicianArray($physicians_array['lstMedecinPrescripteur']);
    }

    public function getPhysiciansFromArray(array $physicians_array): array
    {
        return $this->makePhysicianArray($physicians_array['lstMedecins']);
    }

    public function makeStoreArrayFromPhysician(Physician $physician): array
    {
        $data = [
            "updateMedecinPrescripteur" => [
                "id"            => $physician->getId() ?? '',
                "nom"           => $physician->getLastName(),
                "prenom"        => $physician->getFirstName(),
                "noFacturation" => $physician->getInvoicingNumber(),
                "specialite"    => $physician->getSpeciality(),
                "type"          => $physician->getType(),
            ],
        ];

        $this->addOptionalValue("noStructure", $physician->getStructureId(), $data["updateMedecinPrescripteur"]);
        $this->addOptionalValue("noRPPS", $physician->getNationalId(), $data["updateMedecinPrescripteur"]);

        return $data;
    }

    public function makeDeleteArray(int $physician_id): array
    {
        return ["deleteMedecinPrescripteur" => ["id" => $physician_id]];
    }

    public function makeSetPrescribingPhysicianArray(
        string $invoice_id,
        DateTimeImmutable $date,
        string $origin,
        Physician $physician
    ): array {
        $data = [
            "idFacture"    => $invoice_id,
            "prescripteur" => [
                "datePrescription"    => $date->format('Ymd'),
                "originePrescription" => $origin,
                "medecin"             => [
                    "id"            => $physician->getId(),
                    "nom"           => $physician->getLastName(),
                    "prenom"        => $physician->getFirstName(),
                    "noFacturation" => $physician->getInvoicingNumber(),
                    "specialite"    => $physician->getSpeciality(),
                    "type"          => $physician->getType(),
                ],
            ],
        ];

        $this->addOptionalValue("rpps", $physician->getNationalId(), $data["prescripteur"]["medecin"]);
        $this->addOptionalValue("noStructure", $physician->getStructureId(), $data["prescripteur"]["medecin"]);

        return $data;
    }

    private function makePhysicianArray(array $physicians_array): array
    {
        $physicians = [];
        foreach ($physicians_array as $_physician) {
            $physicians[] = Physician::hydrate(
                [
                    'id'               => $_physician['id'],
                    'last_name'        => $_physician['nom'],
                    'first_name'       => $_physician['prenom'],
                    'invoicing_number' => $_physician['noFacturation'],
                    'national_id'      => $_physician['noRPPS'],
                    'structure_id'     => $_physician['noStructure'],
                    'type'             => $_physician['type'],
                    'speciality'       => $_physician['specialite'],
                ]
            );
        }

        return $physicians;
    }

    public static function getPhysicianFromResponse(array $response): Physician
    {
        return $physician = Physician::hydrate([
            'id'               => CMbArray::get($response, 'id', null),
            'last_name'        => CMbArray::get($response, 'nom'),
            'first_name'       => CMbArray::get($response, 'prenom'),
            'invoicing_number' => CMbArray::get($response, 'noFacturation'),
            'national_id'      => CMbArray::get($response, 'noRPPS'),
            'structure_id'     => CMbArray::get($response, 'noStructure'),
            'type'             => CMbArray::get($response, 'type'),
            'speciality'       => CMbArray::get($response, 'specialite'),
        ]);
    }
}
