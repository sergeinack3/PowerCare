{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=journal ajax=true}}

<script>
  printBill = function(){
    var form = document.printFrm;
    var formBill = document.printFacture;
    var url = new Url('facturation', 'ajax_print_bill');
    url.addElement(form._date_min);
    url.addElement(form._date_max);
    url.addParam('prat_id' , form.chir.value);
    url.addElement(formBill.facture_class);
    url.addElement(formBill.tri);
    url.addElement(formBill.facture_id);
    url.addElement(formBill.type_fact);
    url.addElement(formBill.tiers_soldant);
    url.addElement(formBill.uniq_checklist);
    url.requestModal();
  }
  integrationComptable = function(){
    var form = document.printFrm;
    var formBill = document.printFacture;
    var url = new Url('facturation', 'vw_integration_compta');
    url.addElement(form._date_min);
    url.addElement(form._date_max);
    url.addElement(formBill.facture_class);
    url.requestModal();
  }
  reprint = function() {
    var form = document.reprintFacture;
    var url = new Url('facturation', 'ajax_print_journal');
    url.addElement(form.uniq_checklist);
    url.addElement(form.journal_id);
    url.requestUpdate(SystemMessage.id);
  }
</script>
<table class="form me-no-box-shadow">
  <tr>
    <td style="width: 33%;" class="me-valign-top">
      <form name="printFacture" action="?" method="get">
        <table class="form">
          <tr>
            <th class="category" colspan="4">{{tr}}CFacture.print{{/tr}}</th>
          </tr>
          <tr {{if !"dPfacturation CFactureCabinet view_bill"|gconf || !"dPfacturation CFactureEtablissement view_bill"|gconf}} style="display:none;" {{/if}}>
            <th>
              <label for="facture_class">{{tr}}CFactureEtablissement.type_facture{{/tr}}</label>
            </th>
            <td>
              <select name="facture_class">
                {{if "dPfacturation CFactureCabinet view_bill"|gconf}}
                  <option value="CFactureCabinet">{{tr}}CFactureCabinet{{/tr}}</option>
                {{/if}}
                {{if "dPfacturation CFactureEtablissement view_bill"|gconf}}
                  <option value="CFactureEtablissement">{{tr}}CFactureEtablissement{{/tr}}</option>
                {{/if}}
              </select>
            </td>
          </tr>
          <tr>
            <th></th>
            <td>
              <label>
                <input type="checkbox" name="uniq_checklist" value="0"/>
                {{tr}}CFacture.only_list_control{{/tr}}
              </label>
            </td>
          </tr>
          <tr>
            <th>{{tr}}Order_by{{/tr}}</th>
            <td>
              <select name="tri">
                <option value="nom_patient">{{tr}}CPatient-nom-desc_court{{/tr}}</option>
                <option value="num_fact" selected="selected">{{tr}}CFactureCabinet-invoice-number{{/tr}}</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>{{tr}}CFactureEtablissement.type_facture{{/tr}}</th>
            <td>
              <select name="type_fact">
                <option value="" selected="selected">{{tr}}common-all|f|pl{{/tr}}</option>
                <option value="patient">{{tr}}CEditPdf.facture_patient{{/tr}}</option>
                <option value="garant">{{tr}}CEditPdf.facture_garant{{/tr}}</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>{{tr}}CFactureCabinet-invoice-number{{/tr}}</th>
            <td>
              <input type="" name="facture_id" value=""/>
            </td>
          </tr>
          <tr>
            <th></th>
            <td>
              <label>
                <input type="checkbox" name="tiers_soldant" value="0"/>
                {{tr}}CFacture.redirect_patient{{/tr}}
              </label>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button class="print" type="button" onclick="printBill();">{{tr}}CFactureEtablissement.print{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
    <td style="width: 33%;" class="me-valign-top">
      <form name="reprintFacture" action="?" method="get">
        <table class="form">
          <tr>
            <th class="category" colspan="4">{{tr}}CFacture.print_bis{{/tr}}</th>
          </tr>
          <tr>
            <th style="width: 50%;">{{tr}}CJournalBill{{/tr}}</th>
            <td>

              <input type="hidden" name="journal_id" />
              <input type="text" name="name_journal" style="width: 13em;" value=""/>
              <script>
                Main.add(function () {
                  var form = getForm("reprintFacture");
                  var url = new Url("system", "ajax_seek_autocomplete");
                  url.addParam("object_class", "CJournalBill");
                  url.addParam("field", "journal_id");
                  url.addParam("view_field", "nom");
                  url.addParam("where[type]", "debiteur");
                  url.addParam("input_field", "name_journal");
                  url.autoComplete(form.elements.name_journal, null, {
                    minChars: 0,
                    method: "get",
                    select: "view",
                    dropdown: true,
                    afterUpdateElement: function(field,selected){
                      $V(field.form.journal_id, selected.getAttribute("id").split("-")[2]);
                      $V(field.form.elements.name_journal, selected.down('.view').innerHTML);
                    }
                  });
                });
              </script>
            </td>
          </tr>
          <tr>
            <th></th>
            <td>
              <label>
                <input type="checkbox" name="uniq_checklist" value="0"/>
                {{tr}}CFacture.only_list_control{{/tr}}
              </label>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button class="print" type="button" onclick="reprint();">{{tr}}CFacture.print_bis.court{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
    <td class="me-valign-top">
      <form name="OtherFacture" action="?" method="get">
        <table class="form">
          <tr>
            <th class="category" colspan="4">{{tr}}Other{{/tr}}</th>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button type="button" class="print" onclick="Journal.viewJournal('all-paiement');">
                {{tr}}CJournalBill.type.paiement-small{{/tr}}
              </button>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button type="button" class="print" onclick="integrationComptable();">{{tr}}CFacture.inte_compta{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>