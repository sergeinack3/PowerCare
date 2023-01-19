{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$stats item=_stat}}
<tr>
  <td style="text-align: right;">{{$_stat.docs_count|integer}}</td>
  <td style="text-align: right;">{{$_stat._docs_count_percent|percent}}</td>
  <td style="text-align: right;">{{$_stat.docs_weight|decabinary}}</td>
  <td style="text-align: right;">{{$_stat._docs_weight_percent|percent}}</td>
  <td style="text-align: right;">{{$_stat._docs_average_weight|decabinary}}</td>
  {{if $is_doc}}
    <td class="me-text-align-center">{{$_stat.docs_average_read_time}}</td>
    <td class="me-text-align-center">{{$_stat.docs_average_write_time}}</td>
  {{/if}}
  {{assign var=owner value=$_stat._ref_owner}}
  {{if !$owner->_id}}
  <td class="empty" colspan="2">{{tr}}None{{/tr}}</td>
  {{else}}
  <td>
    {{if $owner|instanceof:'Ox\Mediboard\Mediusers\CMediusers'}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$owner}}
    {{elseif $owner|instanceof:'Ox\Mediboard\Mediusers\CFunctions'}}
      {{mb_include module=mediusers template=inc_vw_function function=$owner}}
    {{elseif $owner|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$owner->_guid}}');">{{$owner}}</span>
    {{else}}
      {{$_stat.user_first_name}} {{$_stat.user_last_name}}
    {{/if}}
  </td>
  <td>
    <button class="search notext compact me-tertiary me-dark" type="button" onclick="Details.statOwner('{{$doc_class}}', null, '{{$owner->_guid}}', null, null, null, '{{$factory}}');">
      {{tr}}Details{{/tr}}
    </button>
    <button class="stats notext compact me-tertiary" type="button" onclick="Details.statPeriodicalOwner('{{$doc_class}}', null, '{{$owner->_guid}}', null, null, null,'{{$factory}}' );">
      {{tr}}Periodical details{{/tr}}
    </button>
  </td>
  {{/if}}
</tr>

{{foreachelse}}
<tr>
  <td class="empty">{{tr}}CFile.none{{/tr}}</td>
</tr>
{{/foreach}}
