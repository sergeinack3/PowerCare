<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;


use Exception;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\CConfigurationModelManager;
use Ox\Mediboard\System\CTranslationOverwrite;

/**
 * Translations manager class
 */
class Translation
{
    private const COMMON_LOCALES = "locales/*/common.php";
    private const COMMON         = 'common';

    private string $module;
    private string $language;
    private string $reference;

    protected array $languages              = [];
    private array   $ref_items              = [];
    private array   $archives               = [];
    private array   $translations_overwrite = [];

    // Old globs
    private array $trans            = [];
    private array $items            = [];
    private array $completions      = [];
    private array $all_locales_lang = [];
    private int   $total_count      = 0;
    private int   $local_count      = 0;

    public function __construct(string $module, string $language, string $reference)
    {
        $this->module    = $module;
        $this->language  = $language;
        $this->reference = $reference;
    }

    /**
     * Compute the translations files to build a translation array
     */
    public function getTranslations(): array
    {
        // Récupération des langues disponibles
        $this->setLanguages();

        $this->translations_overwrite = $this->getTranslationsOverwrite();

        // Récupération des chemins des fichiers de trads
        $locales_dirs = $this->getLocalesDirectories();

        // Chargement des locales pour chaque langue
        $all_locales = $this->getLocalesForAllLanguages($locales_dirs);

        // Reference items
        if ($this->reference !== $this->language) {
            $this->ref_items = $all_locales[$this->reference];
        }

        if (isset($all_locales[$this->language])) {
            $this->all_locales_lang = $all_locales[$this->language];
        }

        // Préparation de $this->trans avec en clé la chaine puis un tableau de langues et les traductions pour ces langues
        $this->trans = $this->getTranslationsFromLocalesDirectories($locales_dirs, $all_locales);

        $this->all_locales_lang = $this->sanitizeLocales($this->all_locales_lang);

        // Ajout des locales suppémentaires à $this->all_locales
        $this->all_locales_lang = $this->addReferencesLocales($this->all_locales_lang, array_keys($this->ref_items));

        // Pour chaque class ajout des locales
        $this->addLocalesForClasses();

        $this->addLocalesForConfigurations();

        // Ajout des locales restantes
        $this->addRemainingLocales();

        $this->applyTranslationsOverwrite();

        $this->calculCompletions();

        return $this->trans;
    }

    /**
     * Get the differents existing languages and their files
     */
    protected function setLanguages(): void
    {
        $files = glob(dirname(__DIR__, 2) . '/' . static::COMMON_LOCALES);

        foreach ($files as $file) {
            $name                   = basename(dirname($file));
            $this->languages[$name] = $file;
        }
    }

    /**
     * Get the locales directories and store the paths in $this->locales_dirs
     */
    protected function getLocalesDirectories(): array
    {
        $locales_dirs = [];
        if ($this->module !== self::COMMON) {
            $files = glob("modules/$this->module/locales/*");

            foreach ($files as $file) {
                $name                = basename($file, ".php");
                $locales_dirs[$name] = $file;
            }
        } else {
            // For common return the directories from root/locales
            $locales_dirs = $this->languages;
        }

        return $locales_dirs;
    }

    /**
     * Prepare the locales for the current module for each language
     */
    protected function getLocalesForAllLanguages(array $locales_dirs): array
    {
        $all_locales = [];

        // Récupération du fichier demandé pour toutes les langues
        // CMbConfig handle the locales files and load their values
        $translate_module             = new CMbConfig();
        $translate_module->sourcePath = null;
        foreach ($locales_dirs as $locale => $path) {
            $translate_module->options    = ["name" => "locales"];
            $translate_module->targetPath = $path;
            try {
                $translate_module->load();

                $all_locales[$locale] = $translate_module->values;
            } catch (Exception $e) {
                CAppUI::setMsg($e->getMessage(), UI_MSG_WARNING);
            }
        }

        return $all_locales;
    }

    /**
     * Prepare the locales for each language
     */
    private function getTranslationsFromLocalesDirectories(array $locales_dirs, array $all_locales): array
    {
        $translations = [];

        foreach ($locales_dirs as $locale => $path) {
            if (!isset($all_locales[$locale])) {
                continue;
            }

            foreach ($all_locales[$locale] as $k => $v) {
                $key                         = ($k === '' || is_int($k)) ? $v : $k;
                $translations[$key][$locale] = $v;
            }
        }

        return $translations;
    }

    /**
     * Sanitize the locales. Line feeds won't get properly stored if escaped
     */
    private function sanitizeLocales(array $locales): array
    {
        foreach ($locales as &$_locale) {
            $_locale = str_replace(['\n', '\t'], ["\n", "\t"], $_locale);
        }

        return $locales;
    }

    /**
     * Add locales remaining in ref language
     */
    private function addReferencesLocales(array $locales, array $items): array
    {
        // Add other remaining locales from reference language if defined
        foreach ($items as $_item) {
            if (!array_key_exists($_item, $locales)) {
                $locales[$_item] = "";
            }
        }

        return $locales;
    }

    /**
     * Set the classes for the current module
     */
    protected function getClassesForModule(): array
    {
        $classes = [];
        if ($this->module !== self::COMMON) {
            $classes = CModule::getClassesFor($this->module);

            // Force CModule translation in system module
            if ($this->module === "system") {
                $classes[] = "CModule";
            }
        }

        return $classes;
    }

    /**
     * Add the locales keys for a class
     *
     * @param string $class Class to add locales for
     */
    private function addLocalesForClass(string $class): void
    {
        if (!class_exists($class) || !is_subclass_of($class, CModelObject::class)) {
            return;
        }

        /** @var CModelObject $object */
        $object    = new $class;
        $classname = $object->_class;

        // Traductions au niveau classe
        $this->addLocale($classname, $classname, "$classname");

        if ($object->_spec->archive) {
            $this->archives[$class] = true;

            return;
        }

        // Add default class locales
        $this->addLocale($classname, $classname, "$classname.none");
        $this->addLocale($classname, $classname, "$classname.one");
        $this->addLocale($classname, $classname, "$classname.all");
        $this->addLocale($classname, $classname, "$classname-msg-create");
        $this->addLocale($classname, $classname, "$classname-msg-modify");
        $this->addLocale($classname, $classname, "$classname-msg-delete");
        $this->addLocale($classname, $classname, "$classname-title-create");
        $this->addLocale($classname, $classname, "$classname-title-modify");

        $this->addLocalesFromProperties($object);
    }

    /**
     * Add locales keys for each prop from the class
     *
     * @param CModelObject $object Object to add locales for
     */
    private function addLocalesFromProperties(CModelObject $object): void
    {
        $classname = $object->_class;

        // Translate key
        if ($object->_spec->key) {
            $prop = $object->_spec->key;

            $this->addLocale($classname, $prop, "$classname-$prop");
            $this->addLocale($classname, $prop, "$classname-$prop-desc");
            $this->addLocale($classname, $prop, "$classname-$prop-court");
        }

        // Traductions de chaque propriété
        foreach ($object->_specs as $prop => $spec) {
            if (!$spec->prop) {
                continue;
            }

            if (in_array($prop, [$object->_spec->key, "_view", "_shortview"])) {
                continue;
            }

            $this->addLocale($classname, $prop, "$classname-$prop");
            $this->addLocale($classname, $prop, "$classname-$prop-desc");
            $this->addLocale($classname, $prop, "$classname-$prop-court");

            if ($spec instanceof CEnumSpec) {
                $this->addEnumLocales($classname, $spec, $prop);
            }

            if ($spec instanceof CRefSpec && $prop[0] != "_") {
                $this->addRefLocales($object, $spec, $prop);
            }
        }

        // Traductions pour les uniques
        foreach (array_keys($object->_spec->uniques) as $unique) {
            $this->addLocale($classname, "Failures", "$classname-failed-$unique");
        }
    }

    /**
     * Add locales keys from enum props
     *
     * @param string    $classname Name of the class
     * @param CEnumSpec $spec      Enum spec to add locales for
     * @param string    $prop      Prop to use
     */
    private function addEnumLocales(string $classname, CMbFieldSpec $spec, string $prop): void
    {
        if (!$spec->notNull) {
            $this->addLocale($classname, $prop, "$classname.$prop.");
        }

        foreach (explode("|", $spec->list) as $value) {
            $this->addLocale($classname, $prop, "$classname.$prop.$value");
        }
    }

    /**
     * Add locales keys from ref specs
     *
     * @param CModelObject $object Object to get locales for
     * @param CRefSpec     $spec   Ref spec to add locales for
     * @param string       $prop   Prop to use
     */
    private function addRefLocales(CModelObject $object, CRefSpec $spec, string $prop): void
    {
        $classname = $object->_class;

        if ($spec->meta && $object->_specs[$spec->meta] instanceof CEnumSpec) {
            $classes = $object->_specs[$spec->meta]->_list;
            foreach ($classes as $fwd_class) {
                $this->addBackLocales($fwd_class, $spec, $classname, $prop);
            }
        } else {
            $fwd_class = $spec->class;
            $this->addBackLocales($fwd_class, $spec, $classname, $prop);
        }
    }

    /**
     * Add back refs locales
     *
     * @param string   $fwd_class Forward class to get locales for
     * @param CRefSpec $spec      Ref spec to add locales for
     * @param string   $classname Main class name
     * @param string   $prop      Prop to use
     */
    private function addBackLocales(string $fwd_class, CRefSpec $spec, string $classname, string $prop): void
    {
        $fwd_object = new $fwd_class;

        // Find corresponding back name
        // Use preg_grep to match backprops like "CClass field cascade"
        $back_name = preg_grep("/{$spec->className} {$spec->fieldName}\s?.*/", $fwd_object->_backProps);
        if (is_array($back_name)) {
            $back_array = array_keys($back_name);
            $back_name  = reset($back_array);
        }

        $this->addLocale($classname, $prop, "$spec->class-back-$back_name");
        $this->addLocale($classname, $prop, "$spec->class-back-$back_name.empty");
    }

    /**
     * Add locales from module configs
     *
     * @param array $model Configuration model to parse
     */
    private function addLocalesForConfigs(array $model): void
    {
        $features = [];
        foreach ($model as $_model) {
            foreach ($_model as $_feature => $_submodel) {
                if (strpos($_feature, $this->module) === 0) {
                    $parts = explode(" ", $_feature);
                    array_shift($parts); // Remove module name
                    $item   = array_pop($parts);   // Remove config name
                    $prefix = implode("-", $parts);
                    if (!isset($features[$prefix])) {
                        $features[$prefix] = [];
                    }

                    $features[$prefix][$item] = $item;
                }
            }
        }

        foreach ($features as $_prefix => $values) {
            $this->addConfigCategoryLocales($this->module, $_prefix, null, false);
            $this->addConfigCategoryLocales($this->module, $_prefix, $values);
        }
    }

    /**
     * Add locales from module action
     */
    protected function addTabsActionLocales(): void
    {
        $files = CAppUI::readFiles("modules/$this->module", '\.php$');

        $this->addLocale("Action", "Name", "module-$this->module-court");
        $this->addLocale("Action", "Name", "module-$this->module-long");

        foreach ($files as $_file) {
            $_tab = substr($_file, 0, -4);

            if (in_array($_tab, ["setup", "index", "config", "preferences", "configuration"])) {
                continue;
            }

            $this->addLocale("Action", "Tabs", "mod-$this->module-tab-$_tab");
        }
    }

    /**
     * Add remaining locales
     */
    private function addRemainingLocales(): void
    {
        // Remaining locales go to an 'other' with a computed category
        foreach (array_keys($this->all_locales_lang) as $_item) {
            // Explode en dashes and dots
            $parts = explode(".", str_replace("-", ".", $_item));
            $this->addLocale("Other", $parts[0], $_item);
        }
    }

    /**
     * Get the CTranslationOverload for the current language
     */
    protected function getTranslationsOverwrite(): array
    {
        $translations = [];

        foreach ($this->loadTranslationsOverwrite() as $_trans) {
            $translations[$_trans->source] = $_trans->translation;
        }

        return $translations;
    }

    /**
     * @return CTranslationOverwrite[]
     * @throws Exception
     */
    protected function loadTranslationsOverwrite(): array
    {
        try {
            $trans           = new CTranslationOverwrite();
            $trans->language = $this->language;
            $translations    = $trans->loadMatchingListEsc();
        } catch (Exception $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
        }

        return $translations ?? [];
    }

    /**
     * Add a locale item in a three levels collection
     * (Yet more of an internationalisation item)
     *
     * @param string $class Class name
     * @param string $cat   Category name
     * @param string $name  Item name
     */
    private function addLocale(string $class, string $cat, string $name): void
    {
        $this->items[$class][$cat][$name] = "";

        if (array_key_exists($name, $this->trans) && isset($this->trans[$name][$this->language])) {
            $this->items[$class][$cat][$name] = $this->trans[$name][$this->language];
        }

        if (array_key_exists($name, $this->all_locales_lang)) {
            unset($this->all_locales_lang[$name]);
        }

        // Stats
        if (!isset($this->completions[$class])) {
            $this->completions[$class] = [
                'total'   => 0,
                'count'   => 0,
                'percent' => 0,
            ];
        }

        $this->completions[$class]["total"]++;
        $this->total_count++;

        if ($this->items[$class][$cat][$name]) {
            $this->completions[$class]["count"]++;
            $this->local_count++;
        }
    }

    /**
     * Add locale item for config category values
     *
     * @param string            $chapter  Chapter name
     * @param string            $category Category name
     * @param string|null|array $values   Key-value array when necessary
     * @param bool              $add_desc Tell wether shoud add a description locale item
     */
    function addConfigCategoryLocales(?string $chapter, string $category, $values, bool $add_desc = true): void
    {
        $prefix = $chapter ? "$chapter-$category" : $category;

        if (!is_array($values)) {
            $this->addLocale("Config", "global", "config-$prefix");
            if ($add_desc) {
                $this->addLocale("Config", "global", "config-$prefix-desc");
            }

            return;
        }

        foreach ($values as $key => $value) {
            $this->addLocale("Config", $category, "config-$prefix-$key");
            if ($add_desc) {
                $this->addLocale("Config", $category, "config-$prefix-$key-desc");
            }
        }
    }

    private function applyTranslationsOverwrite(): void
    {
        foreach ($this->items as $main_key => $items) {
            foreach ($items as $secondary_key => $translations) {
                foreach ($translations as $key => $value) {
                    if (isset($this->translations_overwrite[$key])) {
                        // Add completion if the translation was not present but is in overload
                        if ($this->items[$main_key][$secondary_key][$key] === '') {
                            $this->completions[$main_key]['count']++;
                        }

                        $this->items[$main_key][$secondary_key][$key] = '|overwrite|' . $this->translations_overwrite[$key];
                    }
                }
            }
        }
    }

    private function calculCompletions(): void
    {
        foreach ($this->completions as $key => ['count' => $count, 'total' => $total]) {
            $this->completions[$key]["percent"] = round(100 * $count / $total, 3);
        }
    }

    private function addLocalesForClasses(): void
    {
        foreach ($this->getClassesForModule() as $_class) {
            $this->addLocalesForClass($_class);
        }
    }

    private function addLocalesForConfigurations(): void
    {
        // Pour chaque config ajout des locales
        if ($this->module && $this->module !== self::COMMON) {
            // Add the configs from the default conf tree
            $model = CConfigurationModelManager::_getModel($this->module);
            $this->addLocalesForConfigs($model);

            // Add the configs that exists in config.php
            if ($categories = @CAppUI::conf($this->module)) {
                foreach ($categories as $category => $values) {
                    $this->addConfigCategoryLocales($this->module, $category, $values);
                }
            }

            // Ajout de toutes les configurations qui ne sont pas dans d'autres modules
            if ($this->module == "system") {
                foreach (CAppUI::conf() as $chapter => $values) {
                    if (!CModule::exists($chapter) && $chapter != "db") {
                        $this->addConfigCategoryLocales(null, $chapter, $values);
                    }
                }
            }

            // Pour chaque tabs ajout des locales
            $this->addTabsActionLocales();
        }
    }


    public function getCompletion(): float
    {
        return $this->total_count ? round(100 * $this->local_count / $this->total_count, 3) : 0;
    }

    public function getTotalCount(): int
    {
        return $this->total_count;
    }

    public function getLocalCount(): int
    {
        return $this->local_count;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getArchives(): array
    {
        return $this->archives;
    }

    public function getCompletions(): array
    {
        return $this->completions;
    }

    public function getLanguages(): array
    {
        return array_keys($this->languages);
    }

    public function getRefItems(): array
    {
        return $this->ref_items;
    }
}
