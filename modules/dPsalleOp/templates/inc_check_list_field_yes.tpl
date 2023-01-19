{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=name_checklist value=""}}

{{assign var=class value=$check_list->object_class}}
{{assign var=checked value=false}}
{{assign var=param_name value="default_good_answer_$class"}}
{{assign var=good value="dPsalleOp Default_good_answer $param_name"|gconf}}

{{if $curr_type->_checked == "yes"}}
  {{assign var=checked value=true}}
{{else}}
  {{if $curr_type->_checked === null && $default_value == $good|ternary:"yes":"no"}}
    {{assign var=checked value=true}}
  {{/if}}
{{/if}}

<label style="white-space: nowrap; {{if in_array($class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes)}}{{if $default_value == "no"}}font-style: italic;{{/if}} {{if $default_value == "yes"}}font-weight: bold;{{/if}}{{/if}}">
  <input type="radio" name="_items[{{$curr_type->_id}}]" value="yes" {{if $checked}}checked{{/if}}
         onclick="EditDailyCheck.submitCheckList(this.form, true)" />
  {{tr}}CDailyCheckItem.checked.yes{{/tr}}{{if !in_array($name_checklist, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_comments_other) && in_array($class, 'Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_classes) && $default_value == "no"}}*{{/if}}
</label>
