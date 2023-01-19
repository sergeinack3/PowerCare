<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Html;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Description
 */
class HtmlPurifierAdapter implements PurifierInterface
{
    /** @var string */
    private $cache_dir;

    /** @var HTMLPurifier */
    private $purifier;

    /**
     * HtmlPurifierAdapter constructor.
     *
     * @param string|null $cache_dir
     */
    public function __construct(string $cache_dir = null)
    {
        $this->cache_dir = ($cache_dir) ?: dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'tmp';
        $this->purifier  = new HTMLPurifier();
    }

    /**
     * @param array $options
     *
     * @return HTMLPurifier_Config
     */
    private function makeConfig(array $options = []): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();

        // App encoding (in order to prevent from removing diacritics)
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.SerializerPath', $this->cache_dir);
        $config->set('CSS.Proprietary', true);
        $config->set('CSS.AllowTricky', true);

//        $def = $config->getHTMLDefinition(true);
//        $def->addAttribute('img', 'src', 'Text');

        foreach ($options as $_k => $_v) {
            $config->set($_k, $_v);
        }

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function purify(string $str): string
    {
        $purified = $this->purifier->purify($this->encodeString($str), $this->makeConfig());

        return $this->decodeString($purified);
    }

    /**
     * @inheritDoc
     */
    public function removeHtml(string $str): string
    {
        $purified = $this->purifier->purify($this->encodeString($str), $this->makeConfig(['HTML.Allowed' => '']));

        return $this->decodeString($purified);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function encodeString(string $str): string
    {
        return mb_convert_encoding($str, 'UTF-8', 'Windows-1252');
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function decodeString(string $str): string
    {
        return mb_convert_encoding($str, 'Windows-1252', 'UTF-8');
    }
}
