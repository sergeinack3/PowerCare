<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Html\Markdown;

use Ox\Core\CMbException;
use Parsedown;

/**
 * Description
 */
class ParsedownAdapter extends Parsedown implements MarkdownParserInterface
{
    protected $OriginalBlockTypes;
    protected $OriginalInlineTypes;
    protected $OriginalInlineMarkerList;

    /**
     * @inheritdoc
     */
    public function reduceFormatting()
    {
        $this->OriginalBlockTypes = $this->BlockTypes;

        $this->BlockTypes = [
            '*' => ['List'],
            '-' => ['List'],
            '0' => ['List'],
            '1' => ['List'],
            '2' => ['List'],
            '3' => ['List'],
            '4' => ['List'],
            '5' => ['List'],
            '6' => ['List'],
            '7' => ['List'],
            '8' => ['List'],
            '9' => ['List'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function resetFormatting()
    {
        $this->BlockTypes = $this->OriginalBlockTypes;
    }

    /**
     * @inheritdoc
     */
    public function enableColoredText()
    {
        $marker = '{';

        if (isset($this->InlineTypes[$marker])) {
            throw new CMbException('Markdown-error-%s marker already used', $marker);
        }

        $this->OriginalInlineTypes      = $this->InlineTypes;
        $this->OriginalInlineMarkerList = $this->inlineMarkerList;

        $this->InlineTypes[$marker] = ['ColoredText'];
        $this->inlineMarkerList     .= $marker;
    }

    /**
     * @inheritdoc
     */
    public function disableColoredText()
    {
        $this->InlineTypes      = $this->OriginalInlineTypes;
        $this->inlineMarkerList = $this->OriginalInlineMarkerList;
    }

    /**
     * Inline color parser : {c:red} Text {/c}
     *
     * @param array $Excerpt Excerpt
     *
     * @return array|null
     */
    protected function inlineColoredText($Excerpt)
    {
        if (preg_match('/^{c:([#\w]\w+)}([^{]+){\/c}/', $Excerpt['text'], $matches)) {
            return [
                'extent'  => strlen($matches[0]),
                'element' => [
                    'name'       => 'span',
                    'handler'    => 'line',
                    'text'       => $matches[2],
                    'attributes' => [
                        'style' => 'color: ' . $matches[1],
                    ],
                ],
            ];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function fixEmptyLines($text)
    {
        // In some cases Parsedown can't handle lines with only spaces
        // Trim each line to reduce them before parsing to avoid that
        // Standardize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Remove surrounding line breaks
        $text = trim($text, "\n");
        // Split text into lines
        $lines = explode("\n", $text);

        $text = "";
        foreach ($lines as $_line) {
            $text .= trim($_line) . "\n";
        }

        return $text;
    }
}
