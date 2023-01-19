{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math assign=colspan equation="5+x*5" x=$periods|@count}}

<table class="tbl">
  <tr>
    <th class=title" colspan="{{$colspan}}" style="background-color: #68c; color: #fff; font-size: 1.2em; font-weight: bold;">
      {{tr}}common-Statistic|pl{{/tr}}
    </th>
  </tr>
  <tr>
    <th rowspan="2" class="narrow">
      {{tr}}common-Practitioner{{/tr}}
    </th>
    {{foreach from=$periods key=_period item=_dates}}
      <th colspan="5" id="{{$_period}}" data-begin_date="{{$_dates.begin}}" data-end_date="{{$_dates.end}}">
        {{tr}}pmsi-title-stats_cotation.{{$_period}}{{/tr}}
      </th>
    {{/foreach}}
  </tr>
  <tr>
    {{foreach from=$periods item=_period}}
      <th class="section" style="text-transform: none;">
        <span title="{{tr}}pmsi-title-stats_cotation.no_codes-desc{{/tr}}" style="cursor: help;">
          {{tr}}pmsi-title-stats_cotation.no_codes{{/tr}}
        </span>
      </th>
      <th class="section" style="text-transform: none;">
        <span title="{{tr}}pmsi-title-stats_cotation.number_uncreated_acts-desc{{/tr}}" style="cursor: help;">
          {{tr}}pmsi-title-stats_cotation.number_uncreated_acts{{/tr}}
        </span>
      </th>
      <th class="section" style="text-transform: none;">
        <span title="{{tr}}pmsi-title-stats_cotation.total_uncreated_acts-desc{{/tr}}" style="cursor: help;">
          {{tr}}pmsi-title-stats_cotation.total_uncreated_acts{{/tr}}
        </span>
      </th>
      <th class="section" style="text-transform: none;">
        <span title="{{tr}}pmsi-title-stats_cotation.number_unexported_acts-desc{{/tr}}" style="cursor: help;">
          {{tr}}pmsi-title-stats_cotation.number_unexported_acts{{/tr}}
        </span>
      </th>
      <th class="section" style="text-transform: none;">
        <span title="{{tr}}pmsi-title-stats_cotation.total_unexported_acts-desc{{/tr}}" style="cursor: help;">
          {{tr}}pmsi-title-stats_cotation.total_unexported_acts{{/tr}}
        </span>
      </th>
    {{/foreach}}
  </tr>
  <tr>
    <td colspan="{{$colspan}}">
      {{mb_include module=system template=inc_pagination total=$results.nb_chirs current=$results.page change_page="changePage" step=20}}
    </td>
  </tr>
  <tbody id="stats_datas">
    {{foreach from=$results.chirs item=_result}}
      <tr class="alternate" class="data_row">
        <td class="narrow">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_result.chir}}
        </td>
        {{foreach from=$_result.periods key=_period item=_data}}
          <td class="narrow{{if $_data.no_codes == 0}} empty{{/if}}" style="text-align: right; cursor: pointer;" onclick="showDetailsFor('{{$_result.chir->_id}}', '{{$_period}}');">
            {{$_data.no_codes}}
          </td>
          <td class="narrow{{if $_data.uncreated_acts == 0}} empty{{/if}}" style="text-align: right; cursor: pointer;" onclick="showDetailsFor('{{$_result.chir->_id}}', '{{$_period}}');">
            {{$_data.uncreated_acts}}
          </td>
          <td class="narrow" style="text-align: right; cursor: pointer;" onclick="showDetailsFor('{{$_result.chir->_id}}', '{{$_period}}');">
            {{$_data.price_uncreated_acts|currency}}
          </td>
          <td class="narrow{{if $_data.unexported_acts == 0}} empty{{/if}}" style="text-align: right; cursor: pointer;" onclick="showDetailsFor('{{$_result.chir->_id}}', '{{$_period}}');">
            {{$_data.unexported_acts}}
          </td>
          <td class="narrow" style="text-align: right; cursor: pointer;" onclick="showDetailsFor('{{$_result.chir->_id}}', '{{$_period}}');">
            {{$_data.price_unexported_acts|currency}}
          </td>
        {{/foreach}}
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="{{$colspan}}" class="empty">
          {{tr}}COperation.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
  <tr>
    <td colspan="{{$colspan}}">
      {{mb_include module=system template=inc_pagination total=$results.nb_chirs current=$results.page change_page="changePage" step=20}}
    </td>
  </tr>
</table>