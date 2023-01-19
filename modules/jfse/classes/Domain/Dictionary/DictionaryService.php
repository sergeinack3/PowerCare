<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Dictionary;

use Ox\Components\Cache\LayeredCache;
use Ox\Mediboard\Jfse\ApiClients\DictionaryClient;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Mappers\DictionaryMapper;
use Ox\Mediboard\Patients\CPatient;
use Psr\SimpleCache\InvalidArgumentException;

class DictionaryService extends AbstractService
{
    protected const CACHE_KEY_REGIMES = 'Jfse-Dictionary-Regimes';
    protected const CACHE_KEY_ORGANISM = 'Jfse-Dictionary-Organisms';
    protected const CACHE_KEY_MANAGING_CODES = 'Jfse-Dictionary-ManagingCodes';
    protected const CACHE_TTL = 43200;

    /** @var DictionaryClient */
    protected $client;

    /** @var LayeredCache */
    protected $cache;

    /** @var DictionaryMapper */
    protected $mapper;

    public function __construct(
        DictionaryClient $client = null,
        LayeredCache $cache = null,
        DictionaryMapper $mapper = null
    ) {
        $this->cache = $cache ?? LayeredCache::getCache(LayeredCache::INNER_OUTER);
        $this->mapper = $mapper ?? new DictionaryMapper();

        parent::__construct($client ?? new DictionaryClient());
    }

    public function listRegimes(): array
    {
        try {
            $regimes = $this->cache->get(self::CACHE_KEY_REGIMES);
        } catch (InvalidArgumentException $e) {
            $regimes = null;
        }

        if (!$regimes) {
            $regimes = $this->mapper->getRegimesFromResponse($this->client->listRegimes());

            try {
                $this->cache->set(self::CACHE_KEY_REGIMES, $regimes, self::CACHE_TTL);
            } catch (InvalidArgumentException $e) {
            }
        }

        return $regimes;
    }

    public function listOrganisms(): array
    {
        $cache_key = self::CACHE_KEY_ORGANISM;

        try {
            $organisms = $this->cache->get($cache_key);
        } catch (InvalidArgumentException $e) {
            $organisms = null;
        }

        if (!$organisms) {
            $organisms = $this->mapper->getOrganismsFromResponse($this->client->listOrganisms());

            try {
                $this->cache->set($cache_key, $organisms, self::CACHE_TTL);
            } catch (InvalidArgumentException $e) {
            }
        }

        return $organisms;
    }

    /**
     * Filter the organisms by regime, and with an optional string for the label
     *
     * @param string      $regime_code
     * @param string|null $needle
     *
     * @return array
     */
    public function filterOrganisms(string $regime_code, string $needle = null): array
    {
        return array_filter($this->listOrganisms(), function ($organism, $key) use ($regime_code, $needle) {
            if (!array_key_exists('regime_code', $organism) || !array_key_exists('label', $organism)) {
                return false;
            }

            return $organism['regime_code'] == $regime_code
                && (!$needle || str_contains(strtolower($organism['label']), strtolower($needle)));
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Return the organism that matches with the regime, fund and center codes of the patient
     * If no organism is find by matching the center, the method will recall itself recursively,
     * and only check the regime and fund codes.
     *
     * @param CPatient $patient
     * @param bool     $check_center
     *
     * @return array|null
     */
    public function getOrganismForPatient(CPatient $patient, bool $check_center = true): ?array
    {
        $organism = null;
        if ($patient->code_regime && $patient->caisse_gest) {
            $organisms = array_filter($this->listOrganisms(), function ($organism, $key) use ($patient, $check_center) {
                if (
                    !array_key_exists('regime_code', $organism)
                    || !array_key_exists('fund_code', $organism)
                    || !array_key_exists('center_code', $organism)
                ) {
                    return false;
                }

                return $organism['regime_code'] == $patient->code_regime
                    && $organism['fund_code'] == $patient->caisse_gest
                    && (!$check_center || !$patient->centre_gest || $organism['center_code'] == $patient->centre_gest);
            }, ARRAY_FILTER_USE_BOTH);

            if (count($organisms) === 1) {
                $organism = reset($organisms);
            } elseif (count($organisms) === 0 && $check_center) {
                $organism = $this->getOrganismForPatient($patient, false);
            }
        }

        return $organism;
    }

    public function listManagingCodes(): array
    {
        try {
            $codes = $this->cache->get(self::CACHE_KEY_MANAGING_CODES);
        } catch (InvalidArgumentException $e) {
            $codes = null;
        }

        if (!$codes) {
            $codes = $this->mapper->getManagingCodesFromResponse($this->client->listManagingCodes());

            try {
                $this->cache->set(self::CACHE_KEY_MANAGING_CODES, $codes, self::CACHE_TTL);
            } catch (InvalidArgumentException $e) {
            }
        }

        return $codes;
    }
}
