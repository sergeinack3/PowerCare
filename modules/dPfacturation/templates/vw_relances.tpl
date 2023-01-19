{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
function addRelances(facture_class, type_relance){
  var form = getForm("printFrm");
  if(!form.chir.value) {
    alert($T('Compta.choose_prat'));
    return false;
  }
  var relances = getForm("add-relances");
  relances.type_relance.value   = type_relance;
  relances.facture_class.value  = facture_class;
  relances._date_min.value      = form._date_min.value;
  relances._date_max.value      = form._date_max.value;
  relances.chir.value           = form.chir.value;
  relances.submit();
}
</script>

<form name="printRelance" action="?" method="get" onSubmit="return checkRapport()">
  <input type="hidden" name="a" value="" />
  <input type="hidden" name="dialog" value="1" />
  <table class="form me-no-align me-no-box-shadow">
    {{if "dPfacturation CFactureCabinet view_bill"|gconf}}
      <tr>
        <th class="category" colspan="5">{{tr}}CRelance.cabinet{{/tr}}</th>
      </tr>
      <tr>
        <td class="button" rowspan="2">
          <label for="typerelance_CFactureCabinet">{{tr}}CRelance.type{{/tr}}</label>
          <select name="typerelance_CFactureCabinet">
            <option value="1">{{tr}}CRelance.statut.first{{/tr}}</option>
            <option value="2">{{tr}}CRelance.statut.second{{/tr}}</option>
            <option value="3">{{tr}}CRelance.statut.third{{/tr}}</option>
          </select>
        </td>
        <td class="button">
          <button type="button" class="search" title="{{tr}}CFacture.to_relance.desc{{/tr}}"
                  onclick="ListeFacture.load('CFactureCabinet', this.form.typerelance_CFactureCabinet.value);">
            {{tr}}CFacture.to_relance{{/tr}}
          </button>
        </td>
        <td class="button">
          <label for="typereglement_CFactureCabinet">{{tr}}CReglement{{/tr}}</label>
          <select name="typereglement_CFactureCabinet">
          <option value="0">&mdash; {{tr}}common-all|pl{{/tr}}</option>
          <option value="1">{{tr}}CRelance.emise|pl{{/tr}}</option>
          <option value="2">{{tr}}CRelance.regle|pl{{/tr}}</option>
          <option value="3">{{tr}}CRelance.renouvele|pl{{/tr}}</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="button">
          <button type="button" class="add" onclick="addRelances('CFactureCabinet', this.form.typerelance_CFactureCabinet.value);">{{tr}}CRelance.generate|pl{{/tr}}</button>
        </td>
        <td class="button">
          <button type="button" class="search" onclick="ListeFacture.view('CFactureCabinet', this.form.typerelance_CFactureCabinet.value, this.form.typereglement_CFactureCabinet.value);">{{tr}}CRelance.see_all{{/tr}}</button>
        </td>
      </tr>
    {{/if}}
    {{if "dPfacturation CFactureEtablissement view_bill"|gconf}}
      <tr>
        <th class="category me-border-top-width-1" colspan="4">{{tr}}CRelance.etablissement{{/tr}}</th>
      </tr>
      <tr>
        <td class="button" rowspan="2">
          <label for="typerelance_CFactureEtablissement">{{tr}}CRelance.type{{/tr}}</label>
          <select name="typerelance_CFactureEtablissement">
            <option value="1">{{tr}}CRelance.statut.first{{/tr}}</option>
            <option value="2">{{tr}}CRelance.statut.second{{/tr}}</option>
            <option value="3">{{tr}}CRelance.statut.third{{/tr}}</option>
          </select>
        </td>
        <td class="button">
          <button type="button" class="search" title="{{tr}}CFacture.to_relance.desc{{/tr}}"
               onclick="ListeFacture.load('CFactureEtablissement', this.form.typerelance_CFactureEtablissement.value);">
            {{tr}}CFacture.to_relance{{/tr}}
          </button>
        </td>
        <td class="button">
          <label for="typereglement_CFactureEtablissement">{{tr}}CReglement{{/tr}}</label>
          <select name="typereglement_CFactureEtablissement">
          <option value="0">&mdash; {{tr}}common-all|pl{{/tr}}</option>
          <option value="emise">{{tr}}CRelance.emise|pl{{/tr}}</option>
          <option value="regle">{{tr}}CRelance.regle|pl{{/tr}}</option>
          <option value="renouvelle">{{tr}}CRelance.renouvele|pl{{/tr}}</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="button">
          <button type="button" class="add" onclick="addRelances('CFactureEtablissement', this.form.typerelance_CFactureEtablissement.value);">{{tr}}CRelance.generate|pl{{/tr}}</button>
        </td>
        <td class="button">
          <button type="button" class="search" onclick="ListeFacture.view('CFactureEtablissement', this.form.typerelance_CFactureEtablissement.value, this.form.typereglement_CFactureEtablissement.value);">{{tr}}CRelance.see_all{{/tr}}</button>
        </td>
      </tr>
    {{/if}}
  </table>
</form>
<form name="add-relances" action="" method="post" target="_blank">
  <input type="hidden" name="m" value="facturation" />
  <input type="hidden" name="dosql" value="do_relance_aed" />
  <input type="hidden" name="_date_min" value="" />
  <input type="hidden" name="_date_max" value="" />
  <input type="hidden" name="facture_class" value="" />
  <input type="hidden" name="type_relance" value="" />
  <input type="hidden" name="chir" value="" />
</form>
