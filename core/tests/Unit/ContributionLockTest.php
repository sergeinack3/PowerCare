<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ContributionLockTest extends OxUnitTestCase
{
    private const COMPOSER_JSON_FILE                  = 'composer.json';
    private const COMPOSER_JSON_REQUIRE               = 'require';
    private const EXPECTED_COMPOSER_JSON_DEPENDENCIES = 71;
    private const EXPECTED_ELEMENTS_AT_ROOT_DIRECTORY = 45;
    private const WHITELISTED_ELEMENTS                = [
        'release.xml',
    ];

    /**
     * @throws Exception
     */
    public function testComposerDependenciesAmount(): void
    {
        $current = 0;
        $file    = CAppUI::conf("root_dir") . '/' . self::COMPOSER_JSON_FILE;
        $fs      = new Filesystem();
        if ($fs->exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (array_key_exists(self::COMPOSER_JSON_REQUIRE, $data)) {
                $current = count($data[self::COMPOSER_JSON_REQUIRE]);
            }
        }

        $this->assertEquals(
            self::EXPECTED_COMPOSER_JSON_DEPENDENCIES,
            $current,
            "Contact the framework service support for approval to add composer dependencies (refer to #ADR-024)"
        );
    }

    /**
     * @throws Exception
     */
    public function testItemsAmountAtRootDirectory(): void
    {
        $current   = 0;
        $directory = CAppUI::conf("root_dir") . '/';
        $list      = array_diff(
            scandir($directory),
            array_merge(
                ['.', '..', '.git'],
                self::WHITELISTED_ELEMENTS
            )
        );

        if (!empty($list)) {
            $current = count($list);
            /* Filter with values from .gitignore */
            $ps = new Process(['git', 'status', '--ignored', '--porcelain'], $directory);
            $ps->run();
            if ($ps->isSuccessful()) {
                $ignored = [];
                $data    = explode(PHP_EOL, $ps->getOutput());

                foreach ($data as $line) {
                    $pattern = '!! ';
                    if (!(substr($line, 0, strlen($pattern)) === $pattern)) {
                        continue;
                    }

                    if (count(array_filter(explode('/', $line))) > 1) {
                        continue;
                    }

                    $line      = explode('/', str_replace($pattern, "", $line))[0];
                    $ignored[] = $line;
                }
                $ignored = array_unique($ignored);

                if (count($ignored) > 0) {
                    $filtered = array_diff($list, $ignored);
                    $current  = count($filtered);
                }
            }
        }

        $this->assertEquals(
            self::EXPECTED_ELEMENTS_AT_ROOT_DIRECTORY,
            $current,
            "Contact the framework service support for approval to add elements at root directory"
        );
    }
}
