<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Html\Markdown;

/**
 * Custom markdown, with color a tag
 */
class Markdown
{
    /** @var MarkdownParserInterface $adapter */
    private $adapter;

    /**
     * Markdown constructor.
     *
     * @param MarkdownParserInterface $adapter Markdown parser adapter
     */
    public function __construct(MarkdownParserInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->adapter->enableColoredText();
    }

    /**
     * Allows breaking lines
     *
     * @param bool $breaksEnabled
     *
     * @return MarkdownParserInterface
     */
    public function setBreaksEnabled($breaksEnabled)
    {
        return $this->adapter->setBreaksEnabled($breaksEnabled);
    }

    /**
     * Reduces the list of elements that can be formatted
     */
    public function reduceFormatting()
    {
        return $this->adapter->reduceFormatting();
    }

    /**
     * Reset the list of elements that can be formatted
     */
    public function resetFormatting()
    {
        return $this->adapter->resetFormatting();
    }

    /**
     * Parses the text
     *
     * @param string $text Text to parse
     *
     * @return string
     */
    public function parse($text)
    {
        return $this->adapter->parse($text);
    }
}
