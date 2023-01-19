<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices\Wsdl;

use Countable;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CWSDL;
use Ox\Mediboard\System\CRedisServer;

/**
 * WSDL repository
 */
class WSDLRepository implements IShortNameAutoloadable, Countable
{
    /** @var WSDLRepositoryInterface */
    private $repository;

    /**
     * WSDLRepository constructor.
     *
     * @param WSDLRepositoryInterface|null $repository
     * @param string|null                  $prefix
     *
     * @throws CMbException
     */
    public function __construct(WSDLRepositoryInterface $repository = null, ?string $prefix = "soap_server")
    {
        if ($repository === null) {
            $repository = $this->generate($prefix);
        }

        $this->repository = $repository;
    }

    /**
     * Generate Repository
     *
     * @param string|null $prefix
     *
     * @return WSDLRepositoryInterface
     * @throws CMbException
     */
    private function generate(string $prefix): WSDLRepositoryInterface
    {
        switch (CAppUI::conf('session_handler')) {
            case 'redis':
                return new RedisWSDLRepository($prefix, CRedisServer::getClient());

            case 'files':
            default:
                return new FileWSDLRepository($prefix);
        }
    }

    /**
     * Find a WSDL on a persistence layer
     *
     * @param string|null $login     Login
     * @param string|null $token     Token
     * @param string      $module    Module name
     * @param string      $tab       Tab name
     * @param string      $classname Class name
     * @param string      $wsdl_mode The WSDL mode (CWSDLRPCEncoded or CWSDLRPCLiteral)
     *
     * @return CWSDL|null
     * @throws CMbException
     */
    public function find(
        ?string $login,
        ?string $token,
        string  $module,
        string  $tab,
        string  $classname,
        string  $wsdl_mode
    ) {
        return $this->repository->find($login, $token, $module, $tab, $classname, $wsdl_mode);
    }

    /**
     * Save a WSDL to a persistence layer
     *
     * @param CWSDL $wsdl
     *
     * @return bool
     * @throws CMbException
     */
    public function save(CWSDL $wsdl)
    {
        return $this->repository->save($wsdl);
    }

    /**
     * Delete a WSDL from a persistence layer
     *
     * @param CWSDL $wsdl
     *
     * @return bool
     * @throws CMbException
     */
    public function delete(CWSDL $wsdl)
    {
        return $this->repository->delete($wsdl);
    }

    /**
     * FLush all the WSDL on a persistence layer
     *
     * @return int
     */
    public function flush()
    {
        return $this->repository->flush();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->repository->count();
    }
}
