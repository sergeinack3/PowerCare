<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests;

use Ox\Tests\JsonApi\Collection;
use Ox\Tests\JsonApi\Error;
use Ox\Tests\JsonApi\Item;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OxWebTestCase extends WebTestCase
{
    use OxTestTrait;
    use OxAssertionsTrait;

    /**
     * Get response content
     *
     * @param KernelBrowser $client
     * @param bool          $decoded
     *
     * @return array
     * @throws TestsException
     */
    protected function getResponseContent(KernelBrowser $client, bool $decoded = true): array
    {
        $content = $client->getResponse()->getContent();
        if ($decoded) {
            $content = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new TestsException(json_last_error_msg());
            }
        }

        return mb_convert_encoding($content, 'Windows-1252', 'UTF-8');
    }

    /***
     * Get response content and return JSON:API item
     *
     * @param KernelBrowser $client
     *
     * @return Item
     * @throws TestsException
     */
    protected function getJsonApiItem(KernelBrowser $client): Item
    {
        return Item::createFromArray($this->getResponseContent($client));
    }

    /**
     * Get response content and return JSON:API collection
     *
     * @param KernelBrowser $client
     *
     * @return Collection
     * @throws TestsException
     */
    protected function getJsonApiCollection(KernelBrowser $client): Collection
    {
        return Collection::createFromArray($this->getResponseContent($client));
    }

    /**
     * Get response content and return JSON:API error
     *
     * @param KernelBrowser $client
     *
     * @return Error
     * @throws TestsException
     */
    protected function getJsonApiError(KernelBrowser $client): Error
    {
        return Error::createFromArray($this->getResponseContent($client));
    }

    protected static function createClient(
        array $options = [],
        array $server = [],
        bool $force_auth = true,
        string $ox_token = null
    ) {
        $server['OX_REQUEST_NO_LOG'] = true;
        $server['CONTENT_TYPE']      = 'application/vnd.api+json';

        if ($ox_token) {
            $server['HTTP_X-OXAPI-KEY'] = $ox_token;
        } elseif ($force_auth) {
            if (!$b64 = $_ENV['OX_PHPUNIT_BASIC']) {
                throw new TestsException('Missing OX_PHPUNIT_BASIC key in .env');
            }
            $server['HTTP_Authorization'] = 'Basic ' . $b64;
        }

        return parent::createClient($options, $server);
    }
}
