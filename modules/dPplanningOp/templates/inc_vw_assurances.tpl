{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture ajax=true}}
<tr>
  <th colspan="4" class="category me-text-align-left me-h5">
    {{tr}}CSejour-_assurance_maladie{{/tr}}
    {{if $sejour->_ref_factures|@count && $sejour->_ref_facture->_id}}
      <button style="float:right;" type="button" class="search me-secondary" onclick="Facture.gestionFacture('{{$sejour->_id}}');">
      {{tr}}CFacture|pl{{/tr}}
      </button>
    {{/if}}
  </th>
</tr>
<tr>
  <th>{{mb_label object=$sejour field=_type_sejour}}</th>
  <td>{{mb_field object=$sejour field=_type_sejour onchange="Value.synchronize(this, 'editSejour');"}}</td>
  <th>{{mb_label object=$sejour field=_dialyse}}</th>
  <td>{{mb_field object=$sejour field=_dialyse onchange="Value.synchronize(this, 'editSejour', false);"}}</td>
</tr>
<tr>
  <th>{{mb_label object=$sejour field=_statut_pro}}</th>
  <td>{{mb_field object=$sejour field=_statut_pro emptyLabel="Choisir un status" onchange="Value.synchronize(this, 'editSejour');"}}</td>
  <th>{{mb_label object=$sejour field=_cession_creance}}</th>
  <td>{{mb_field object=$sejour field=_cession_creance onchange="Value.synchronize(this, 'editSejour', false);"}}</td>
</tr>
<script>
  Main.add(function(){
    var form = getForm('{{$form}}');
    var urlmaladie = new Url('dPpatients', 'ajax_correspondant_autocomplete');
    urlmaladie.addParam('patient_id', '{{$sejour->patient_id}}');
    urlmaladie.addParam('type', '_assurance_maladie_view');
    urlmaladie.autoComplete(form._assurance_maladie_view, null, {
      minChars: 0,
      dropdown: true,
      select: "newcode",
      updateElement: function(selected) {
        $V(form._assurance_maladie_view, selected.down(".newcode").getText(), false);
        $V(form._assurance_maladie, selected.down(".newcode").get("id"), false);
        {{if $form == "editOpEasy"}}
        var form2 = getForm('editSejour');
        $V(form2._assurance_maladie, selected.down(".newcode").get("id"), false);
        {{/if}}
      }
    });
  });
</script>
<tr>
  <th>{{mb_label object=$sejour field=_assurance_maladie}}</th>
  <td colspan="3">
    <input type="hidden" name="_assurance_maladie" value="{{if $sejour->_assurance_maladie}}{{$sejour->_assurance_maladie->_id}}{{/if}}"/>
    <input type="text" name="_assurance_maladie_view" value="{{if $sejour->_assurance_maladie}}{{$sejour->_assurance_maladie->nom}}{{/if}}"/>
    {{if $sejour->patient_id}}
      <button type="button" class="add notext" onclick="Correspondant.edit(0, '{{$patient->_id}}');">
        {{tr}}CCorrespondant-title-create{{/tr}}
      </button>
    {{/if}}
  </td>
</tr>
<tr>
  <th>{{mb_label object=$sejour field=_rques_assurance_maladie}}</th>
  <td colspan="3">
    {{mb_field object=$sejour field="_rques_assurance_maladie" onchange="Value.synchronize(this, 'editSejour');checkAssurances();"
    form="editSejour" aidesaisie="validateOnBlur: 0"}}
  </td>
</tr>