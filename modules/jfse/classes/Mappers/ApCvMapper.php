<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvContext;

class ApCvMapper extends AbstractMapper
{
    public function getApCvContextFromResponse(array $response): ApCvContext
    {
        return ApCvContext::hydrate([
            'identifier' => CMbArray::get($response, 'identifiant'),
            'token' => CMbArray::get($response, 'token'),
            'expiration_date' => CMbArray::get($response, 'dateFinValidite') ?
                new DateTimeImmutable(CMbArray::get($response, 'dateFinValidite')) : null,
        ]);
    }

    public function arrayFromApCvContext(ApCvContext $context): array
    {
        return [
            'identifiant' => $context->getIdentifier(),
            'token' => $context->getToken(),
            'dateFinValidite' => $context->getExpirationDate() ? $context->getExpirationDate()->format('YmdHis') : '',
        ];
    }

    public function getFromJson(string $json): ?ApCvContext
    {
        $context = null;
        $data = json_decode($json, true);
        if (is_array($data)) {
            $context = ApCvContext::hydrate([
                'identifier' => CMbArray::get($data, 'identifier'),
                'token' => CMbArray::get($data, 'token'),
                'expiration_date' => CMbArray::get($data, 'expiration_date') ?
                    new DateTimeImmutable(CMbArray::get($data, 'expiration_date')) : null,
            ]);
        }

        return $context;
    }
}
