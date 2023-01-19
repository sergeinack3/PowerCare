<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use DateTimeInterface;
use Ox\Core\CModelObject;
use Ox\Mediboard\Jfse\Api\Question;

class CJfseQuestion extends CModelObject
{
    /** @var string */
    public $id;

    /** @var int */
    public $nature;

    /** @var string */
    public $question;

    /** @var int */
    public $type;

    /** @var array */
    public $possible_answers;

    /** @var int */
    public $answer;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']            = 'str';
        $props['nature']        = 'num';
        $props['question']      = 'str';
        $props['type']          = 'num';
        $props['answer']        = 'num';

        return $props;
    }

    public static function getFromQuestion(Question $question): self
    {
        $view_model = new self();
        $props      = $view_model->getProps();
        foreach ($props as $name => $prop) {
            $getter = self::guessGetterName($name);
            if (method_exists($question, $getter)) {
                $view_model->$name = $question->$getter();
            }
        }

        $view_model->possible_answers = $question->getPossibleAnswers();

        return $view_model;
    }

    /**
     * Returns the getter method name from the property name
     *
     * @param string $property
     *
     * @return string
     */
    public static function guessGetterName(string $property): string
    {
        return 'get' . str_replace('_', '', ucwords($property, '_'));
    }
}
