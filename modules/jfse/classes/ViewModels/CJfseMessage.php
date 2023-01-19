<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use DateTimeInterface;
use Ox\Core\CModelObject;
use Ox\Mediboard\Jfse\Api\Message;

class CJfseMessage extends CModelObject
{
    /** @var string */
    public $id;

    /** @var int The severity level of the message */
    public $level;

    /** @var string The class used to display the message in the view */
    public $level_class;

    /** @var string */
    public $description;

    /** @var string The medical act concerned by the message */
    public $concerned_act;

    /** @var int */
    public $source;

    /** @var string */
    public $source_library;

    /** @var string */
    public $type_id;

    /** @var bool */
    public $validation_message;

    /** @var int */
    public $rule;

    /** @var string */
    public $rule_id;

    /** @var bool */
    public $breakable_rule;

    /** @var string */
    public $rule_serial_id;

    /** @var string */
    public $diagnosis_code;

    /** @var string */
    public $diagnosis_module;

    /** @var int */
    public $diagnosis_level;

    /** @var string */
    public $type;

    /** @var string */
    public $forcing_type;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']                 = 'num';
        $props['level']              = 'num';
        $props['description']        = 'str';
        $props['concerned_act']      = 'str';
        $props['source']             = 'num';
        $props['source_library']     = 'str';
        $props['type_id']            = 'str';
        $props['validation_message'] = 'bool';
        $props['rule']               = 'num';
        $props['rule_id']            = 'str';
        $props['breakable_rule']     = 'bool';
        $props['rule_serial_id']     = 'str';
        $props['diagnosis_code']     = 'str';
        $props['diagnosis_module']   = 'str';
        $props['diagnosis_level']    = 'num';
        $props['type']               = 'str';
        $props['forcing_type']       = 'enum list|std|cc';

        return $props;
    }

    public static function getFromMessage(Message $message): self
    {
        $view_model = new self();
        $props      = $view_model->getProps();
        foreach ($props as $name => $prop) {
            $getter = self::guessGetterName($name);
            if (method_exists($message, $getter)) {
                $value = $message->$getter();
                if ($value instanceof DateTimeInterface) {
                    $value = $value->format('Y-m-d');
                } elseif (strpos($prop, 'bool') !== false) {
                    $value = $value ? '1' : '0';
                }

                $view_model->$name = $value;
            }
        }

        switch ($view_model->level) {
            case Message::ERROR:
                $view_model->level_class = 'error';
                break;
            case Message::WARNING:
                $view_model->level_class = 'warning';
                break;
            case Message::INFO:
            default:
                $view_model->level_class = 'info';
        }

        switch ($message->getRule()) {
            case Message::RULE_STD:
                $view_model->forcing_type = 'std';
                break;
            case Message::RULE_CC:
                $view_model->forcing_type = 'cc';
                break;
            default:
        }

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
