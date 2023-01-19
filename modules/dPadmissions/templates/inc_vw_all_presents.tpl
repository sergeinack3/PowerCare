{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-small" style="text-align: center;">
  <tbody>
  {{foreach from=$days key=day item=counts}}
  <tr {{if $day == $date}}class="selected"{{/if}}>
    {{assign var=day_number value=$day|date_format:"%w"}}
    <td  style="text-align: right;
      {{if array_key_exists($day, $bank_holidays)}}
        background-color: #fc0;
      {{elseif $day_number == '0' || $day_number == '6'}}
        background-color: #ccc;
      {{/if}}">
      <a href="?m={{$m}}&tab=vw_idx_present&date={{$day|iso_date}}" title="{{$day|date_format:$conf.longdate}}">
        <strong>
          {{$day|date_format:"%a"|upper|substr:0:1}}
          {{$day|date_format:"%d"}}
        </strong>
      </a>
    </td>
    <td>{{if $counts}}{{$counts}}{{else}}-{{/if}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">Pas d'admission ce mois</td>
  </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th class="title" colspan="4">
      <a style="display: inline;" href="?m={{$m}}&tab=vw_idx_present&date={{$lastmonth}}">&lt;&lt;&lt;</a>
      {{$date|date_format:"%b %Y"}}
      <a style="display: inline;" href="?m={{$m}}&tab=vw_idx_present&date={{$nextmonth}}">&gt;&gt;&gt;</a>
    </th>
  </tr>

  <tr>
    <th>Date</th>
    <th>Présents</th>
  </tr>
  </thead>
</table>
