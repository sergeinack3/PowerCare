{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="list">
  {{if $result->result_id}}
    {{foreach from=$result->_siblings item=_sibling}}
      <li class="alternate" data-result_id="{{$_sibling->result_id}}" onclick="DRC.selectResult('{{$_sibling->result_id}}');">
        {{$_sibling->title}}
      </li>
    {{foreachelse}}
      <li style="font-style: italic; color: #aaa">
        {{tr}}CDRCConsultationResult-_siblings.none{{/tr}}
      </li>
    {{/foreach}}
  {{else}}
    <li style="font-style: italic; color: #aaa">
      {{tr}}CDRCConsultationResult-msg-no_selected_result{{/tr}}
    </li>
  {{/if}}
</ul>