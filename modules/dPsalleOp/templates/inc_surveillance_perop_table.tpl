{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" style="height: 20px;">
      {{$table}}
    </th>
    {{foreach from=$table->_timings item=_datetime}}
      <th style="height: 20px;">
        {{$_datetime|date_format:"%H:%M"}}
      </th>
    {{foreachelse}}
      <th style="height: 20px;"></th>
    {{/foreach}}
  </tr>
  {{foreach from=$table->_ref_rows item=_row}}
    <tr>
      <th class="row-title" style="height: 16px;{{if $_row->color}} color: #{{$_row->color}}{{/if}}">
        {{$_row}}
      </th>
      {{foreach from=$_row->_data item=values}}
        <td style="height: 16px;{{if $_row->color}} color: #{{$_row->color}}{{/if}}">
          {{if $values.value !== null}}
            {{$values.value}}
          {{/if}}
        </td>
      {{foreachelse}}
        <td class="empty" style="height: 16px;">
          {{tr}}CSupervisionTable-No data{{/tr}}
        </td>
      {{/foreach}}
    </tr>
  {{/foreach}}
</table>
