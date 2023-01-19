<?php

/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;

/**
 * Build a new module
 */
class CModuleBuilder
{
    public const LICENSE_GPL = [
        ' * @license https://www.gnu.org/licenses/gpl.html GNU General Public License',
        ' * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License',
    ];

    public const LICENSE_OXOL = [
        " * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License",
    ];

    public const LICENSES = [
        'GNU GPL' => self::LICENSE_GPL,
        'OXOL'    => self::LICENSE_OXOL,
    ];

    public const ZIP_PATH = 'modules/dPdeveloppement/resources/sample_module.zip';

    public const TMP_PATH = 'tmp/sample_module';

    /** @var string */
    private $root_dir;

    /** @var string */
    private $namespace;

    /** @var array */
    private $license;

    /** @var string */
    private $module_name;

    /** @var string */
    private $name_short;

    /** @var string */
    private $name_long;

    /** @var string */
    private $trigramme;

    /** @var string */
    private $mod_package;

    /** @var string */
    private $mod_category;

    /** @var string */
    private $namespace_category;

    public function __construct(
        string $module_name,
        string $namespace,
        string $name_short,
        string $name_long,
        string $license,
        string $trigramme,
        string $mod_package,
        string $mod_category,
        string $namespace_category
    ) {
        $this->root_dir = rtrim(CAppUI::conf('root_dir'), '/\\') . '/';

        $this->module_name        = $this->sanitizeModuleName($module_name);
        $this->namespace          = $namespace;
        $this->name_short         = $this->sanitizeSimpleString($name_short);
        $this->name_long          = $this->sanitizeSimpleString($name_long);
        $this->license            = (isset(self::LICENSES[$license])) ? self::LICENSES[$license] : self::LICENSE_GPL;
        $this->trigramme          = $trigramme;
        $this->mod_package        = $mod_package;
        $this->mod_category       = $mod_category;
        $this->namespace_category = $namespace_category;
    }

    /**
     * @throws CMbException
     */
    public function build(): void
    {
        if (is_dir($this->root_dir . 'modules/' . $this->module_name)) {
            throw new CMbException("Module '{$this->module_name}' existe déjà");
        }

        $tmp_path = $this->root_dir . self::TMP_PATH;
        if (!$this->extractFiles($this->root_dir . self::ZIP_PATH, $tmp_path)) {
            throw new CMbException("Impossible d'extraire l'archive '" . self::ZIP_PATH . "'");
        }

        rename($tmp_path . '/sample_module', "{$tmp_path}/{$this->module_name}");

        $files = $this->listFiles();

        $this->replaceInfosInFiles($files);

        $this->renameFiles();
    }

    private function replaceInfosInFiles(array $files): void
    {
        $translate = [
            '{NAME_CANONICAL}'     => lcfirst($this->module_name),
            '{NAME_SHORT}'         => $this->name_short,
            '{TRIGRAMME}'          => $this->trigramme,
            '{NAME_LONG}'          => $this->name_long,
            '{LICENSE}'            => implode("\n", $this->license),
            '{PACKAGE}'            => ucfirst($this->module_name),
            '{PACKAGE_LOWER}'      => $this->module_name,
            '{NAMESPACE}'          => str_replace("\\\\", "\\", $this->namespace),
            '{NAMESPACE_ESCAPED}'  => str_replace("\\", "\\\\", $this->namespace),
            '{MOD_PACKAGE}'        => $this->mod_package,
            '{MOD_CATEGORY}'       => $this->mod_category,
            '{NAMESPACE_CATEGORY}' => strtolower($this->namespace_category),
        ];

        foreach ($files as $_file) {
            if (is_dir($_file)) {
                continue;
            }

            file_put_contents(
                $_file,
                strtr(file_get_contents($_file), $translate)
            );
        }
    }

    private function listFiles(): array
    {
        $path = $this->root_dir . self::TMP_PATH . "/{$this->module_name}";

        return array_merge(
            glob("$path/*"),
            glob("$path/classes/*"),
            glob("$path/locales/*"),
            glob("$path/templates/*")
        );
    }

    private function renameFiles(): void
    {
        $maj_canonical = ucfirst($this->module_name);

        $module_dir = $this->root_dir . 'modules/' . $this->module_name;

        // Move tmp dir to module
        rename($this->root_dir . self::TMP_PATH . "/{$this->module_name}", $module_dir);
        rename("{$module_dir}/classes/CSetup.php", "{$module_dir}/classes/CSetup{$maj_canonical}.php");
        rename(
            "{$module_dir}/classes/CConfiguration.php",
            "{$module_dir}/classes/CConfiguration{$maj_canonical}.php"
        );
        rename(
            "{$module_dir}/classes/CTabsModule.php",
            "{$module_dir}/classes/CTabs{$maj_canonical}.php"
        );
    }

    private function extractFiles(string $from_dir, string $to_dir): bool
    {
        $files_count = CMbPath::extract($from_dir, $to_dir);

        return $files_count !== false;
    }

    private function sanitizeModuleName(string $module_name): string
    {
        return preg_replace("/[^\w\s]/", "", $module_name);
    }

    private function sanitizeSimpleString(string $sanitize): string
    {
        return CMbString::purifyHTML($sanitize);
    }
}
