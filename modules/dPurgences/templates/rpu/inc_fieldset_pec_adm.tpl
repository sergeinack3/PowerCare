{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modal value=0}}

{{assign var=pays value=$conf.ref_pays}}

<script>
  changeModeEntree = function(mode_entree) {
    loadTransfert(mode_entree);
    loadServiceMutation(mode_entree);
  };

  loadTransfert = function(mode_entree) {
    $('etablissement_entree_transfert').setVisible(mode_entree == 7);
    {{if "dPurgences CRPU required_from_when_transfert"|gconf}}
    var oform = getForm('editRPU');
    var provenance = $(oform._provenance);
    if (mode_entree == 7) {
      provenance.addClassName('notNull');
      $('labelFor_editRPU__provenance').addClassName('notNull');
    }
    else {
      provenance.removeClassName('notNull');
      $('labelFor_editRPU__provenance').removeClassName('notNull');
    }
    {{/if}}
  };

  loadServiceMutation = function(mode_entree) {
    $('service_entree_mutation').setVisible(mode_entree == 6);
  };

  changePecTransport = function(transport) {
    var pec_transport = transport.form.elements.pec_transport,
    def_pec_transport = {{"dPurgences pec_transport"|gconf|@json_encode|html_entity_decode}};
    if (def_pec_transport[transport.value]) {
      $V(pec_transport, def_pec_transport[transport.value]);
    }
  };

  changeProvenanceWithEntree = function(entree) {
    {{if "dPurgences CRPU provenance_domicile_pec_non_org"|gconf}}
    if (entree.value === "8") {
      $V(entree.form.elements._provenance, "5");
    }
    {{/if}}
  };
</script>

{{if $modal}}
  <button type="button" class="search" onclick="Modal.open('pec_adm_div')">{{tr}}CRPU-pec_adm{{/tr}}</button>
{{/if}}

{{if $modal}}
<div id="pec_adm_div" style="display: none;">
{{else}}
<fieldset class="me-small">
  <legend>{{tr}}CRPU-pec_adm{{/tr}}</legend>
{{/if}}

  <table class="form me-no-align me-no-box-shadow me-small-form">
    {{if $modal}}
    <tr>
      <th colspan="2" class="title">
        <button type="button" class="cancel notext" onclick="Control.Modal.close();" style="float: right;">{{tr}}Close{{/tr}}</button>
        {{tr}}CRPU-pec_adm{{/tr}}
      </th>
    </tr>
    {{/if}}
    <tr>
      <th style="width: 10em;">
        {{mb_include module=patients template=inc_button_pat_anonyme form=editRPU patient_id=$rpu->_patient_id input_name="_patient_id"}}

        <input type="hidden" name="_patient_id" class="{{$sejour->_props.patient_id}}" ondblclick="PatSelector.init()"
               value="{{$rpu->_patient_id}}"  onchange="requestInfoPat(); {{$submit_ajax}}" />
        {{mb_label object=$rpu field="_patient_id"}}
      </th>
      <td>
        <input type="text" name="_patient_view" style="width: 15em;" value="{{$patient->_view}}"
          {{if $conf.dPurgences.allow_change_patient || !$sejour->_id || $app->user_type == 1}}
            onfocus="PatSelector.init()"
          {{/if}}
               readonly="readonly" />

        {{if $conf.dPurgences.allow_change_patient || !$sejour->_id || $app->user_type == 1}}
          <button type="button" class="search notext" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
        {{/if}}
        <script>
          PatSelector.init = function(){
            this.sForm = "editRPU";
            this.sId   = "_patient_id";
            this.sView = "_patient_view";
            this.pop();
          }
        </script>
        {{if $patient->_id}}
          <button id="button-edit-patient" type="button" class="edit notext"
                  onclick="location.href='?m=patients&tab=vw_edit_patients&patient_id='+this.form._patient_id.value"
          >
            {{tr}}Edit{{/tr}}
          </button>

          <button id="button-edit-corresp" type="button"
                  onclick="Patient.editModal($V(this.form._patient_id), 0, null, null, 'correspondance');" class="search">
            Corresp.
          </button>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$rpu field="_entree"}}</th>
      <td>{{mb_field object=$rpu field="_entree" form="editRPU" register=true onchange=$submit_ajax}}</td>
    </tr>

    <tr>
      {{if $list_mode_entree|@count}}
        <th>{{mb_label object=$rpu field=_mode_entree_id}}</th>
        <td>
          {{mb_field object=$sejour field=mode_entree onchange="ContraintesRPU.updateProvenance(this.value, true); changeModeEntree(this.value)" hidden=true}}

          <input type="hidden" name="_mode_entree_id" value="{{$rpu->_mode_entree_id}}" onchange="{{$submit_ajax}}"
                 class="autocomplete notNull" size="50"/>
          <input type="text" name="_mode_entree_id_autocomplete_view" size="50" value="{{if $rpu->_mode_entree_id}}{{$rpu->_fwd._mode_entree_id}}{{/if}}"
                 class="autocomplete" onchange='if(!this.value){this.form["_mode_entree_id"].value=""}' />

          <script>
            Main.add(function(){
              var form = getForm("editRPU");
              var input = form._mode_entree_id_autocomplete_view;
              var url = new Url("system", "httpreq_field_autocomplete");
              url.addParam("class", "CRPU");
              url.addParam("field", "_mode_entree_id");
              url.addParam("limit", 50);
              url.addParam("view_field", "libelle");
              url.addParam("show_view", false);
              url.addParam("input_field", "_mode_entree_id_autocomplete_view");
              url.addParam("wholeString", true);
              url.addParam("min_occurences", 1);
              url.autoComplete(input, "_mode_entree_id_autocomplete_view", {
                minChars: 1,
                method: "get",
                select: "view",
                dropdown: true,
                afterUpdateElement: function(field, selected){
                  $V(field.form["_mode_entree_id"], selected.getAttribute("id").split("-")[2]);
                  var elementFormRPU = getForm("editRPU").elements;
                  var selectedData = selected.down(".data");
                  $V(elementFormRPU.mode_entree, selectedData.get("mode"));
                  $V(elementFormRPU._provenance, selectedData.get("provenance"));
                },
                callback: function(element, query){
                  query += "&where[group_id]={{$g}}";
                  query += "&where[actif]=1";
                  return query;
                }
              });
            });
          </script>
        </td>
      {{else}}
        <th>{{mb_label object=$rpu field="_mode_entree"}}</th>
        <td>
          {{mb_field object=$rpu field="_mode_entree" style="width: 15em;" emptyLabel="Choose"
          onchange="ContraintesRPU.updateProvenance(this.value, true); changeModeEntree(this.value); changeProvenanceWithEntree(this); $submit_ajax"}}
        </td>
      {{/if}}
    </tr>
    <tr>
      <th></th>
      <td>
        <input type="hidden" name="group_id" value="{{$g}}" />
        <div id="etablissement_entree_transfert" {{if !$rpu->_etablissement_entree_id}}style="display:none"{{/if}}>
          {{mb_field object=$rpu field="_etablissement_entree_id" hidden=true onchange=$submit_ajax}}
          <input type="text" name="_etablissement_entree_id_view" value="{{$sejour->_ref_etablissement_provenance}}" style="width: 12em;"/>

          <script>
            Main.add(function() {
              var url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
              url.addParam('field', '_etablissement_entree_id');
              url.addParam('input_field', '_etablissement_entree_id_view');
              url.addParam('view_field', 'nom');
              url.autoComplete(getForm('editRPU')._etablissement_entree_id_view, null, {
                minChars: 0,
                method: 'get',
                select: 'view',
                dropdown: true,
                afterUpdateElement: function(field, selected) {
                  var id = selected.getAttribute("id").split("-")[2];
                  $V(getForm('editRPU')._etablissement_entree_id, id);
                  if ($('editRPU__provenance')) {
                    $V(getForm('editRPU')._provenance, selected.down('span').get('provenance'));
                  }
                }
              });
            });
          </script>
        </div>
        <div id="service_entree_mutation" {{if !$rpu->_service_entree_id}}style="display:none"{{/if}}>
          {{mb_field object=$rpu field="_service_entree_id" form="editRPU" autocomplete="true,1,50,true,true" onchange=$submit_ajax}}
          <input type="hidden" name="cancelled" value="0" />
        </div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$rpu field="_transport"}}</th>
      <td>
        {{mb_field object=$rpu field="_transport"  emptyLabel="Choose" onchange="changePecTransport(this);$submit_ajax" style="width: 15em;"}}

        {{mb_field object=$rpu field="commentaire" form="editRPU" onchange=$submit_ajax}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$rpu field="pec_transport"}}</th>
      <td>{{mb_field object=$rpu field="pec_transport" canNull=false emptyLabel="Choose" style="width: 15em;" onchange=$submit_ajax}}</td>
    </tr>

      {{assign var="provenance" value=""}}
      {{if "dPurgences CRPU provenance_necessary"|gconf}}
        {{assign var="provenance" value="notNull"}}
      {{/if}}
      <th>{{mb_label object=$rpu field="_provenance" class=$provenance}}</th>
      <td>{{mb_field object=$rpu field="_provenance" class=$provenance emptyLabel="Choose" style="width: 15em;" onchange=$submit_ajax}}</td>


    {{if $conf.dPurgences.display_regule_par}}
      <tr>
        <th>{{mb_label object=$rpu field="regule_par"}}</th>
        <td>{{mb_field object=$rpu field="regule_par" emptyLabel="Choose" onchange=$submit_ajax}}</td>
      </tr>
    {{/if}}
  </table>

{{if $modal}}
</div>
{{else}}
</fieldset>
{{/if}}
