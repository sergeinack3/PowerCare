{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-small">
  <tbody>
  {{foreach from=$days key=day item=count}}
    <tr class="preAdmission-day {{if $day == $date}}selected{{/if}}" id="paday_{{$day}}">
      {{assign var=day_number value=$day|date_format:"%w"}}
      <td style="text-align: right;
        {{if array_key_exists($day, $bank_holidays)}}
          background-color: #fc0;
        {{elseif $day_number == '0' || $day_number == '6'}}
          background-color: #ccc;
        {{/if}}">
        <a href="#" onclick="Admissions.updateListPreAdmissions('{{$day|iso_date}}', 0);">
          <strong>
            {{$day|date_format:"%a"|upper|substr:0:1}}
            {{$day|date_format:"%d"}}
          </strong>
        </a>
      </td>
      <td style="text-align: center;" {{if !$count.total}}class="empty"{{/if}}>
        {{$count.total}}
      </td>
    </tr>
  {{/foreach}}
  <tr>
    <td style="text-align: right;"><strong>Total</strong></td>
    <td style="text-align: center;"><strong>{{$total}}</strong></td>
  </tr>
  </tbody>
  <thead>
  <tr>
    <th class="title" colspan="4">
      <a style="display: inline;" href="?m={{$m}}&tab=vw_idx_preadmission&date={{$lastmonth}}">&lt;&lt;&lt;</a>
      {{$date|date_format:"%b %Y"}}
      <a style="display: inline;" href="?m={{$m}}&tab=vw_idx_preadmission&date={{$nextmonth}}">&gt;&gt;&gt;</a>
    </th>
  <tr>
    <th class="text">Date</th>
    <th class="text">Pré-ad.</th>
  </tr>
  </thead>
</table>
