{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  modifRepartition = function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        var url = new Url('facturation', 'ajax_view_facture');
        url.addParam('facture_id'   , '{{$facture->_id}}');
        url.addParam('object_class', '{{$facture->_class}}');
        url.requestUpdate("load_facture");
        Control.Modal.close();
      }}
    );
  }

  modifDuPatient = function() {
    var form = getForm("Edit-repartitionFacture");
    var total = $V(form.montant_total);
    var du_patient = $V(form.du_patient);
    $V(form.du_tiers, Math.round((total - du_patient)*100)/100);
  }
  modifDusTiers = function() {
    var form = getForm("Edit-repartitionFacture");
    var total = $V(form.montant_total);
    var du_tiers = $V(form.du_tiers);
    $V(form.du_patient, Math.round((total - du_tiers)*100)/100);
  }
</script>

<form name="Edit-repartitionFacture" action="?m={{$m}}" method="post" onsubmit="modifRepartition(this);">
  {{mb_key    object=$facture}}
  {{mb_class  object=$facture}}
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="montant_total" value="{{$montant_total}}"/>
  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}CFacture-repartition_montant{{/tr}} {{$facture->_view}}<br/> {{tr}}date.from{{/tr}} {{mb_value object=$facture field=ouverture}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$facture field=du_patient}}</th>
      <td>{{mb_field object=$facture field=du_patient onchange="modifDuPatient();"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$facture field=du_tiers}}</th>
      <td>{{mb_field object=$facture field=du_tiers onchange="modifDusTiers();"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        <button class="save" type="button" onclick="return modifRepartition(this.form);">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{if $facture->_ref_consults|@count == 1}}
  <form name="modif-repartitionConsult" action="?m={{$m}}" method="post">
    {{mb_key    object=$consult}}
    {{mb_class  object=$consult}}
    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="du_patient" value=""/>
    <input type="hidden" name="du_tiers" value=""/>
  </form>
{{/if}}