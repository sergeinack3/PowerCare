{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="category" colspan="3">{{tr}}CSejour-uf_soins_id-court{{/tr}}</th>
  </tr>
  {{foreach from=$ufs item=uf}}
    <tr>
      <td><span onmouseover="ObjectTooltip.createEx(this, '{{$uf->_guid}}')">{{mb_value object=$uf field=libelle}}</span></td>
      <td><strong>{{mb_value object=$uf field=type}}</strong></td>
      <td class="empty">
        {{if $uf->date_debut && $uf->date_fin}}
          ({{tr}}date.From{{/tr}} {{mb_value object=$uf field=date_debut}}
          {{tr}}date.to{{/tr}} {{mb_value object=$uf field=date_fin}})
        {{elseif $uf->date_debut}}
          ({{tr}}date.To_long{{/tr}} {{mb_value object=$uf field=date_fin}})
        {{elseif $uf->date_fin}}
          ({{tr}}date.From_long{{/tr}} {{mb_value object=$uf field=date_fin}})
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">{{tr}}CAffectationUniteFonctionnelle.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>