{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=rdvs_externe value=$sejour->_refs_rdv_externes}}
{{assign var=patient      value=$sejour->_ref_patient}}
<table class="tbl print_tasks me-no-align me-no-box-shadow">
  <tr>
    <th>{{mb_label class=CRDVExterne field=libelle}}</th>
    <th class="narrow">{{mb_label class=CRDVExterne field=date_debut}}</th>
    <th class="narrow">{{mb_label class=CRDVExterne field=duree}}</th>
    <th class="narrow">{{mb_label class=CRDVExterne field=statut}}</th>
    <th class="text">{{mb_label class=CRDVExterne field=commentaire}}</th>
  </tr>
  {{foreach from=$rdvs_externe item=_rdv}}
    <tr {{if $_rdv->statut == "annule"}}class="hatching"{{/if}}>
      <td style="cursor: help;">
        <span title="{{$_rdv->description}}">
          {{mb_value object=$_rdv field=libelle}}
        </span>
      </td>
      <td>{{mb_value object=$_rdv field=date_debut}}</td>
      <td>{{mb_value object=$_rdv field=duree}}</td>
      <td>{{mb_value object=$_rdv field=statut}}</td>
      <td>{{mb_value object=$_rdv field=commentaire}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">
        {{tr}}CRDVExterne.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
