<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Wsdl;

use Countable;
use Ox\Components\Yampee\Redis\Exception\Error as ErrorException;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CMbException;
use Ox\Core\CWSDL;
use Ox\Core\Redis\CRedisClient;
use Throwable;

/**
 * Redis implementation of WSDLRepositoryInterface
 */
class RedisWSDLRepository implements WSDLRepositoryInterface, IShortNameAutoloadable, Countable
{
    use WSDLNameGeneratorTrait;

    /** @var string */
    private $prefix;

    /** @var CRedisClient */
    private $client;

    /**
     * @param string       $prefix
     * @param CRedisClient $client
     *
     * @throws CMbException
     */
    public function __construct(string $prefix, CRedisClient $client)
    {
        if (!$client->isConnected()) {
            throw new CMbException("RedisWSDLRepository-error-Redis client is not connected");
        }

        $root_dir     = CApp::getAppIdentifier();
        $this->prefix = $root_dir . "_" . $prefix;
        $this->client = $client;
    }

    /**
     * Generate a WSDL Redis key
     *
     * @param string $wsdl_name WSDL name
     *
     * @return string
     */
    private function generateWSDLKey(string $wsdl_name): string
    {
        return "{$this->prefix}_{$wsdl_name}";
    }

    /**
     * @inheritDoc
     */
    public function find(
        ?string $login,
        ?string $token,
        string  $module,
        string  $tab,
        string  $classname,
        string  $wsdl_mode
    ): ?CWSDL {
        $wsdl_name = static::generateWSDLName($login, $token, $module, $tab, $classname);
        $wsdl_key  = $this->generateWSDLKey($wsdl_name);

        try {
            $wsdl_content = $this->client->get($wsdl_key);
        } catch (ErrorException $exception) {
            return null;
        }

        if (!$wsdl_content) {
            return null;
        }

        $wsdl = WSDLFactory::createFromString($wsdl_mode, $classname, $wsdl_name, $wsdl_content);

        if ($wsdl->loadXML($wsdl_content) === false) {
            throw new CMbException('FileWSDLRepository-error-Unable to load WSDL XML content');
        }

        return $wsdl;
    }

    /**
     * @inheritDoc
     */
    public function save(CWSDL $wsdl)
    {
        $wsdl_key = $this->generateWSDLKey($wsdl->getName());
        $xml      = $wsdl->saveXML();

        if ($xml === false) {
            return false;
        }

        try {
            $this->client->set($wsdl_key, $xml, 600);
        } catch (Throwable $t) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete(CWSDL $wsdl)
    {
        $wsdl_key = $this->generateWSDLKey($wsdl->getName());

        try {
            $this->client->remove($wsdl_key);
        } catch (Throwable $t) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        // If no prefix, will flush all the DB...
        if ($this->prefix === null || $this->prefix === '') {
            return 0;
        }

        // Todo: Use CRedisClient
        return (int)Cache::deleteKeys(Cache::DISTR, $this->prefix);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        // Todo: To reimplement
        return 0;
    }
}
