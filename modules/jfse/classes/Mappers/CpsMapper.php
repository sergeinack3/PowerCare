<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Cps\Card;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;

/**
 * Class CpsMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
final class CpsMapper
{
    /**
     * Return a Card object from the json response of the API
     *
     * @param Response $response
     *
     * @return Card
     */
    public static function getCardFromReadResponse(Response $response): Card
    {
        $response = $response->getContent();
        $data     = [
            'type_code'                          => intval(CMbArray::get($response, 'typeCarte')),
            'type_label'                         => CMbArray::get($response, 'libelleTypeCarte'),
            'national_identification_type_code'  => intval(CMbArray::get($response, 'typeIdentificationNational')),
            'national_identification_type_label' => CMbArray::get($response, 'libelleTypeIdentificationNational'),
            'national_identification_number'     => CMbArray::get($response, 'numIdentificationNational'),
            'national_identification_key'        => CMbArray::get($response, 'cleIdentificationNational'),
            'civility_code'                      => intval(CMbArray::get($response, 'codeCivilite')),
            'civility_label'                     => CMbArray::get($response, 'libelleCodeCivilite'),
            'last_name'                          => CMbArray::get($response, 'nom'),
            'first_name'                         => CMbArray::get($response, 'prenom'),
            'situations'                         => [],
        ];

        $situations = CMbArray::get($response, 'lstCpsSituation', []);
        foreach ($situations as $situation_data) {
            $situation = self::getSituationFromResponse($situation_data);
            $data['situations'][$situation->getSituationId()] = $situation;
        }

        return Card::hydrate($data);
    }

    /**
     * Returns the data from the given response to an array from which a Situation can be hydrated
     *
     * @param array $response
     *
     * @return array
     */
    public static function getSituationFromResponse(array $response): Situation
    {
        $data = [
            'practitioner_id'           => intval(CMbArray::get($response, 'idJfse')),
            'situation_id'              => intval(CMbArray::get($response, 'identifiantLogique')),
            'structure_identifier_type' => intval(CMbArray::get($response, 'typeIdentificationStructure')),
            'structure_identifier'      => CMbArray::get($response, 'numIdentificationStructure')
                ?? CMbArray::get($response, 'noIdentificationStructure'),
            'structure_name'            => CMbArray::get($response, 'raisonSociale'),
            'invoicing_number'          => CMbArray::get($response, 'numIdentificationPSFacturation')
                ?? CMbArray::get($response, 'noPSFacturation'),
            'invoicing_number_key'      => CMbArray::get($response, 'cleIdentificationPSFacturation'),
            'substitute_number'         => CMbArray::get($response, 'numIdentificationPSRemplacant')
        ?? CMbArray::get($response, 'noPSRemplacant'),
            'convention_code'           => intval(CMbArray::get($response, 'codeConventionnel')),
            'convention_label'          => CMbArray::get($response, 'libelleCodeConventionnel'),
            'speciality_code'           => CMbArray::get($response, 'codeSpecialite'),
            'speciality_label'          => CMbArray::get($response, 'libelleCodeSpecialite')
                ?? CMbArray::get($response, 'libelleSpecialite'),
            'speciality_group'          => CMbArray::get($response, 'familleSpecialite'),
            'price_zone_code'           => CMbArray::get($response, 'codeZoneTarif')
                ?? CMbArray::get($response, 'codeZoneTarifaire'),
            'price_zone_label'          => CMbArray::get($response, 'libelleCodeZoneTarif'),
            'distance_allowance_code'   => CMbArray::get($response, 'codeZoneIK'),
            'distance_allowance_label'  => CMbArray::get($response, 'libelleCodeZoneIK'),
            'approval_codes'            => [],
            'approval_labels'           => [],
            'fse_signing_authorisation' => boolval(
                CMbArray::get($response, 'habilitationSignerFSE') ?? CMbArray::get($response, 'habilitationFSE')
            ),
            'lot_signing_authorisation' => boolval(
                CMbArray::get($response, 'habilitationSignerLOT') ?? CMbArray::get($response, 'habilitationLots')
            ),
            'practice_mode'             => intval(CMbArray::get($response, 'modeExercice')),
            'practice_status'           => intval(
                CMbArray::get($response, 'statutExercice') ?? CMbArray::get($response, 'statusExercice')
            ),
            'activity_sector'           => CMbArray::get($response, 'secteurActivite'),
        ];

        for ($i = 1; $i <= 3; $i++) {
            $code  = intval(CMbArray::get($response, "codeAgrement{$i}"));
            $label = CMbArray::get($response, "libelleCodeAgrement{$i}");
            if (isset($code) && isset($label)) {
                $data['approval_codes'][$i]  = $code;
                $data['approval_labels'][$i] = $label;
            }
        }

        return Situation::hydrate($data);
    }

    /**
     * @param Card $card
     *
     * @return array
     */
    public static function getArrayFromCard(Card $card): array
    {
        $data = [
            'typeCarte'                         => $card->getTypeCode(),
            'libelleTypeCarte'                  => $card->getTypeLabel(),
            'typeIdentificationNational'        => $card->getNationalIdentificationTypeCode(),
            'libelleTypeIdentificationNational' => $card->getNationalIdentificationTypeLabel(),
            'numIdentificationNational'         => $card->getNationalIdentificationNumber(),
            'cleIdentificationNational'         => $card->getNationalIdentificationKey(),
            'codeCivilite'                      => $card->getCivilityCode(),
            'libelleCodeCivilite'               => $card->getCivilityLabel(),
            'nom'                               => $card->getLastName(),
            'prenom'                            => $card->getFirstName(),
            'lstCpsSituation'                   => [],
        ];

        $situations = $card->getSituations();
        foreach ($situations as $situation) {
            $situation_data = [
                'identifiantLogique'             => $situation->getSituationId(),
                'typeIdentificationStructure'    => $situation->getStructureIdentifierType(),
                'numIdentificationStructure'     => $situation->getStructureIdentifier(),
                'raisonSociale'                  => $situation->getStructureName(),
                'numIdentificationPSFacturation' => $situation->getInvoicingNumber(),
                'cleIdentificationPSFacturation' => $situation->getInvoicingNumberKey(),
                'numIdentificationPSRemplacant'  => $situation->getSubstituteNumber(),
                'codeConventionnel'              => $situation->getConventionCode(),
                'libelleCodeConventionnel'       => $situation->getConventionLabel(),
                'codeSpecialite'                 => str_pad($situation->getSpecialityCode(), 2, '0', STR_PAD_LEFT),
                'libelleCodeSpecialite'          => $situation->getSpecialityLabel(),
                'familleSpecialite'              => $situation->getSpecialityGroup(),
                'codeZoneTarif'                  => $situation->getPriceZoneCode(),
                'libelleCodeZoneTarif'           => $situation->getPriceZoneLabel(),
                'codeZoneIK'                     => str_pad(
                    $situation->getDistanceAllowanceCode(),
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                'libelleCodeZoneIK'              => $situation->getDistanceAllowanceLabel(),
                'codeAgrement1'                  => '',
                'libelleCodeAgrement1'           => '',
                'codeAgrement2'                  => '',
                'libelleCodeAgrement2'           => '',
                'codeAgrement3'                  => '',
                'libelleCodeAgrement3'           => '',
                'habilitationSignerFSE'          => (int)$situation->getFseSigningAuthorisation(),
                'habilitationSignerLOT'          => (int)$situation->getLotSigningAuthorisation(),
                'modeExercice'                   => $situation->getPracticeMode(),
                'statusExercice'                 => $situation->getPracticeStatus(),
                'secteurActivite'                => $situation->getActivitySector(),
            ];

            $codes = $situation->getApprovalCodes();
            foreach ($codes as $index => $code) {
                $situation_data["codeAgrement{$index}"] = $code;
            }

            $labels = $situation->getApprovalLabels();
            foreach ($labels as $index => $label) {
                $situation_data["libelleCodeAgrement{$index}"] = $label;
            }

            $data['lstCpsSituation'][] = $situation_data;
        }

        if (count($data['lstCpsSituation']) === 1) {
            $data['lstCpsSituation'] = $data['lstCpsSituation'][0];
        }

        return $data;
    }
}
