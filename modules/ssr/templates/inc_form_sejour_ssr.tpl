{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=pat_selector}}

<script>
ProtocoleSelector.init = function(do_not_pop){
  this.sForSejour         = true;
  this.sForm              = "editSejour";
  this.sChir_id           = "praticien_id";
  this.sLibelle_sejour    = "libelle";
  this.sForceType         = "{{$m}}";
  this.sDuree_prevu       = "_duree_prevue";
  this.sRques_sej         = "rques";
  this.sUf_soins_id       = "uf_soins_id";
  this.sCharge_id         = "charge_id";
  this.sUf_medicale_id    = "uf_medicale_id";
  this.sUf_hebergement_id = "uf_hebergement_id";
  if (!do_not_pop) {
    this.pop();
  }
};

function updateSortiePrevue() {
  var oForm = document.editSejour;
    
  if (!oForm._duree_prevue.value) {
    $V(oForm._duree_prevue, 0);
  }
  
  var sDate = oForm.entree_prevue.value;
  if (!sDate) {
    return;
  }
  
  // Add days
  var dDate = Date.fromDATETIME(sDate);
  var nDuree = parseInt(oForm._duree_prevue.value, 10);
  dDate.addDays(nDuree);

  // Update fields
  $V(oForm.sortie_prevue,    dDate.toDATETIME());
  $V(oForm.sortie_prevue_da, dDate.toLocaleDateTime());
}

function updateDureePrevue() {
  var oForm = document.editSejour;
  
  if(oForm.entree_prevue.value) {
    var dEntree = Date.fromDATETIME(oForm.entree_prevue.value);
    var dSortie = Date.fromDATETIME(oForm.sortie_prevue.value);
    var iSecondsDelta = dSortie - dEntree;
    var iDaysDelta = iSecondsDelta / (24 * 60 * 60 * 1000);
    oForm._duree_prevue.value = Math.floor(iDaysDelta);
  }
}

function cancelSejourSSR() {
  var oForm = document.editSejour;
  var oElement = oForm.annule;
  
  if (oElement.value == "0") {
    if (confirm($T('CSejourSSR-alert_cancel'))) {
      oElement.value = "1";
      oForm.submit();
      return;
    }
  }
      
  if (oElement.value == "1") {
    if (confirm($T('CSejourSSR-restore'))) {
      oElement.value = "0";
      oForm.submit();
      return;
    }
  }
}

function printFormSejour() {
  var url = new Url;
  url.setModuleAction("dPplanningOp", "view_planning"); 
  url.addParam("sejour_id", $V(getForm("editSejour").sejour_id));
  url.popup(700, 500, "printSejour");
  return;
}
</script>

<form name="editSejour" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  {{if $conf.ssr.recusation.sejour_readonly}}<input type="hidden" name="_locked" value="1" />{{/if}}
  {{mb_key object=$sejour}}
  {{mb_class object=$sejour}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="annule" value="{{$sejour->annule|default:"0"}}" />
  <input type="hidden" name="type" value="{{$m}}" />
  {{mb_field object=$sejour field=group_id          hidden=true}}
  {{mb_field object=$sejour field=uf_soins_id       hidden=true}}
  {{mb_field object=$sejour field=charge_id         hidden=true}}
  {{mb_field object=$sejour field=uf_medicale_id    hidden=true}}
  {{mb_field object=$sejour field=uf_hebergement_id hidden=true}}

  {{if !$sejour->annule}}
    <input type="hidden" name="recuse" value="{{if $conf.ssr.recusation.use_recuse && !$sejour->_id}}-1{{else}}{{$sejour->recuse}}{{/if}}"/>
  {{/if}}

  <input type="hidden" name="_bind_sejour" value="1" />

  <table style="display: none" class="main me-table">
    <tr>
      <td class="separator expand" onclick="MbObject.toggleColumn(this, $('tableFormSejourSSR'));" style="padding: 0;"></td>
    </tr>
  </table>
  <table id="tableFormSejourSSR" class="form me-small-form">
    <tr>
      {{if $sejour->_id}}
      <th class="title modify text" colspan="8">
        {{mb_include module=system template=inc_object_notes      object=$sejour}}
        {{mb_include module=system template=inc_object_idsante400 object=$sejour}}
        {{mb_include module=system template=inc_object_history    object=$sejour}}
  
        <a class="action" style="float: right;" title="Modifier uniquement le sejour" href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
          {{me_img src="edit.png" icon="edit" class="me-primary" alt="modifier"}}
        </a>
        {{tr}}CSejour-title-modify{{/tr}}
        {{mb_include module=system template=inc_vw_mbobject object=$sejour}}
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      </th>
      {{else}}
      <th class="title me-th-new" colspan="8">
        <button type="button" class="search" style="float: left;" onclick="ProtocoleSelector.init();">
          {{tr}}button-COperation-choixProtocole{{/tr}}
        </button>
        
        {{tr}}CSejour-title-create{{/tr}} 
        {{if $sejour->_NDA}}
          {{tr}}ssr-dossier_nda{{/tr}}
          {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
        {{/if}}
      </th>
      {{/if}}
    </tr>
  
    {{if $sejour->annule}}
      <tr>
        <th class="category cancelled" colspan="6">
          {{tr}}CSejour-{{$sejour->recuse|ternary:"recuse":"annule"}}{{/tr}}
        </th>
      </tr>
    {{/if}}
    
    {{if !$modules.dPplanningOp->_can->edit}}
      {{if $sejour->_id}}
        <tr>
          <th>{{mb_label object=$sejour field=praticien_id}}</th>
          <td>{{mb_value object=$sejour field=praticien_id}}</td>
          <th>{{mb_label object=$sejour field=libelle}}</th>
          <td>{{mb_value object=$sejour field=libelle}}</td>
          <th>{{mb_label object=$sejour field=_duree_prevue}}</th>
          <td>{{mb_value object=$sejour field=_duree_prevue}} jours</td>
        </tr>
      {{else}}
        <tr>
          <td>
            <div class="small-warning">{{tr}}ssr-msg_no_create_sejour{{/tr}}</div>
          </td>
        </tr>
      {{/if}}
    {{else}}
      <tr>
        <th>{{mb_label object=$sejour field=praticien_id}}</th>
        <td>
          <select name="praticien_id" class="{{$sejour->_props.praticien_id}}" style="width: 15em" tabindex="1">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$prats item=_user}}
            <option value="{{$_user->_id}}" class="mediuser" 
              style="border-color: #{{$_user->_ref_function->color}}" {{if $_user->_id == $sejour->praticien_id}}selected="selected"{{/if}}>
              {{$_user->_view}}
            </option>
            {{/foreach}}
          </select>
        </td>
  
        <th>{{mb_label object=$sejour field=entree_prevue}}</th>
        <td colspan="2">
          {{mb_field object=$sejour field="entree_prevue" form="editSejour" tabindex="5" register=true canNull=false onchange="updateSortiePrevue();"}}
        </td>
      </tr>
    
      <tr>
        <th>
          <input type="hidden" name="patient_id" class="{{$sejour->_props.patient_id}}" ondblclick="PatSelector.init();" value="{{$sejour->patient_id}}"/>
          {{mb_label object=$sejour field="patient_id"}}
        </th>
        <td>
          <input type="text" name="patient_view" style="width: 15em" value="{{$patient->_view}}" tabindex="2" 
            {{if (!$sejour->_id || $app->user_type == 1) && !$conf.ssr.recusation.sejour_readonly}} 
            onclick="PatSelector.init()" 
            {{/if}}
            readonly="readonly" />
          {{if (!$sejour->_id || $app->user_type == 1) && !$conf.ssr.recusation.sejour_readonly}} 
            <button type="button" class="search notext me-tertiary me-dark" onclick="PatSelector.init()">
              {{tr}}Choose{{/tr}}
            </button>
          {{/if}}
          <button id="button-edit-patient" type="button" class="edit notext me-tertiary me-dark"
                  onclick="location.href='?m=patients&tab=vw_edit_patients&patient_id='+this.form.patient_id.value"
                  {{if !$patient->_id || $conf.ssr.recusation.sejour_readonly}}style="display: none;"{{/if}}>
            {{tr}}Edit{{/tr}}
          </button>
          <script>
            PatSelector.init = function(){
              this.sForm = "editSejour";
              this.sId   = "patient_id";
              this.sView = "patient_view";
              this.pop();
            }
          </script>
        </td>
        
        <th>{{mb_label object=$sejour field=sortie_prevue}}</th>
        <td colspan="2">{{mb_field object=$sejour field="sortie_prevue" form="editSejour"  tabindex="6" register=true canNull=false onchange="updateDureePrevue();"}}</td>
      </tr>
      
      <tr>
        <th>{{mb_label object=$sejour field=libelle}}</th>
        <td>{{mb_field object=$sejour field=libelle form=editSejour tabindex="3" style="width: 12em"}}</td>
        <th>{{mb_label object=$sejour field=_duree_prevue}}</th>
        <td>
          {{mb_field object=$sejour field="_duree_prevue" increment=true form=editSejour prop="num min|0" size=2 tabindex="7" onchange="updateSortiePrevue();" value=$sejour->sejour_id|ternary:$sejour->_duree_prevue:0}}
          {{tr}}night|pl{{/tr}}
        </td>
        <td id="dureeEst"></td>
      </tr>
      {{if $sejour->_id}}
        {{assign var=affectation value=null}}
        {{if $sejour->_ref_curr_affectation->_id}}
          {{assign var=affectation value=$sejour->_ref_curr_affectation}}
        {{elseif $sejour->_ref_prev_affectation->_id}}
          {{assign var=affectation value=$sejour->_ref_prev_affectation}}
        {{elseif $sejour->_ref_next_affectation->_id}}
          {{assign var=affectation value=$sejour->_ref_next_affectation}}
        {{/if}}

        <tr>
          <th>{{tr}}CAffectation{{/tr}}</th>
          <td colspan="3" {{if !$affectation}}class="empty" {{/if}}>
            {{if $affectation}}
              {{$affectation->_view}}
            {{else}}
              {{tr}}CAffectation.none{{/tr}}
            {{/if}}
          </td>
        </tr>
      {{/if}}
      {{if !$dialog && !$conf.ssr.recusation.sejour_readonly}}
      <tr>
        <td class="button" colspan="8">
          {{if $sejour->_id}}
            {{if $can->edit}}
              <button class="modify default me-primary" tabindex="23">{{tr}}Save{{/tr}}</button>
              {{if $can->admin}}
              <button class="{{$sejour->annule|ternary:'change':'cancel'}} me-tertiary" type="button" tabindex="24"
                      onclick="cancelSejourSSR();">
                 {{tr}}{{$sejour->annule|ternary:'Restore':'Cancel'}}{{/tr}}
              </button>
                <button class="trash me-tertiary" type="button" tabindex="25"
                        onclick="confirmDeletion(this.form,{typeName:'le séjour ',objName:'{{$sejour->_view|smarty:nodefaults|JSAttribute}}'})">
                  {{tr}}Delete{{/tr}}
                </button>
              {{/if}}
            {{/if}}
            <button class="print me-tertiary" type="button" onclick="printFormSejour();">{{tr}}Print{{/tr}}</button>
          {{else}}
            <button class="submit default" tabindex="26">{{tr}}Create{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
      {{/if}}
    {{/if}}

  </table>
</form>
