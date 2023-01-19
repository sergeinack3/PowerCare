<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTime;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Cps\Situation;
use Ox\Mediboard\Jfse\Domain\UserManagement\User;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserConfiguration;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserParameter;

/**
 * Class UserMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
final class UserMapper extends AbstractMapper
{
    /**
     * @param Response $response
     *
     * @return User[]
     */
    public static function getUsersFromListResponse(Response $response): array
    {
        $response = $response->getContent();
        $users    = [];

        $users_list = [];
        if (array_key_exists('lstInfoPS', $response)) {
            $users_list = CMbArray::get($response, 'lstInfoPS', []);
        } elseif (array_key_exists('lst', $response)) {
            $users_list = CMbArray::get($response, 'lst', []);
        }

        foreach ($users_list as $user) {
            $users[] = self::getUserFromData($user);
        }

        return $users;
    }

    /**
     * @param Response $response
     *
     * @return User
     */
    public static function getUserFromGetInformationResponse(Response $response): User
    {
        return self::getUserFromData($response->getContent());
    }

    /**
     * @param array $response
     *
     * @return User
     */
    public static function getUserFromData(array $response): User
    {
        $data = [
            'id'                                       =>
                CMbArray::get($response, 'idJfse') ?? CMbArray::get($response, 'id'),
            'establishment_id'                         => intval(CMbArray::get($response, 'idEtablissement')),
            'login'                                    => CMbArray::get($response, 'login'),
            'password'                                 => CMbArray::get($response, 'password'),
            'national_identification_type_code'        => intval(
                CMbArray::get($response, 'typeIdentification')
            ),
            'national_identification_number'           => CMbArray::get($response, 'numIdentificationNational')
                ?? CMbArray::get($response, 'noNational'),
            'civility_code'                            => intval(CMbArray::get($response, 'codeCivilite')),
            'civility_label'                           => CMbArray::get($response, 'libelleCodeCivilite'),
            'last_name'                                => CMbArray::get($response, 'nom')
                ?? CMbArray::get($response, 'nomPS'),
            'first_name'                               => CMbArray::get($response, 'prenom')
                ?? CMbArray::get($response, 'prenomPS'),
            'address'                                  => '',
            'installation_date'                        => null,
            'installation_zone_under_medicalized_date' => null,
            'ccam_activation'                          => boolval(CMbArray::get($response, 'activationCCAM')),
            'health_insurance_agency'                  => CMbArray::get($response, 'caisseExecutant'),
            'health_center'                            => boolval(CMbArray::get($response, 'modeCS')),
            'cnda_mode'                                => boolval(CMbArray::get($response, 'modeCNDA')),
            'cardless_mode'                            => boolval(CMbArray::get($response, 'modeSansCPS')),
            'care_path'                                => intval(CMbArray::get($response, 'parcoursSoins')),
            'card_type'                                => intval(CMbArray::get($response, 'typeCartePS')),
            'last_fse_number'                          => intval(CMbArray::get($response, 'dernierNumeroFacture')),
            'formatting'                               => boolval(CMbArray::get($response, 'formatageOK')),
            'substitute_number'                        => CMbArray::get($response, 'noPSRemplacant'),
            'substitute_last_name'                     => CMbArray::get($response, 'nomPSRemplacant'),
            'substitute_first_name'                    => CMbArray::get($response, 'prenomPSRemplacant'),
            'substitute_situation_number'              => intval(CMbArray::get($response, 'noSituationPSRemplacant')),
            'substitute_rpps_number'                   => CMbArray::get($response, 'noRPPSPSRemplacant'),
            'substitution_session'                     => intval(CMbArray::get($response, 'sessionRemplacement')),
            'invoicing_number'                         => CMbArray::get($response, 'noFacturation'),
            'parameters'                               => [],
            'situation'                                => null,
        ];

        if (CMbArray::get($response, 'dateInstallation', null)) {
            $data['installation_date'] = DateTime::createFromFormat(
                'Ymd',
                CMbArray::get($response, 'dateInstallation')
            );
        }

        if (CMbArray::get($response, 'dateInstallationZoneSousMedicalisee', null)) {
            $data['installation_zone_under_medicalized_date'] = DateTime::createFromFormat(
                'Ymd',
                CMbArray::get($response, 'dateInstallationZoneSousMedicalisee')
            );
        }

        $address_lines = [];
        for ($i = 1; $i <= 4; $i++) {
            if ($address_line = CMbArray::get($response, "adresse{$i}")) {
                $address_lines[] = $address_line;
            }
        }

        $data['address'] = implode(' ', $address_lines);

        if (array_key_exists('situation', $response)) {
            $situation = CMbArray::get($response, 'situation', []);
        } elseif (array_key_exists('lstCpsSituation', $response)) {
            $situations = CMbArray::get($response, 'lstCpsSituation', []);
            if (!empty($situations) && !array_key_exists('idJfse', $situations)) {
                $situation = $situations[0];
            } elseif (array_key_exists('idJfse', $situations)) {
                $situation = $situations;
            }
        }

        if (!empty($situation)) {
            $data['situation'] = CpsMapper::getSituationFromResponse($situation);
        }

        $data['parameters'] = self::getParametersFromData(CMbArray::get($response, 'lstParamPS', []));

        if (array_key_exists('remplacant', $response)) {
            $data['substitute_number']      = CMbArray::get($response['remplacant'], 'numFact');
            $data['substitute_last_name']   = CMbArray::get($response['remplacant'], 'nom');
            $data['substitute_first_name']  = CMbArray::get($response['remplacant'], 'prenom');
            $data['substitute_rpps_number'] = CMbArray::get($response['remplacant'], 'numRpps');
            $data['substitution_session']   = (int)CMbArray::get($response['remplacant'], 'session');
        }

        return User::hydrate($data);
    }

    /**
     * @param array $data
     *
     * @return UserParameter[]
     */
    protected static function getParametersFromData(array $data): array
    {
        $parameters = [];
        foreach ($data as $parameter) {
            $id = intval(CMbArray::get($parameter, 'id'));
            $parameters[$id] = UserParameter::hydrate([
                'id'    => $id,
                'name'  => CMbArray::get($parameter, 'name'),
                'value' => CMbArray::get($parameter, 'value'),
            ]);
        }

        ksort($parameters);

        return $parameters;
    }

    /**
     * @param Response $response
     *
     * @return UserConfiguration
     */
    public static function getUserConfigFromGetParametersResponse(Response $response): UserConfiguration
    {
        $response = $response->getContent();

        $user = CMbArray::get($response, 'infoPs', ['idJfse' => null, 'nom' => null, 'prenom' => null]);

        $data = [
            'user' => User::hydrate([
                'id'         => CMbArray::get($user, 'identifiant'),
                'last_name'  => CMbArray::get($user, 'nomMedecin'),
                'first_name' => CMbArray::get($user, 'prenomMedecin'),
            ]),
            'conventions_folder_path' => CMbArray::get($response, 'repertoireConvention'),
            'cerfas_list' => [],
        ];

        $cerfas = CMbArray::get($response, 'lstCerfas', []);
        foreach ($cerfas as $cerfa) {
            $data['cerfas_list'][] = $cerfa;
        }

        $data['parameters'] = self::getParametersFromData(CMbArray::get($response, 'lstParam', []));

        return UserConfiguration::hydrate($data);
    }
}
