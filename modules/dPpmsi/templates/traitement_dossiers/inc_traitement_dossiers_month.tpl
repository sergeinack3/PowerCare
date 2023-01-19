{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="pmsi" script="traitementDossiers" ajax=true}}

<table class="tbl" style="text-align: center;">
  <tr>
    <th class="title" colspan="4">
      <a style="display: inline" href="#1" onclick="$V(getForm('selType').date, '{{$lastmonth}}'); traitementDossiers.reloadAllTraitementDossiers(getForm('selType'))">&lt;&lt;&lt;</a>
      {{$date|date_format:"%b %Y"}}
      <a style="display: inline" href="#1" onclick="$V(getForm('selType').date, '{{$nextmonth}}'); traitementDossiers.reloadAllTraitementDossiers(getForm('selType'))">&gt;&gt;&gt;</a>
    </th>
  </tr>

  <tr>
    <th rowspan="2">Date</th>
  </tr>

  <tr>
    <th class="text">
      <a class="{{if !$tri_recept && !$tri_complet}}selected{{else}}selectable{{/if}}" title="{{tr}}CTraitementDossier-Sortie{{/tr}}" href="#"
         onclick="traitementDossiers.filterSortie(0, 0);">{{tr}}CTraitementDossier-Sortie-short{{/tr}}</a>
    </th>
    <th class="text">
      <a class="{{if $tri_recept && !$tri_complet}}selected{{else}}selectable{{/if}}" title="{{tr}}CTraitementDossier-Traitement{{/tr}}" href="#"
         onclick="traitementDossiers.filterSortie(1, 0);">{{tr}}CTraitementDossier-Traitement-short{{/tr}}</a>
    </th>
    <th class="text">
      <a class="{{if !$tri_recept && $tri_complet}}selected{{else}}selectable{{/if}}" title="{{tr}}CTraitementDossier-Validate{{/tr}}" href="#"
         onclick="traitementDossiers.filterSortie(0, 1);">{{tr}}CTraitementDossier-Validate-short{{/tr}}</a>
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
        <a href="#1" onclick="traitementDossiers.reloadSortieDate(this, '{{$day}}', getForm('selType'));" title="{{$day|date_format:$conf.longdate}}">
          <strong>
            {{$day|date_format:"%a"|upper|substr:0:1}}
            {{$day|date_format:"%d"}}
          </strong>
        </a>
      </td>
      <td {{if $day == $date}}style="font-weight: bold;"{{/if}}>
        {{assign var=sortie value=0}}
        {{if isset($counts.sortie|smarty:nodefaults) && $counts.sortie}}
          {{assign var=sortie value=$counts.sortie}}
          {{$counts.sortie}}
        {{else}}
          -
        {{/if}}
      </td>
      <td {{if $day == $date}}style="font-weight: bold;"{{/if}} class="{{if $sortie && $sortie > $counts.traitement}}warning{{elseif $sortie}}ok{{/if}}">
        {{if isset($counts.traitement|smarty:nodefaults) && $counts.traitement}}{{$counts.traitement}}{{else}}-{{/if}}
      </td>

      <td {{if $day == $date}}style="font-weight: bold;"{{/if}} class="{{if $sortie && $sortie > $counts.complet}}warning{{elseif $sortie}}ok{{/if}}">
        {{if isset($counts.complet|smarty:nodefaults) && $counts.complet}}{{$counts.complet}}{{else}}-{{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">Pas de sorties ce mois</td>
    </tr>
  {{/foreach}}
</table>