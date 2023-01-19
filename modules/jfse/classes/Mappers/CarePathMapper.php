<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Exceptions\CarePath\CarePathMappingException;

class CarePathMapper extends AbstractMapper
{
    public static function getSaveRequestFromEntity(CarePath $care_path): array
    {
        return [
            "idFacture"     => $care_path->getInvoiceId(),
            "parcoursSoins" => self::getArrayFromEntity($care_path),
        ];
    }

    public static function getArrayFromEntity(CarePath $care_path): array
    {
        $indicator = $care_path->getIndicator();
        $data = ["indicateur" => $care_path->getIndicator()->getValue()];

        self::addOptionalValue("declaration", self::getDeclaration($care_path), $data);

        // Deal with other information
        if (in_array($indicator, [CarePathEnum::ORIENTED_BY_NRP(), CarePathEnum::ORIENTED_BY_RP()])) {
            $data['medecin'] = [
                'nom'              => $care_path->getDoctor()->getLastName(),
                'prenom'           => $care_path->getDoctor()->getFirstName(),
            ];

            if ($care_path->getDoctor()->getInvoicingId()) {
                $data['medecin']['noIdentification'] = $care_path->getDoctor()->getInvoicingId();
            }
        } elseif ($indicator == CarePathEnum::RECENTLY_INSTALLED_RP()) {
            $data['dateInstallation'] = $care_path->getInstallDate()->format('Ymd');
        } elseif ($indicator == CarePathEnum::POOR_MEDICALIZED_ZONE()) {
            $data['dateInstallationZoneSousMedicalisee'] = $care_path->getPoorMdZoneInstallDate(
            )->format('Ymd');
        }

        return $data;
    }

    private static function getDeclaration(CarePath $care_path): ?int
    {
        // Deal with declaration first
        switch ($care_path->getIndicator()) {
            case CarePathEnum::NOT_SPECIFIC_ACCESS():
            case CarePathEnum::SPECIFIC_DIRECT_ACCESS():
            case CarePathEnum::OUT_OF_RESIDENCY():
            case CarePathEnum::POOR_MEDICALIZED_ZONE():
            case CarePathEnum::RECENTLY_INSTALLED_RP():
            case CarePathEnum::ORIENTED_BY_NRP():
                $declaration = $care_path->getDeclaration();
                if ($declaration === null) {
                    throw CarePathMappingException::missingDeclaration($care_path->getIndicator());
                }

                return ($declaration) ? CarePath::DECLARATION_YES : CarePath::DECLARATION_NO;
            case CarePathEnum::RP_SUBSTITUTE():
                return CarePath::DECLARATION_YES;
            case CarePathEnum::NON_COMPLIANCE_CARE_PATH():
                $declaration = $care_path->getDeclaration();
                if ($declaration !== null) {
                    $declaration = ($declaration) ? CarePath::DECLARATION_YES : CarePath::DECLARATION_NO;
                }

                return $declaration;
            default:
                return null;
        }
    }
}
