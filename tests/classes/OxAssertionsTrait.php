<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests;

use Countable;
use DOMDocument;
use DOMNode;
use DOMXPath;

/**
 * horizontal composition of assertions
 */
trait OxAssertionsTrait
{
    /**
     * @param string $message
     */
    public static function markTestSkipped(string $message = ''): void
    {
        $message = $message ?: 'Missing Skipped message';
        parent::markTestSkipped($message);
    }

    /**
     * @param mixed $iterable
     * @param array $expected
     * @param int   $max_count_iterable
     *
     * @return void
     */
    public function assertIterableCount($iterable, $expected, int $max_count_iterable): void
    {
        $this->assertIsIterable($iterable);

        $this->assertEquals($expected[0], $iterable->current());

        $this->assertTrue($iterable->valid());

        $iterable->next();
        $this->assertEquals($expected[1], $iterable->current());

        $iterable->next();
        $this->assertEquals(2, $iterable->key());

        for ($i = 0; $i < $max_count_iterable * 2; $i++) {
            $iterable->next();
        }

        $this->assertFalse($iterable->valid());

        $iterable->rewind();
        $this->assertEquals(0, $iterable->key());
    }

    /**
     * @param mixed $countable
     * @param int   $expected
     *
     * @return void
     */
    public function assertCountableCount($countable, int $expected): void
    {
        if (!($countable instanceof Countable)) {
            $this->fail('The object is not a countable');
        }

        $this->assertCount($expected, $countable);
    }

    /**
     * @param DOMDocument  $document
     * @param mixed        $expected
     * @param string       $xpath
     * @param string|null  $message
     * @param DOMNode|null $context
     */
    protected function assertXpathMatch(
        DOMDocument $document,
        $expected,
        string $xpath,
        string $message = "",
        DOMNode $context = null
    ): void {
        $xpathObj = new DOMXPath($document);

        $context = ($context === null)
            ? $document->documentElement
            : $context;

        $res = $xpathObj->evaluate($xpath, $context);

        $this->assertEquals(
            $expected,
            $res,
            $message
        );
    }

    /**
     * @param DOMDocument  $document
     * @param string       $pattern
     * @param string       $xpath
     * @param string|null  $message
     * @param DOMNode|null $context
     */
    protected function assertXpathRegMatch(
        DOMDocument $document,
        string $pattern,
        string $xpath,
        string $message = '',
        DOMNode $context = null
    ): void {
        $xpathObj = new DOMXPath($document);

        $context = ($context === null)
            ? $document->documentElement
            : $context;

        $res = $xpathObj->evaluate($xpath, $context);

        $this->assertMatchesRegularExpression(
            $pattern,
            $res,
            $message
        );
    }

    /**
     * Assert that two array have content equals
     *
     * @param array  $expected
     * @param array  $actual
     * @param string $message
     */
    public function assertArrayContentsEquals(array $expected, array $actual, string $message = ''): void
    {
        $this->assertCount(count($expected), $actual, $message);
        foreach ($expected as $expected_value) {
            $this->assertContains($expected_value, $actual);
        }
    }
}
