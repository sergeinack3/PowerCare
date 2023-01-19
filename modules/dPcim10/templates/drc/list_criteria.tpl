{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="list">
  {{if $result->result_id}}
    <li style="font-weight: bold;">
      {{tr}}CDRCCriterion-list{{/tr}}
    </li>
    {{foreach from=$result->_criteria item=criterion}}
      {{mb_include module=cim10 template=drc/criterion}}
    {{foreachelse}}
      <li style="font-style: italic; color: #aaa">
        {{tr}}CDRCConsultationResult-_criteria.none{{/tr}}
      </li>
    {{/foreach}}
  {{else}}
    <li style="font-style: italic; color: #aaa">
      {{tr}}CDRCConsultationResult-msg-no_selected_result{{/tr}}
    </li>
  {{/if}}
</ul>