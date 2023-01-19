{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="list">
  {{foreach from=$results item=_result}}
    <li data-result_id="{{$_result.result_id}}" onclick="DRC.selectResult('{{$_result.result_id}}');">
      {{$_result.title}}
    </li>
  {{foreachelse}}
    <li style="font-style: italic; color: #aaa">
      {{tr}}CDRCConsultationResult.none{{/tr}}
    </li>
  {{/foreach}}
</ul>