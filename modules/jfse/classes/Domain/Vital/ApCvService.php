<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use DateTime;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\CAppUI;
use Ox\Mediboard\Jfse\ApiClients\ApCvClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Mappers\ApCvMapper;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;
use Ox\Mediboard\Jfse\Mappers\VitalCardMapper;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\CPatientVitalCard;

class ApCvService extends AbstractService
{
    /** @var ApCvClient */
    protected $client;

    /** @var LayeredCache */
    protected $cache;

    /** @var VitalCardMapper */
    protected $vital_mapper;

    /** @var ApCvMapper */
    protected $apcv_mapper;

    public function __construct(ApCvClient $client = null, LayeredCache $cache = null)
    {
        parent::__construct($client ?? new ApCvClient());

        $this->cache = $cache ?? LayeredCache::getCache(LayeredCache::INNER_DISTR);
        $this->apcv_mapper = new ApCvMapper();
        $this->vital_mapper = new VitalCardMapper();
    }

    public function generateApCvContext(ApCvAcquisitionModeEnum $mode, string $context = null): ?VitalCard
    {
        $this->destroyApCvContext();

        $response = $this->client->generateApCvContext($mode, $context);
        $vital = $this->vital_mapper->arrayToVitalCard($response->getContent());

        /* When the CPS card is absent, we set a flag,
           because only the administrative date of the patients must be returned */
        if ($response->hasError(61441)) {
            $vital->setCpsAbsent(true);
        } else {
            $vital->setCpsAbsent(false);
        }

        $this->setContextInCache($vital->getApcvContext());
        $this->setApCvDataInCache($vital);

        if ($vital->hasBeneficiaries()) {
            VitalCardService::setBeneficiariesInCache($vital);
        }

        return $vital;
    }

    public function getApCvContext(): ?ApCvContext
    {
        $context = $this->getContextFromCache();
        if (!$context) {
            $context = $this->apcv_mapper->getApCvContextFromResponse($this->client->getApCVContext()->getContent());
        }

        return $context;
    }

    public function getPatients(): array
    {
        $patients = [];

        $vital = $this->getVitalCard();
        foreach ($vital->getBeneficiaries() as $beneficiary) {
            $patients[] = CPatientVitalCard::getFromEntity($beneficiary);
        }

        return $patients;
    }

    /**
     * Get the VitalCard object stored in cache, or from Jfse
     *
     * @return VitalCard|null
     */
    public function getVitalCard(): ?VitalCard
    {
        $vital = $this->getDataFromCache();
        if (!$vital && ApCvService::isApCvAuthorized()) {
            $response = $this->client->getApCVContext();
            $vital = $this->vital_mapper->arrayToVitalCard($response->getContent());
            $this->setContextInCache($vital->getApcvContext());
            $this->setApCvDataInCache($vital);
        }

        return $vital;
    }

    public function destroyApCvContext(): bool
    {
        $context = $this->getContextFromCache();

        if ($context) {
            $this->client->deleteApCvContext($context);
        }

        $this->deleteDataInCache();
        return $this->deleteContextInCache();
    }

    public function renewApCvContextForInvoice(
        string $invoice_id,
        ApCvAcquisitionModeEnum $mode,
        string $context = null
    ): ?ApCvContext {
        $response = $this->client->renewApCvContextForInvoice($invoice_id, $mode, $context);
        $invoice = InvoicingMapper::getInvoiceFromResponse($response);

        $context = null;
        if ($invoice->getBeneficiary() && $invoice->getBeneficiary()->getApcvContext()) {
            $context = $invoice->getBeneficiary()->getApcvContext();
            $this->setContextInCache($context);
        }

        return $context;
    }

    protected function setContextInCache(ApCvContext $context): bool
    {
        return $this->cache->set(self::getContextCacheKey(), $context, $context->getLifeExpectancy());
    }

    protected function setApCvDataInCache(VitalCard $vital): bool
    {
        return $this->cache->set(self::getApCvDataCacheKey(), $vital, $vital->getApcvContext()->getLifeExpectancy());
    }

    protected function getContextFromCache(): ?ApCvContext
    {
        $context = $this->cache->get(self::getContextCacheKey());

        return $context;
    }

    protected function getDataFromCache(): ?VitalCard
    {
        return $this->cache->get(self::getApCvDataCacheKey());
    }

    /**
     * @todo Set method to protected after the Certification
     */
    public function deleteContextInCache(): bool
    {
        return $this->cache->set(self::getContextCacheKey(), null);
    }

    /**
     * @todo Set method to protected after the Certification
     */
    public function deleteDataInCache(): bool
    {
        return $this->cache->set(self::getApCvDataCacheKey(), null);
    }

    protected static function getContextCacheKey(): string
    {
        return 'Jfse-ApCvContext-JfseUser-' . Utils::getJfseUserId();
    }

    protected static function getApCvDataCacheKey(): string
    {
        return 'Jfse-ApCvData-JfseUser-' . Utils::getJfseUserId();
    }

    public static function isApCvAuthorized(): bool
    {
        $date_apcv = CAppUI::gconf('jfse General apcv_date');

        return CAppUI::gconf('jfse General apcv') === '1'
            || ($date_apcv && new DateTime($date_apcv) <= new DateTime());
    }
}
