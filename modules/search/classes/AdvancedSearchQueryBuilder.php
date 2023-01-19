<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Core\CMbException;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

class AdvancedSearchQueryBuilder
{
    public const CONTAIN_WORDS   = 0;
    public const EXACT_MATCH     = 1;
    public const CONTAINS_A_WORD = 2;

    /** @var string[] */
    private $words;

    /** @var string[] */
    private $without_words;

    /** @var int */
    private $policy;

    /** @var CMediusers */
    private $author;

    /** @var CPatient */
    private $patient;

    /** @var string */
    private $atc;

    /** @var string */
    private $cim;

    /** @var string */
    private $ccam;

    public function __construct(string $words, int $policy)
    {
        if (!($policy >= 0 && $policy < 3)) {
            throw new CMbException('Policy issue');
        }

        $this->words  = explode(' ', $words) ?? [];
        $this->policy = $policy;
    }

    public function setWithoutWords(string $without_words): void
    {
        $this->without_words = explode(' ', $without_words) ?? [];
    }

    public function setAuthor(CMediusers $author): void
    {
        $this->author = $author;
    }

    public function setPatient(CPatient $patient): void
    {
        $this->patient = $patient;
    }

    public function setAtc(string $atc): void
    {
        $this->atc = $atc;
    }

    public function setCim(string $cim): void
    {
        $this->cim = $cim;
    }

    public function setCcam(string $ccam): void
    {
        $this->ccam = $ccam;
    }

    public function getExpression(): string
    {
        $expression_words = null;

        switch ($this->policy) {
            case AdvancedSearchQueryBuilder::CONTAIN_WORDS:
                $expression_words .= $this->containWordsExpression();
                break;
            case AdvancedSearchQueryBuilder::EXACT_MATCH:
                $expression_words .= $this->exactMatch();
                break;
            case AdvancedSearchQueryBuilder::CONTAINS_A_WORD:
                $expression_words .= $this->containsAWord();
                break;
            default:
        }

        $expression_without_words = $this->withoutWords();

        $expressions_array = array_filter(
            [$expression_words, $expression_without_words]
        );

        $expressions = '(' . implode(') AND (', $expressions_array) . ')';

        if ($this->atc) {
            $expressions .= ' AND ' . $this->atc;
        }
        if ($this->cim) {
            $expressions .= ' AND ' . $this->cim;
        }
        if ($this->ccam) {
            $expressions .= ' AND ' . $this->ccam;
        }

        return $expressions;
    }

    private function containWordsExpression(): string
    {
        return implode(' AND ', $this->words);
    }

    private function exactMatch(): string
    {
        return '"' . implode(' ', $this->words) . '"';
    }

    private function containsAWord(): string
    {
        return implode(' OR ', $this->words);
    }

    private function withoutWords(): string
    {
        $words = array_map(
            function (string $word) {
                return 'NOT ' . $word;
            },
            $this->without_words ?? []
        );

        return implode(' OR ', $words);
    }
}
