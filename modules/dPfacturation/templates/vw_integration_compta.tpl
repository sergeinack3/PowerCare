{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="width: 50%;">
  <tr>
    <th class="title">{{tr}}Month{{/tr}}</th>
    <th class="title">{{tr}}CFacture-nb|pl{{/tr}}</th>
    <th class="title">{{tr}}CFacture.inte_compta{{/tr}}</th>
  </tr>
  {{foreach from=$mois item=factures key=nom_mois}}
    <tr>
      <th>{{$nom_mois|ucfirst}}</th>
      <td style="text-align: center">{{$factures.factures|@count}}</td>
      <td style="text-align: center">
        <form name="get_compta_csv" method="get" target="_blank">
          <input type="hidden" name="m" value="dPfacturation" />
          <input type="hidden" name="a" value="ajax_export_compta" />
          <input type="hidden" name="suppressHeaders" value="1" />
          <input type="hidden" name="dialog" value="1" />
          <input type="hidden" name="facture_class" value="{{$facture_class}}" />
          <input type="hidden" name="factures" value="{{$factures.factures_id}}" />
          <button type="button" class="agenda notext" onclick="this.form.submit();"></button>
        </form>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">{{tr}}CFactureEtablissement.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>