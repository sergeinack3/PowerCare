<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\Translation;
use Ox\Mediboard\System\CTranslationOverwrite;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;

class TranslationTest extends OxUnitTestCase
{
    private const COMMON_LANGUAGES = [
        'de'    => 'locales/de/common.php',
        'en'    => 'locales/en/common.php',
        'fr'    => 'locales/fr/common.php',
        'fr-be' => 'locales/fr-be/common.php',
        'it'    => 'locales/it/common.php',
        'nl-be' => 'locales/nl-be/common.php',
    ];

    public function testSetLanguages(): void
    {
        $translation = new Translation('system', 'fr', 'fr');
        $this->invokePrivateMethod($translation, 'setLanguages');

        $languages = $this->getPrivateProperty($translation, 'languages');
        foreach (self::COMMON_LANGUAGES as $lang => $path) {
            $this->assertStringEndsWith($path, $languages[$lang]);
        }
    }

    public function testGetLocalesDirectoriesModuleDoesNotExists(): void
    {
        $translation = new Translation('foo', 'fr', 'fr');
        $this->assertEquals([], $this->invokePrivateMethod($translation, 'getLocalesDirectories'));
    }

    public function testGetTranslationsFromLocalesDirectories(): void
    {
        $locales_dirs = ['fr' => '', 'en' => ''];
        $all_locales  = [
            'fr' => ['test', 'foo' => 'bar', null => 'toto'],
        ];

        $translation = new Translation('system', 'fr', 'fr');
        $this->assertEquals(
            [
                'test' => ['fr' => 'test'],
                'foo'  => ['fr' => 'bar'],
                'toto' => ['fr' => 'toto'],
            ],
            $this->invokePrivateMethod(
                $translation,
                'getTranslationsFromLocalesDirectories',
                $locales_dirs,
                $all_locales
            )
        );
    }

    public function testSanitizeLocales(): void
    {
        $locales = [
            'foo',
            'bar\n\t',
            't\ne\t\ns\tt',
        ];

        $translation = new Translation('system', 'fr', 'fr');

        $this->assertEquals(['foo', "bar\n\t", "t\ne\t\ns\tt"],
                            $this->invokePrivateMethod($translation, 'sanitizeLocales', $locales));
    }

    public function testAddReferencesLocales(): void
    {
        $locales = ['foo' => null, 'bar' => '', 'test' => 'toto'];
        $items   = ['foo', 'bar', 'test', 'other'];

        $translation = new Translation('system', 'fr', 'fr');
        $this->assertEquals(
            ['foo' => null, 'bar' => '', 'test' => 'toto', 'other' => ''],
            $this->invokePrivateMethod($translation, 'addReferencesLocales', $locales, $items)
        );
    }

    public function testAddLocale(): void
    {
        $translation = new Translation('system', 'fr', 'fr');
        $this->assertEmpty($this->getPrivateProperty($translation, 'items'));
        $this->assertEmpty($this->getPrivateProperty($translation, 'completions'));

        $reflection = new ReflectionClass($translation);
        $prop       = $reflection->getProperty('trans');
        $prop->setAccessible(true);
        $prop->setValue($translation, ['foo' => ['fr' => 'bar'], 'test' => ['fr' => 'value']]);
        $prop->setAccessible(false);

        $this->invokePrivateMethod($translation, 'addLocale', 'className', 'Cat', 'foo');

        $items       = $this->getPrivateProperty($translation, 'items');
        $completions = $this->getPrivateProperty($translation, 'completions');

        $this->assertEquals('bar', $items['className']['Cat']['foo']);
        $this->assertEquals(['total' => 1, 'count' => 1, 'percent' => 0], $completions['className']);

        $this->invokePrivateMethod($translation, 'addLocale', 'className', 'Cat', 'NonExisting');

        $items       = $this->getPrivateProperty($translation, 'items');
        $completions = $this->getPrivateProperty($translation, 'completions');

        $this->assertEquals('', $items['className']['Cat']['NonExisting']);
        $this->assertEquals(['total' => 2, 'count' => 1, 'percent' => 0], $completions['className']);

        $this->invokePrivateMethod($translation, 'addLocale', 'className', 'Cat', 'test');

        $items       = $this->getPrivateProperty($translation, 'items');
        $completions = $this->getPrivateProperty($translation, 'completions');

        $this->assertEquals('value', $items['className']['Cat']['test']);
        $this->assertEquals(['total' => 3, 'count' => 2, 'percent' => 0], $completions['className']);
    }

    public function testAddLocalesForClass(): void
    {
        $translation = new Translation('system', 'fr', 'fr');
        $this->assertEmpty($this->getPrivateProperty($translation, 'items'));
        $this->assertEmpty($this->getPrivateProperty($translation, 'completions'));
        $this->invokePrivateMethod($translation, 'addLocalesForClass', 'lorem');
        $this->assertEmpty($this->getPrivateProperty($translation, 'items'));
        $this->assertEmpty($this->getPrivateProperty($translation, 'completions'));

        $this->invokePrivateMethod($translation, 'addLocalesForClass', 'CUser');

        $items = $this->getPrivateProperty($translation, 'items');
        $this->assertArrayHasKey('CUser', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser.none', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser.one', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser.all', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser-msg-create', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser-msg-modify', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser-msg-delete', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser-title-create', $items['CUser']['CUser']);
        $this->assertArrayHasKey('CUser-title-modify', $items['CUser']['CUser']);
    }

    public function testGetTranslations(): void
    {
        $translation = $this->getMockBuilder(Translation::class)
            ->onlyMethods(
                [
                    'setLanguages',
                    'getTranslationsOverwrite',
                    'getLocalesForAllLanguages',
                    'getClassesForModule',
                    'addTabsActionLocales',
                    'getLocalesDirectories',
                ]
            )
            ->setConstructorArgs(['sample', 'fr', 'fr'])
            ->getMock();

        $reflection = new ReflectionClass($translation);
        $prop       = $reflection->getProperty('languages');
        $prop->setAccessible(true);
        $prop->setValue($translation, ['fr' => '']);
        $prop->setAccessible(false);

        $translation->method('getTranslationsOverwrite')->willReturn($this->getTranslationsOverwrite());
        $translation->method('getLocalesDirectories')->willReturn(['fr' => '']);
        $translation->method('getLocalesForAllLanguages')->willReturn(
            [
                'fr' => [
                    'foo'  => 'bar',
                    'test' => 'test',
                    'key'  => 'value',
                ],
            ]
        );
        $translation->method('getClassesForModule')->willReturn(['CSampleMovie', 'CSamplePerson']);

        $translations = $translation->getTranslations();
        $this->assertEquals(
            [
                'foo'  => ['fr' => 'bar'],
                'test' => ['fr' => 'test'],
                'key'  => ['fr' => 'value'],
            ],
            $translations
        );

        $items = $translation->getItems();

        $this->assertEquals(
            [
                'foo'  => ['foo' => '|overwrite|foo replaced'],
                'test' => ['test' => 'test'],
                'key'  => ['key' => 'value'],
            ],
            $items['Other']
        );

        $this->assertArrayHasKey('CSampleMovie', $items);
        $this->assertArrayHasKey('CSamplePerson', $items);
    }

    private function getTranslationsOverwrite(): array
    {
        return ['foo' => 'foo replaced', 'Nop nop nop' => 'Bar !'];
    }
}
