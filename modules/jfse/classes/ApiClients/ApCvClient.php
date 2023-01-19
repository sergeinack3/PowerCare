<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvAcquisitionModeEnum;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvContext;

class ApCvClient extends AbstractApiClient
{
    /**
     * Get the ApCV context, either by using a NFC reader handled by JFSE, or by using the context read from a QRCode
     * or NFC reader handled by the client application
     *
     * @param ApCvAcquisitionModeEnum $mode
     * @param string|null             $context
     *
     * @return Response
     */
    public function generateApCvContext(ApCvAcquisitionModeEnum $mode, string $context = null): Response
    {
        $parameters = [
            'mode' => $mode->getValue()
        ];

        if ($mode->getValue() === ApCvAcquisitionModeEnum::QRCODE()->getValue()) {
            $parameters['contexteApCV'] = $context;
        }

        return self::sendRequest(
            Request::forge('TEL-getBeneficiaireViaApCV', ['getBeneficiaireViaApCV' => $parameters]),
            60
        );
    }

    /**
     * Deletes the currently stored ApCV context
     *
     * @param ApCvContext $context
     *
     * @return Response
     */
    public function deleteApCvContext(ApCvContext $context): Response
    {
        return self::sendRequest(Request::forge('TEL-deleteContexteApCV', [
            'deleteContexteApCV' => [
                'identifiantApCV' => $context->getIdentifier()
            ]
        ]));
    }

    /**
     * This method restitutes the currently stored ApCV context in JFSE
     *
     * @return Response
     */
    public function getApCVContext(): Response
    {
        return self::sendRequest(Request::forge('TEL-restitutionContexteApCV', []));
    }

    public function renewApCvContextForInvoice(
        string $invoice_id,
        ApCvAcquisitionModeEnum $mode,
        string $context = null
    ): Response {
        $parameters = [
            'mode' => $mode->getValue()
        ];

        if ($mode->getValue() === ApCvAcquisitionModeEnum::QRCODE()->getValue()) {
            $parameters['contexteApCV'] = $context;
        }

        return self::sendRequest(
            Request::forge('FDS-renouvellementContexteApCV', [
                'idFacture' => $invoice_id,
                'renouvellementContexteApCV' => $parameters
            ]),
            60
        );
    }
}
