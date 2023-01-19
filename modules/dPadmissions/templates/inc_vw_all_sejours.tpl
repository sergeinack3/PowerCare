{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="text-align: center;">
  <tr>
    <th class="title" colspan="{{if $current_m == "reservation"}}5{{else}}4{{/if}}">
      <a style="display: inline;" href="?m={{$current_m}}&tab=vw_sejours_validation&date={{$lastmonth}}">&lt;&lt;&lt;</a>
      {{$date|date_format:"%b %Y"}}
      <a style="display: inline;" href="?m={{$current_m}}&tab=vw_sejours_validation&date={{$nextmonth}}">&gt;&gt;&gt;</a>
    </th>
  </tr>
    
  <tr>
    <th rowspan="2">Date</th>
  </tr>

  <tr>
    <th class="text">
      <a class="{{if $recuse=='-1'}}selected{{else}}selectable{{/if}}" title="Séjours en attente" href="?m={{$current_m}}&tab=vw_sejours_validation&recuse=-1{{if $current_m == "reservation"}}&envoi_mail=0{{/if}}">
        Att.
      </a>
    </th>
    <th class="text">
      <a class="{{if $recuse=='0'}}selected{{else}}selectable{{/if}}" title="Séjours validés" href="?m={{$current_m}}&tab=vw_sejours_validation&recuse=0{{if $current_m == "reservation"}}&envoi_mail=0{{/if}}">
        Val.
      </a>
    </th>
    <th class="text">
      <a class="{{if $recuse=='1'}}selected{{else}}selectable{{/if}}" title="Séjours récusés" href="?m={{$current_m}}&tab=vw_sejours_validation&recuse=1{{if $current_m == "reservation"}}&envoi_mail=0{{/if}}">
        Rec.
      </a>
    </th>
    {{if $current_m == "reservation"}}
      <th class="text">
        <a class="{{if $envoi_mail=='1'}}selected{{else}}selectable{{/if}}" title="DHE avec envoi de mail" href="?m={{$current_m}}&tab=vw_sejours_validation&envoi_mail=1&recuse=-2">
          Mail
        </a>
      </th>
    {{/if}}
  </tr>

  {{foreach from=$days key=day item=counts}}
  <tr {{if $day == $date}}class="selected"{{/if}}>
    {{assign var=day_number value=$day|date_format:"%w"}}
    <td style="text-align: right;
      {{if array_key_exists($day, $bank_holidays)}}
        background-color: #fc0;
      {{elseif $day_number == '0' || $day_number == '6'}}
        background-color: #ccc;
      {{/if}}">
      <a href="?m={{$current_m}}&tab=vw_sejour_validation&date={{$day|iso_date}}" title="{{$day|date_format:$conf.longdate}}">
        <strong>
          {{$day|date_format:"%a"|upper|substr:0:1}}
          {{$day|date_format:"%d"}}
        </strong>
      </a>
    </td>
    <td {{if $recuse == "-1" && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if $counts.num1}}{{$counts.num1}}{{else}}-{{/if}}
    </td>
    <td {{if $recuse == "0" && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if $counts.num2}}{{$counts.num2}}{{else}}-{{/if}}
    </td>
    <td {{if $recuse == "1" && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if $counts.num3}}{{$counts.num3}}{{else}}-{{/if}}
    </td>
    {{if $current_m == "reservation"}}
      <td {{if $envoi_mail == "1" && $day == $date}}style="font-weight: bold;"{{/if}}>
        {{if $counts.num4}}{{$counts.num4}}{{else}}-{{/if}}
      </td>
    {{/if}}
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">Pas d'admission ce mois</td>
  </tr>
  {{/foreach}}
</table>