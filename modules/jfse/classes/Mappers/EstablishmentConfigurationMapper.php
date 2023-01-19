<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\UserManagement\EstablishmentConfiguration;

class EstablishmentConfigurationMapper extends AbstractMapper
{
    /**
     * @param Response $response
     *
     * @return EstablishmentConfiguration
     */
    public static function getEntityFromResponse(Response $response): EstablishmentConfiguration
    {
        $data = CMbArray::get($response->getContent(), 'parametres', []);

        $invoice_number = CMbArray::get($data, 'noFacture');
        $invoice_set_number = CMbArray::get($data, 'noLotFse');
        $refund_demand_number = CMbArray::get($data, 'noLotDre');
        $maximum_invoice_set_number = CMbArray::get($data, 'maxNoLotFse');
        $maximum_refund_demand_number = CMbArray::get($data, 'maxNoLotDre');
        $desired_invoice_number = CMbArray::get($data, 'noFactureSouhaite');
        $file_number = CMbArray::get($data, 'noFichier');
        $invoice_number_range_start = CMbArray::get($data, 'plageDebutNoFSE');
        $invoice_number_range_end = CMbArray::get($data, 'plageFinNoFSE');

        return EstablishmentConfiguration::hydrate(
            [
                'invoice_number'                  => $invoice_number ? intval($invoice_number) : null,
                'invoice_set_number'              => $invoice_set_number ? intval($invoice_set_number) : null,
                'refund_demand_number'            => $refund_demand_number ? intval($refund_demand_number) : null,
                'maximum_invoice_set_number'      => $maximum_invoice_set_number ?
                    intval($maximum_invoice_set_number) : null,
                'maximum_refund_demand_number'    => $maximum_refund_demand_number ?
                    intval($maximum_refund_demand_number) : null,
                'desired_invoice_number'          => $desired_invoice_number ? intval($desired_invoice_number) : null,
                'file_number'                     => $file_number ? intval($file_number) : null,
                'invoice_number_range_activation' => boolval(CMbArray::get($data, 'activationPlage')),
                'invoice_number_range_start'      => $invoice_number_range_start ?
                    intval($invoice_number_range_start) : null,
                'invoice_number_range_end'        => $invoice_number_range_end ?
                    intval($invoice_number_range_end) : null,
            ]
        );
    }

    public static function getApiDataFromEntity(int $establishment_id, EstablishmentConfiguration $configuration): array
    {
        $data = [
            'idEtablissement' => $establishment_id
        ];

        self::addOptionalValue('noFacture', $configuration->getInvoiceNumber(), $data);
        self::addOptionalValue('noLotFse', $configuration->getInvoiceSetNumber(), $data);
        self::addOptionalValue('noLotDre', $configuration->getRefundDemandNumber(), $data);
        self::addOptionalValue('maxNoLotFse', $configuration->getMaximumInvoiceSetNumber(), $data);
        self::addOptionalValue('maxNoLotDre', $configuration->getMaximumRefundDemandNumber(), $data);
        self::addOptionalValue('noFactureSouhaite', $configuration->getDesiredInvoiceNumber(), $data);
        self::addOptionalValue('noFichier', $configuration->getFileNumber(), $data);
        $data['activationPlage'] = intval($configuration->getInvoiceNumberRangeActivation());
        self::addOptionalValue('plageDebutNoFSE', $configuration->getInvoiceNumberRangeStart(), $data);
        self::addOptionalValue('plageFinNoFSE', $configuration->getInvoiceNumberRangeEnd(), $data);

        return $data;
    }
}
