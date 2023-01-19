<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\ProofAmo;

use DateTimeImmutable;
use Ox\Core\Cache;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\ProofAmoClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;
use Ox\Mediboard\Jfse\Mappers\ProofAmoTypeMapper;

/**
 * Class ProofAmoService
 *
 * @package Ox\Mediboard\Jfse\Domain\ProofAmo
 */
class ProofAmoService extends AbstractService
{
    /** @var ProofAmoClient */
    protected $client;

    /** @var Cache */
    private $proof_types_cache;

    /**
     * ProofAmoService constructor.
     *
     * @param ProofAmoClient|null $client
     * @param Cache|null          $proof_types_cache
     */
    public function __construct(ProofAmoClient $client = null, Cache $proof_types_cache = null)
    {
        $client = $client ?? new ProofAmoClient();

        parent::__construct($client);

        $this->proof_types_cache = $proof_types_cache ?? new Cache("Jfse-ProofAmo", "proofTypes", Cache::OUTER, 86400);
    }

    /**
     * Get the list of proof types using the cache or the API
     *
     * @return ProofAmoType[]
     */
    public function listProofTypes(): array
    {
        $raw_types = $this->proof_types_cache->get();

        if (!$raw_types) {
            $response  = $this->client->listProofTypes();
            $raw_types = ProofAmoTypeMapper::getArrayFromResponse($response);
            $this->proof_types_cache->put($raw_types);
        }

        $types = [];
        foreach ($raw_types as $_proof) {
            $types[] = ProofAmoType::hydrate($_proof);
        }

        return $types;
    }

    /**
     * Save a proof AMO from the raw request data
     *
     * @param string                 $invoice_id
     * @param int                    $nature
     * @param DateTimeImmutable|null $date
     * @param int|null               $origin
     *
     * @return bool
     */
    public function saveProofAmo(string $invoice_id, int $nature, ?DateTimeImmutable $date, ?int $origin): bool
    {
        $this->client->setErrorsHandler(
            function (Response $response): void {
                if ($response->hasError(ProofAmoClient::INVOICE_ERROR)) {
                    throw InvoiceException::invalidInvoice();
                }
            }
        );

        $this->client->setMessagesHandler([$this, 'handleErrorMessagesOnly']);

        $this->client->saveProofAmo($invoice_id, $nature, $date, $origin);

        return true;
    }
}
