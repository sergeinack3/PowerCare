<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

use Ox\Core\CMbArray;

final class Question
{
    /** @var string */
    private $id;

    /** @var int */
    private $nature;

    /** @var string */
    private $question;

    /** @var int */
    private $type;

    /** @var array */
    private $possible_answers;

    /** @var string */
    private $answer;

    public function __construct(
        string $id,
        int $nature,
        string $question,
        int $type,
        array $possible_answers,
        ?string $answer
    ) {
        $this->id               = $id;
        $this->nature           = $nature;
        $this->question         = $question;
        $this->type             = $type;
        $this->possible_answers = $possible_answers;
        $this->answer           = $answer;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getNature(): int
    {
        return $this->nature;
    }

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getPossibleAnswers(): array
    {
        return $this->possible_answers;
    }

    /**
     * @return string
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     *
     * @return $this
     */
    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public static function map(array $data): self
    {
        return new self(
            CMbArray::get($data, 'id', ''),
            CMbArray::get($data, 'genre', 0),
            CMbArray::get($data, 'libelle', ''),
            CMbArray::get($data, 'type', 0),
            CMbArray::get($data, 'lstReponses', []),
            CMbArray::get($data, 'reponse', null)
        );
    }
}
