<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;

/**
 * Description
 */
class CUserAnswerResponse extends CMbObject
{
    /** @var string */
    public const RESOURCE_TYPE = 'userAnswerResponse';

    // Question pour AppFine
    //TODO : En dur dans la version mobile : En cas de modification il faut répercuter les changements
    /** @var int */
    public const QUESTION_1 = 1;
    /** @var int */
    public const QUESTION_2 = 2;
    /** @var int */
    public const QUESTION_3 = 3;
    /** @var int */
    public const QUESTION_4 = 4;
    /** @var int */
    public const QUESTION_5 = 5;

    // DB Table key
    /** @var int */
    public $user_answer_response_id;

    // DB References
    /** @var int */
    public $user_id;
    /** @var string */
    public $answer;
    /** @var string */
    public $response;
    /** @var bool */
    public $active;
    /** @var int */
    public $number_tests;

    // Fwd refs
    /** @var CUser */
    public $_ref_user;

    /**
     * Initialize the class specifications
     *
     * @return CMbFieldSpec
     */
    function getSpec()
    {
        $spec                     = parent::getSpec();
        $spec->table              = "user_answer_response";
        $spec->key                = "user_answer_response_id";
        $spec->uniques['user_id'] = ['user_id'];

        return $spec;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["user_id"]      = "ref notNull class|CUser back|answers_response fieldset|default";
        $props["answer"]       = "num maxLength|1 notNull fieldset|default";
        $props["response"]     = "str maxLength|250 fieldset|default";
        $props["active"]       = "bool notNull default|0 fieldset|default";
        $props["number_tests"] = "num notNull default|0 fieldset|default";

        return $props;
    }
}
