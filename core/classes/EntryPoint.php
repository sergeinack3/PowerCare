<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Symfony\Component\Routing\Router;

/**
 * Entry point for vue scripts.
 */
class EntryPoint
{
    private string    $entry_id;
    private ?string   $script_name = null;

    protected ?Router $router  = null;

    protected array $data    = [];
    protected array $meta    = [];
    protected array $prefs   = [];
    protected array $configs = [];
    protected array $links   = [];
    protected array $locales = [];

    public function __construct(string $entry_id, ?Router $router = null)
    {
        $this->entry_id = $entry_id;
        $this->router   = $router;
    }

    public function getId(): string
    {
        return $this->entry_id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param mixed $data
     */
    public function addData(string $name, $data): self
    {
        $this->data[$name] = $data;

        return $this;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param mixed $meta
     */
    public function addMeta(string $name, $meta): self
    {
        $this->meta[$name] = $meta;

        return $this;
    }

    public function getPrefs(): array
    {
        return $this->prefs;
    }

    public function setPrefs(array $prefs): self
    {
        $this->prefs = [];
        foreach ($prefs as $name => $pref) {
            $this->addPref($name, $pref);
        }

        return $this;
    }

    /**
     * @param mixed $pref
     */
    public function addPrefValue(string $name, $pref): self
    {
        $this->prefs[$name] = $pref;

        return $this;
    }

    public function addPref(string $name, string $pref): self
    {
        $this->prefs[$name] = CAppUI::pref($pref);

        return $this;
    }

    public function getConfigs(): array
    {
        return $this->configs;
    }

    public function setConfigs(array $configs): self
    {
        $this->configs = $configs;

        return $this;
    }

    /**
     * @param mixed $config
     */
    public function addConfigValue(string $name, $config): self
    {
        $this->configs[$name] = $config;

        return $this;
    }

    /**
     * Get and add the $config to configs under the key $name.
     *
     * @param null|string|CMbObject $context
     *
     * @throws Exception
     */
    public function addConfig(string $name, string $config, $context = null): self
    {
        $this->configs[$name] = CAppUI::conf($config, $context);

        return $this;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Totaly replace the links with $links.
     */
    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Add the $link under the $name key.
     */
    public function addLinkValue(string $name, string $link): self
    {
        $this->links[$name] = $link;

        return $this;
    }

    /**
     * Generate the $link with $parameters using a Router.
     * Add the $link under the $name key.
     *
     * @throws CMbException
     */
    public function addLink(string $name, string $link, array $parameters = []): self
    {
        if (!$this->router) {
            throw new CMbException('EntryPoint-Error-No router to generate link');
        }

        $this->links[$name] = $this->router->generate($link, $parameters);

        return $this;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Replace all the locales with $locales.
     *
     * @param array $locales Strings to localise. This array can contains simple strings or arrays.
     *                       In case of arrays the first element of the array will be the string to localise and
     *                       the rest of elements will be passed as additionnal arguments to the localisation function.
     */
    public function setLocales(array $locales): self
    {
        $this->locales = [];
        foreach ($locales as $locale) {
            if (is_array($locale)) {
                $str = array_shift($locale);
                $this->addLocale($str, ...$locale);
            } else {
                $this->addLocale($locale);
            }
        }

        return $this;
    }

    /**
     * @param string $locale  The string to localise
     * @param mixed  ...$args Additionnal arguments to pass to the localisation function
     */
    public function addLocale(string $locale, ...$args): self
    {
        $this->locales[$locale] = CAppUI::tr($locale, $args);

        return $this;
    }

    public function setScriptName(string $script): self
    {
        $this->script_name = $script;

        return $this;
    }

    public function getScriptName(): ?string
    {
        return $this->script_name;
    }
}
