{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="pmsi" script="reception" ajax=true}}

<script>
  Main.add(function() {
    Reception.form = 'selType';
  });
</script>

<table class="tbl me-recept-dossier-month" style="text-align: center;">
  <tr>
    <th class="title" colspan="5">
      <a style="display: inline" href="#1" onclick="$V(getForm('selType').date, '{{$lastmonth}}'); Reception.reloadAllReceptDossiers()">&lt;&lt;&lt;</a>
      {{$date|date_format:"%b %Y"}}
      <a style="display: inline" href="#1" onclick="$V(getForm('selType').date, '{{$nextmonth}}'); Reception.reloadAllReceptDossiers()">&gt;&gt;&gt;</a>
    </th>
  </tr>

  <tr>
    <th rowspan="2">Date</th>
  </tr>

  <tr>
    <th class="text">
      <a class="{{if !$tri_recept && !$tri_complet}}selected{{else}}selectable{{/if}}" title="Toutes les sorties" href="#"
         onclick="Reception.filterSortie(0, 0);">Sort.</a>
    </th>
    <th class="text">
      <a class="{{if $tri_recept && !$tri_complet}}selected{{else}}selectable{{/if}}" title="Dossiers réceptionnées" href="#"
         onclick="Reception.filterSortie(2, 0);">Recept.</a>
    </th>
    <th class="text">
      <a class="{{if !$tri_recept && $tri_complet}}selected{{else}}selectable{{/if}}" title="Dossiers complétés" href="#"
         onclick="Reception.filterSortie(0, 2);">Compl.</a>
    </th>
    <th class="text">
      <a class="{{if $tri_recept && !$tri_complet}}selected{{else}}selectable{{/if}}" title="Nombre de dossiers manquants" href="#"
         onclick="Reception.filterSortie(1, 0);">Manq.</a>
    </th>
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
        <a href="#1" onclick="Reception.reloadSortieDate(this, '{{$day|iso_date}}');" title="{{$day|date_format:$conf.longdate}}">
          <strong>
            {{$day|date_format:"%a"|upper|substr:0:1}}
            {{$day|date_format:"%d"}}
          </strong>
        </a>
      </td>
      <td {{if $day == $date}}style="font-weight: bold;"{{/if}}>
        {{assign var=num1 value=0}}
        {{if isset($counts.num1|smarty:nodefaults) && $counts.num1}}
          {{assign var=num1 value=$counts.num1}}
          {{$counts.num1}}
        {{else}}
          -
        {{/if}}
      </td>
      <td {{if $day == $date}}style="font-weight: bold;"{{/if}} class="{{if $num1 && $num1 > $counts.num2}}warning{{elseif $num1}}ok{{/if}}">
        {{assign var=num2 value=0}}
        {{if isset($counts.num2|smarty:nodefaults) && $counts.num2}}
          {{assign var=num2 value=$counts.num2}}
          {{$counts.num2}}
        {{else}}
          -
        {{/if}}
      </td>

      <td {{if $day == $date}}style="font-weight: bold;"{{/if}} class="{{if $num1 && $num1 > $counts.num3}}warning{{elseif $num1}}ok{{/if}}">
        {{if isset($counts.num3|smarty:nodefaults) && $counts.num3}}{{$counts.num3}}{{else}}-{{/if}}
      </td>
      <td {{if $day == $date}}style="font-weight: bold;"{{/if}}>
        {{math assign=num4 equation="x-y" x=$num1 y=$num2}}
        {{if $num4}}{{$num4}}{{else}}-{{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">Pas de sorties ce mois</td>
    </tr>
  {{/foreach}}
</table>