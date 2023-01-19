{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="follow_up_form" method="post" onsubmit="return false;">
  {{if $result->result_id}}
    <ul class="list" style="display: inline-block;">
      <li>
        <label><input type="radio" name="follow_up" value="N">{{tr}}CDRConsultationResult.follow_up.N{{/tr}}</label>
      </li>
      <li>
        <label><input type="radio" name="follow_up" value="P">{{tr}}CDRConsultationResult.follow_up.P{{/tr}}</label>
      </li>
      <li>
        <label><input type="radio" name="follow_up" value="R">{{tr}}CDRConsultationResult.follow_up.R{{/tr}}</label>
      </li>
    </ul>
    <ul class="list" style="display: inline-block;">
      <li>
        <label><input type="checkbox" name="asymptomatic" value="1">{{tr}}CDRConsultationResult-asymptomatic{{/tr}}</label>
      </li>
      <li>
        <label><input type="checkbox" name="ALD" value="1">{{tr}}CDRConsultationResult-ALD{{/tr}}</label>
      </li>
    </ul>
  {{else}}
    <ul class="list">
      <li style="font-style: italic; color: #aaa">
        {{tr}}CDRCConsultationResult-msg-no_selected_result{{/tr}}
      </li>
    </ul>
  {{/if}}
</form>