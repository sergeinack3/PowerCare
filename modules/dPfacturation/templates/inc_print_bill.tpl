{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  impression = function(){
    var form = document.printFactures;
    var factures_id = '';

    {{foreach from=$factures item=facture}}
      if (form.elements['{{$facture->_guid}}'].checked) {
        factures_id = factures_id+'| {{$facture->_id}}';
      }
    {{/foreach}}
    var url = new Url('facturation', 'vw_integration_compta');
    url.addElement(form.facture_class);
    url.addElement(form.type_pdf);
    url.addElement(form.tiers_soldant);
    url.addParam('uniq_checklist', '{{$uniq_checklist}}');
    url.addParam('factures', factures_id);
    url.requestModal();
  }
</script>
<form name="printFactures" action="" method="get">
  <input hidden="hidden" name="facture_class" value="{{$facture_class}}"/>
  <input hidden="hidden" name="type_pdf" value="impression"/>
  <input hidden="hidden" name="tiers_soldant" value="{{$tiers_soldant}}"/>
  <table class="form main tbl">
    <tr>
      <th class="title" colspan="11">{{tr}}CFacture-print|pl{{/tr}}</th>
    </tr>
    <tr>
      <th></th>
      <th class="category">{{mb_label class=CPatient field=nom}}</th>
      <th class="category">{{mb_label class=CPatient field=prenom}}</th>
      <th class="category">{{tr}}CFacture-type_sejour{{/tr}}</th>
      <th class="category">{{tr}}Date{{/tr}}</th>
      <th class="category">{{mb_label object=$facture field=type_facture}}</th>
      <th class="category">{{mb_label class=CSejour field=entree}}</th>
      <th class="category">{{mb_label class=CSejour field=sortie}}</th>
      <th class="category">{{tr}}CDebiteur{{/tr}}</th>
      <th class="category" style="width:60px;">{{tr}}CFacture.montant{{/tr}}</th>
    </tr>
    {{foreach from=$factures item=facture}}
      <tr>
        <td><input type="checkbox" name="{{$facture->_guid}}" value="{{$facture->_id}}" checked="checked"/></td>
        <td>{{mb_value object=$facture->_ref_patient field=nom}}</td>
        <td>{{mb_value object=$facture->_ref_patient field=prenom}}</td>
        <td>{{$facture}} {{mb_value object=$facture->_ref_last_sejour field=type}}</td>
        <td>{{mb_value object=$facture field=cloture}}</td>
        <td>{{mb_value object=$facture field=type_facture}}</td>
        <td>{{mb_value object=$facture->_ref_last_sejour field=entree format=$conf.date}}</td>
        <td>{{mb_value object=$facture->_ref_last_sejour field=sortie format=$conf.date}}</td>
        <td>
          {{mb_include module=system template=inc_vw_mbobject object=$facture->_assurance_patient}}
        </td>
        <td style="text-align: right;">{{$facture->_montant_avec_remise|string_format:"%0.2f"}}</td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="10" style="text-align: right;"><strong>{{mb_label class=CFactureEtablissement field=montant_total}}</strong></td>
      <td style="text-align: right;">{{$montant_total|string_format:"%0.2f"}}</td>
    </tr>
    <tr>
      <td colspan="11" class="button">
        <button class="print" type="button" onclick="impression();">{{tr}}Print{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>