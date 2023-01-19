<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Html\Markdown;

/**
 * Description
 */
interface MarkdownParserInterface
{
    /**
     * Enables breaking lines
     *
     * @param bool $enabled
     */
    public function setBreaksEnabled($enabled);

    /**
     * Reduces the list of elements that can be formatted
     */
    public function reduceFormatting();

    /**
     * Reset the list of elements that can be formatted
     */
    public function resetFormatting();

    /**
     * Enables the colored text feature
     */
    public function enableColoredText();

    /**
     * Disables the colored text feature
     */
    public function disableColoredText();

    /**
     * Fix the text to avoid a bug with blank lines
     *
     * @param string $text Text to fix
     *
     * @return string
     */
    public function fixEmptyLines($text);

    /**
     * Parses the text
     *
     * @param string $text Text to parse
     *
     * @return string
     */
    public function parse($text);
}
