{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-box-shadow">
  <tr>
    <th class="title" colspan="2">{{tr}}CObjectifSoins-late{{/tr}}</th>
  </tr>
  <tr>
    <th>
      {{mb_label class=CObjectifSoin field="libelle"}}
    </th>
    <th class="narrow">
      {{mb_label class=CObjectifSoin field="delai"}}
    </th>
  </tr>
  {{foreach from=$listObjectifsSoins item=_objectif}}
    <tr {{if $_objectif->statut != "ouvert"}}style="opacity: 0.8" class="hatching"{{/if}}>
      <td>{{mb_value object=$_objectif field=libelle}}</td>
      <td>{{mb_value object=$_objectif field=delai}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">{{tr}}CObjectifSoin.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>