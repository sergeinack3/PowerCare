<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Html;

use Ox\Core\Cache;

/**
 * Description
 */
class Purifier
{
    /** @var PurifierInterface */
    private $adapter;

    /**
     * Purifier constructor.
     *
     * @param PurifierInterface $adapter
     */
    public function __construct(PurifierInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Sanitize Html content (remove dangerous inputs).
     *
     * @param string $str
     *
     * @return string
     */
    public function purify(string $str): string
    {
        if (trim($str) === '') {
            return $str;
        }

        $cache    = $this->getCache('html_purify', $str);
        $purified = $cache->get();

        if ($purified !== null) {
            return $purified;
        }

        $purified = $this->adapter->purify($str);

        if (isset($purified[5])) {
            $cache->put($purified);
        }

        return $purified;
    }

    /**
     * Remove all Html content.
     *
     * @param string $str
     *
     * @return string
     */
    public function removeHtml(string $str): string
    {
        if (trim($str) === '') {
            return $str;
        }

        $cache    = $this->getCache('html_removeHtml', $str);
        $purified = $cache->get();

        if ($purified !== null) {
            return $purified;
        }

        $purified = $this->adapter->removeHtml($str);

        if (isset($purified[5])) {
            $cache->put($purified);
        }

        return $purified;
    }

    /**
     * For mockability.
     *
     * @param string $prefix
     * @param string $key
     *
     * @return Cache
     */
    protected function getCache(string $prefix, string $key): Cache
    {
        return new Cache($prefix, sha1($key), Cache::INNER);
    }
}
