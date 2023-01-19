<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

use DateTime;
use Exception;
use Ox\Core\Cache;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\InsuranceTypeClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Invoicing\CommonLawAccident;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Exceptions\Insurance\InsuranceException;
use Ox\Mediboard\Jfse\Mappers\InsuranceTypeMapper;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;

/**
 * Class InsuranceTypeService
 */
class InsuranceTypeService extends AbstractService
{
    /** @var InsuranceTypeClient */
    protected $client;

    /** @var Cache */
    private $types_cache;

    /** @var InvoicingService */
    private $invoicing_service;

    /** @var string The value of the field source_library of the messages that concerns the insurance type */
    protected const ERROR_SOURCE = 'NATURE ASSURANCE';

    /**
     * InsuranceTypeService constructor.
     *
     * @param ?InsuranceTypeClient $client
     * @param Cache|null           $types_cache
     * @param ?InvoicingService    $invoice_service
     */
    public function __construct(
        InsuranceTypeClient $client = null,
        Cache $types_cache = null,
        InvoicingService $invoice_service = null
    ) {
        parent::__construct($client ?? new InsuranceTypeClient());

        if (!$invoice_service) {
            $invoice_service = new InvoicingService();
        }
        $this->invoicing_service = $invoice_service;

        $this->types_cache = $types_cache ?? new Cache('Jfse', 'InsuranceTypes', Cache::INNER_OUTER, 7200);
    }

    /**
     * @param array $content
     *
     * @return bool
     * @throws Exception
     */
    public function save(array $content): bool
    {
        if (!isset($content['nature_type'])) {
            throw new Exception('Wrong type');
        }
        $this->client->setErrorsHandler(
            function (Response $response): void {
                if (in_array(InsuranceTypeClient::NO_INVOICE_ERROR, $response->getErrorCodes())) {
                    throw InsuranceException::invoiceNotInitialized();
                }
            }
        );

        $this->client->setMessagesHandler(
            [$this, 'handleErrorAndWarningMessagesForSourcesLibraryOnly'],
            [self::ERROR_SOURCE]
        );

        $insurance = $this->makeInsurance($content['nature_type'], $content);

        $invoice = InvoicingMapper::getInvoiceFromResponse($this->client->save($insurance, $content['invoice_id']));

        if ($insurance instanceof MedicalInsurance && $insurance->getCodeExonerationDisease() === 4) {
            $invoice->setLongLastingAffliction(true);
        } else {
            $invoice->setLongLastingAffliction(false);
        }

        if ($insurance instanceof FmfInsurance) {
            $invoice->setFreeMedicalCare(true);
        } else {
            $invoice->setFreeMedicalCare(false);
        }

        return true;
    }

    /**
     * @param int   $type
     * @param array $content
     *
     * @return AbstractInsurance
     * @throws Exception
     */
    private function makeInsurance(int $type, array $content): AbstractInsurance
    {
        $content["insurance_type"] = $this->getInsuranceTypeByCode($type);

        switch ($type) {
            case MedicalInsurance::CODE:
                $insurance_type = MedicalInsurance::hydrate($content);
                break;
            case WorkAccidentInsurance::CODE:
                $insurance_type = WorkAccidentInsurance::hydrate($content);
                break;
            case FmfInsurance::CODE:
                $insurance_type = FmfInsurance::hydrate($content);
                break;
            case MaternityInsurance::CODE:
                $insurance_type = MaternityInsurance::hydrate($content);
                break;
            default:
                throw new Exception("Unknown insurance code");
        }

        return $insurance_type;
    }

    /**
     * @param int $code
     *
     * @return InsuranceType|null
     */
    public function getInsuranceTypeByCode(int $code): ?InsuranceType
    {
        foreach ($this->getAllInsuranceTypes() as $_type) {
            if ($_type->getCode() === $code) {
                return $_type;
            }
        }

        return null;
    }

    /**
     * @return InsuranceType[]
     */
    public function getAllInsuranceTypes(): array
    {
        $raw_types = $this->types_cache->get();

        if (!$raw_types) {
            $response  = $this->client->getAllTypes();
            $raw_types = InsuranceTypeMapper::getTypesFromResponse($response);
            $this->types_cache->put($raw_types);
        }

        return array_map(
            function (array $type): InsuranceType {
                return InsuranceType::hydrate($type);
            },
            $raw_types
        );
    }
}
