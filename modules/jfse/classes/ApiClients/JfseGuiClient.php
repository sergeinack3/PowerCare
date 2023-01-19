<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

/**
 * Class JfseGuiClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
final class JfseGuiClient extends AbstractApiClient
{
    public function manageUsers(): Response
    {
        return self::sendRequest(
            Request::forge(
                'IDE-gui-gestion',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function manageEstablishments(): Response
    {
        return self::sendRequest(
            Request::forge(
                'IDE-gui-etablissements',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function settings(): Response
    {
        return self::sendRequest(
            Request::forge(
                'IDE-gui-parametrage-admin',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function manageFormula(): Response
    {
        return self::sendRequest(Request::forge('FORM-gui-gestion', ['redirect' => false], false));
    }

    public function userSettings(bool $display_substitutes = true): Response
    {
        $data = [
            'redirect' => false,
        ];

        if (!$display_substitutes) {
            $data['parameters'] = [
                'data' => [
                    'remplacants' => false
                ]
            ];
        }

        return self::sendRequest(Request::forge('IDE-gui-parametrage', $data, false));
    }

    public function viewInvoice(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-gui',
                [
                    'idFacture' => $invoice_id,
                    'redirect'  => false,
                ],
                false
            ),
            30
        );
    }

    public function invoiceDashboard(): Response
    {
        return self::sendRequest(
            Request::forge(
                'TAB-gui-fse',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function scorDashboard(): Response
    {
        return self::sendRequest(
            Request::forge(
                'TAB-gui-scor',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function globalTeletransmission(): Response
    {
        return self::sendRequest(
            Request::forge(
                'XMD-gui-teletransmission',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function manageNoemieReturns(): Response
    {
        return self::sendRequest(Request::forge('NOE-gui-gestion', [
            'activeRspNr' => true,
            'redirect'    => false,
        ], false));
    }

    public function manageTLA(): Response
    {
        return self::sendRequest(
            Request::forge(
                'TLA-gui-gestion',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function moduleVersion(): Response
    {
        return self::sendRequest(
            Request::forge(
                'CFG-gui-version/jfse',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function apiVersion(): Response
    {
        return self::sendRequest(
            Request::forge(
                'CFG-gui-version/api',
                [
                    'redirect' => false,
                ],
                false
            )
        );
    }

    public function selectCpsSituation(array $cps_data): Response
    {
        return self::sendRequest(Request::forge('LPS-gui-choix/situation', [
            'cps' => $cps_data,
            'redirect' => false
        ], false)->setForceObject(false));
    }

    public function selectVitalBeneficiary(array $vital_data): Response
    {
        return self::sendRequest(Request::forge('DVF-gui-choix/beneficiaire', [
            'cv' => $vital_data,
            'redirect' => false
        ], false)->setForceObject(false));
    }
}
