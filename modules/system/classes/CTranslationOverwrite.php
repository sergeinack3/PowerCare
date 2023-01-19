<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Core\CRequest;

/**
 * Overload of translations.
 */
class CTranslationOverwrite extends CMbObject
{
    public $translation_id;

    public $source;
    public $translation;
    public $language;
    public $_old_translation;

    public bool $_in_cache = false;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec                  = parent::getSpec();
        $spec->table           = 'translation';
        $spec->key             = 'translation_id';
        $spec->uniques['trad'] = ['source', 'language'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                = parent::getProps();
        $props['source']      = 'str notNull';
        $props['language']    = 'enum notNull list|' . implode('|', $this->getAvailableLanguages()) . ' default|fr';
        $props['translation'] = 'text markdown notNull';

        return $props;
    }

    /**
     * Load the actives translation from mediboard (used to compare with the sql one)
     */
    public function loadOldTranslation(array $locales = []): string
    {
        if (!count($locales)) {
            $locales = [];
            $locale  = CAppUI::pref("LOCALE", "fr");

            foreach (CAppUI::getLocaleFilesPaths($locale) as $_path) {
                include_once $_path;
            }
        }

        return $this->_old_translation = $locales[$this->source] ?? "";
    }

    /**
     * Check if the translation is cached
     *
     * @return bool
     */
    public function checkInCache(): bool
    {
        static $locales;

        if (!$locales) {
            $locales = CAppUI::flattenCachedLocales(CAppUI::$lang);
        }

        return $this->_in_cache = (isset($locales[$this->source]) && ($locales[$this->source] == $this->translation));
    }

    /**
     * @inheritdoc
     */
    public function updatePlainFields(): void
    {
        parent::updatePlainFields();

        // Avoid seting an empty string instead of null !
        if ($this->translation !== null) {
            $this->translation = CMbString::purifyHTML($this->translation);
        }
    }

    /**
     * Transform the mb locales with the overwrite system
     *
     * @param array       $locales  locales from mediboard
     * @param string|null $language language chosen, if not defined, use the preference.
     *
     * @return array $locales locales transformed
     */
    public function transformLocales(array $locales, ?string $language = null): array
    {
        $key = 'locales-' . $language . '-' . CAppUI::LOCALES_OVERWRITE;

        $cache = Cache::getCache(Cache::INNER_OUTER);
        if (!($overwrites = $cache->get($key))) {
            $ds    = $this->_spec->ds;
            $where = [
                'language' => $ds->prepare('=%', $language ?: CAppUI::pref('LOCALE')),
            ];

            $query = new CRequest();
            $query->addSelect(['source', 'translation']);
            $query->addTable('translation');
            $query->addWhere($where);
            $overwrites = $ds->loadList($query->makeSelect());

            $cache->set($key, $overwrites);
        }

        foreach ($overwrites as $_overwrite) {
            $locales[$_overwrite['source']] = $_overwrite['translation'];
        }

        return $locales;
    }

    protected function getAvailableLanguages(): array
    {
        return CAppUI::getAvailableLanguages();
    }
}
