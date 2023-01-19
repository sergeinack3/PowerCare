{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    if (navigator.appVersion.indexOf("Chrome/") === -1) {
      var div_permissions =  $("allPermissions");
      if (div_permissions && div_permissions.clientHeight < div_permissions.scrollHeight) {
        div_permissions.style.paddingRight = '16px';
      }
    }
  });
</script>

<table class="tbl me-small" style="text-align: center;">
  <thead>
  <tr>
    <th class="title" colspan="4">
      <a style="display: inline;" href="?m={{$m}}&tab=vw_idx_permissions&date={{$lastmonth}}">&lt;&lt;&lt;</a>
      {{$date|date_format:"%b %Y"}}
      <a style="display: inline;" href="?m={{$m}}&tab=vw_idx_permissions&date={{$nextmonth}}">&gt;&gt;&gt;</a>
    </th>
  </tr>
  <tr>
    <th rowspan="2">Date</th>
  </tr>
  <tr>
    <th class="text">
      <a class="{{if $type_externe == 'depart'}}selected{{else}}selectable{{/if}}" title="Départs" href="?m={{$m}}&tab=vw_idx_permissions&type_externe=depart">
        Départ
      </a>
    </th>
    <th class="text">
      <a class="{{if $type_externe == 'retour'}}selected{{else}}selectable{{/if}}" title="Retours" href="?m={{$m}}&tab=vw_idx_permissions&type_externe=retour">
        Retour
      </a>
    </th>
  </tr>
  </thead>
  <tbody>
  {{foreach from=$days key=day item=counts}}
  <tr {{if $day == $date}}class="selected"{{/if}}>
    {{assign var=day_number value=$day|date_format:"%w"}}
    <td style="text-align: right;
      {{if array_key_exists($day, $bank_holidays)}}
        background-color: #fc0;
      {{elseif $day_number == '0' || $day_number == '6'}}
        background-color: #ccc;
      {{/if}}">
      <a href="?m={{$m}}&tab=vw_idx_permissions&date={{$day|iso_date}}" title="{{$day|date_format:$conf.longdate}}">
        <strong>
          {{$day|date_format:"%a"|upper|substr:0:1}}
          {{$day|date_format:"%d"}}
        </strong>
      </a>
    </td>
    <td {{if $type_externe == "depart" && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if $counts.num1}}{{$counts.num1}}{{else}}-{{/if}}
    </td>
    <td {{if $type_externe == "retour"  && $day == $date}}style="font-weight: bold;"{{/if}}>
      {{if $counts.num2}}{{$counts.num2}}{{else}}-{{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">Pas de permissions ce mois</td>
  </tr>
  {{/foreach}}
  </tbody>
</table>
