{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  createDA = function(operation_id, consult_anesth_id, duplicate, sejour_id, dest_consult_anesth_id) {
    if (Object.isUndefined(dest_consult_anesth_id) || confirm($T('CConsultAnesth-verify_prescriptions'))) {
      var form = getForm("createDossierAnesth");
      $V(form.operation_id, operation_id);
      $V(form.sejour_id, sejour_id);
      if (duplicate == 1) {
        $V(form.dosql, "do_duplicate_dossier_anesth_aed");
        $V(form.redirect, "1");
        $V(form._consult_anesth_id, consult_anesth_id);
        if (!Object.isUndefined(dest_consult_anesth_id)) {
          $V(form._dest_consult_anesth_id, dest_consult_anesth_id);
        }
      }
      form.submit();
    }
  };

  deleteDA = function(form) {
    confirmDeletion(form, {typeName: 'le dossier d\'anesthésie'});
  };

  saveModif = function(form) {
    return onSubmitFormAjax(form, {
      onComplete: function() {
        GestionDA.url.refreshModal();
      }});
  };

  reloadDossierAnesthCurr = function() {
    new Url("cabinet", "httpreq_vw_consult_anesth")
      .addParam("chirSel", '{{$consult->_ref_chir->_id}}')
      .addParam("selConsult", '{{$consult->_id}}')
      .addParam("dossier_anesth_id", '{{$consult->_ref_consult_anesth->_id}}')
      .requestUpdate('consultAnesth');
  };

  updateOperation = function(operation_id, consult_anesth_id) {
    new Url('cabinet', 'ajax_update_operation')
      .addParam('operation_id', operation_id)
      .addParam('consult_anesth_id', consult_anesth_id)
      .requestModal(800, 250);
  }
</script>

<form name="createDossierAnesth" action="?m={{$m}}&tab=edit_consultation&selConsult={{$consult->_id}}" method="post">
  <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="redirect" value="?m={{$m}}&tab=edit_consultation&selConsult={{$consult->_id}}" />
  <input type="hidden" name="_consult_anesth_id" value="" />
  <input type="hidden" name="_dest_consult_anesth_id" value="" />
  <input type="hidden" name="consultation_id" value="{{$consult->_id}}" />
  <input type="hidden" name="operation_id" value=""/>
  <input type="hidden" name="sejour_id" value=""/>
</form>

<table class="tbl me-no-hover">
  <tr>
    <th>Dossier d'anesthésie</th>
    <th style="width: 45%;">{{tr}}COperation{{/tr}}</th>
  </tr>
  {{if $consult->_refs_dossiers_anesth|@count == 1 && !$consult->_ref_consult_anesth->operation_id}}
    {{foreach from=$patient->_ref_sejours item=curr_sejour}}
      {{foreach from=$curr_sejour->_ref_operations item=operation}}
        <tr>
          <td class="button">
            {{if !$operation->_ref_consult_anesth->_id}}
              <button type="button" class="link" {{if $operation->annulee}}disabled{{/if}}
                {{if !$perm_to_duplicate}}disabled="disabled" title="{{tr var1=$consult->_ref_praticien->_ref_function}}CConsultAnesth-user_no_this_cabinet{{/tr}}"{{/if}}
                      onclick="updateOperation({{$operation->_id}}, {{$consult->_ref_consult_anesth->_id}});">
                Associer à cette intervention
              </button>
            {{else}}
              {{assign var=_consult_op value=$operation->_ref_consult_anesth}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult_op->_guid}}')">
                {{tr}}{{$_consult_op->_class}}{{/tr}} <strong>{{mb_value object=$_consult_op->_ref_consultation field=_datetime}}</strong>
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult_op->_ref_chir}}
              </span>
              <strong><br />IPAQSS:</strong>{{mb_include module=cabinet template=inc_check_ipaqss consult_anesth=$_consult_op}}
              <span style="float:right;">
                {{mb_include module=system template=inc_object_history object=$_consult_op}}
              </span>

              <br />
              <button type="button" class="link" {{if $operation->annulee || !$perm_to_duplicate}}disabled{{/if}}
                      onclick="createDA('{{$operation->_id}}','{{$_consult_op->_id}}', 1, '{{$operation->sejour_id}}', '{{$consult->_ref_consult_anesth->_id}}');"
                {{if !$perm_to_duplicate}}title="{{tr var1=$consult->_ref_praticien->_ref_function}}CConsultAnesth-user_no_this_cabinet{{/tr}}"{{/if}}>
                {{tr}}CConsultAnesth-button-dissociate_interv_for_this{{/tr}}
              </button>
            {{/if}}
          </td>
          <td {{if $operation->annulee}}class="hatching" {{/if}}>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}', null, { view_tarif: true })">
              Le <strong>{{$operation->_datetime|date_format:$conf.longdate}}</strong>
              {{if $operation->cote}}Coté: {{$operation->cote}}{{/if}}<br/>
              {{if $operation->libelle}}
                <strong>{{$operation->libelle}}</strong>
              {{/if}}
              par le <strong>Dr {{$operation->_ref_chir}}</strong>
            </span><br/>
            {{assign var=sejour value=$operation->_ref_sejour}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_sejour->_guid}}')">
              <strong>Séjour du {{$curr_sejour->entree_prevue|date_format:$conf.date}} au {{$curr_sejour->sortie_prevue|date_format:$conf.date}}</strong>
              {{if $curr_sejour->type!="ambu" && $curr_sejour->type!="exte"}} {{$curr_sejour->_duree_prevue}} jour(s){{/if}} {{mb_value object=$curr_sejour field=type}}
            </span>
          </td>
        </tr>
      {{/foreach}}
    {{foreachelse}}
      <tr>
        <td class="button">
          {{assign var=consult_anesth value=$consult->_ref_consult_anesth}}

          {{if !$consult_anesth->date_interv && !$consult_anesth->chir_id && !$consult_anesth->libelle_interv && !$consult_anesth->depassement_anesth}}
            <button type="button" class="down" onclick="$('no_interv').show();">
              {{tr}}CConsultAnesth-edit_info_no_dhe{{/tr}}
            </button>
            <div style="display:none;" id="no_interv">
          {{else}}
            <div>
          {{/if}}
            <form name="opInfoFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this)">
              <input type="hidden" name="m" value="cabinet" />
              <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
              <input type="hidden" name="del" value="0" />
              {{mb_key object=$consult_anesth}}
              <table class="form">
                <tr>
                  <td style="text-align: right"><strong>{{mb_label object=$consult_anesth field="date_interv"}}</strong></td>
                  <td>{{mb_field object=$consult_anesth field="date_interv" form="opInfoFrm" register=true onchange="this.form.onsubmit()"}}</td>
                </tr>
                <tr>
                  <td style="text-align: right"><strong>{{mb_label object=$consult_anesth field="chir_id"}}</strong></td>
                  <td>
                    <select name="chir_id" class="{{$consult_anesth->_props.chir_id}}" style="width: 14em;" onchange="this.form.onsubmit();">
                      <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                      {{mb_include module=mediusers template=inc_options_mediuser selected=$consult_anesth->chir_id list=$listChirs}}
                    </select>
                  </td>
                </tr>
                <tr>
                  <td style="text-align: right"><strong>{{mb_label object=$consult_anesth field="libelle_interv"}}</strong></td>
                  <td>{{mb_field object=$consult_anesth field="libelle_interv" onchange="this.form.onsubmit()"}}</td>
                </tr>
                <tr>
                  <td style="text-align: right"><strong>{{mb_label object=$consult_anesth field=depassement_anesth}}</strong></td>
                  <td>{{mb_field object=$consult_anesth field=depassement_anesth onchange="this.form.onsubmit()"}}</td>
                </tr>
                <tr>
                  <td colspan="2" class="button">
                    <button type="button" class="save" onclick="reloadDossierAnesthCurr();Control.Modal.close();">{{tr}}Validate{{/tr}}</button>
                  </td>
                </tr>
              </table>
            </form>
          </div>
        </td>
        <td>
          <div class="warning" style="float:left;"> Pas d'intervention </div>
          {{if !$app->user_prefs.simpleCabinet}}
            {{if "ecap"|module_active && $current_group|idex:"ecap"|is_numeric}}
              {{mb_include module=ecap template=inc_button_dhe patient_id=$patient->_id praticien_id=$consult->_ref_praticien->_id; show_non_prevue=false}}
            {{else}}
              <button class="new" type="button" onclick="showSejourButtons();" style="float:right;">{{tr}}CSejour-title-new{{/tr}}</button>
            {{/if}}
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
    {{if "maternite"|module_active && !$consult->grossesse_id && $patient->_ref_next_grossesse->_id}}
    {{assign var=grossesse value=$patient->_ref_next_grossesse}}
    <tr>
      <th>{{tr}}CConsultation-back-consult_anesth{{/tr}}</th>
      <th>{{tr}}CGrossesse{{/tr}}</th>
    </tr>
    <tr>
      <td class="button">
        <form name="linkGrossesse" method="post" onsubmit="return onSubmitFormAjax(this, function() { document.location.reload(); });">
          {{mb_class object=$consult}}
          {{mb_key   object=$consult}}
          <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
          <button type="button" class="link" onclick="this.form.onsubmit();">Associer à cette grossesse</button>
        </form>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$grossesse->_guid}}');">
          {{$grossesse}}
        </span>
      </td>
    </tr>
    {{/if}}
    <tr>
      <td colspan="2" class="button">
        <form name="deleteDossierAnesth-{{$consult->_ref_consult_anesth->_guid}}" action="?m={{$m}}" method="post">
          {{mb_class object=$consult->_ref_consult_anesth}}
          {{mb_key   object=$consult->_ref_consult_anesth}}
          <input type="hidden" name="del" value="1" />
          <button class="trash" type="button" onclick="deleteDA(this.form);"
                  {{if !$perm_to_duplicate}}disabled="disabled" title="{{tr var1=$consult->_ref_praticien->_ref_function}}CConsultAnesth-user_no_this_cabinet{{/tr}}"{{/if}}
            >{{tr}}CConsultation-action-Delete the anesthesia file{{/tr}}</button>
        </form>
      </td>
    </tr>
  {{else}}
    {{foreach from=$consult->_refs_dossiers_anesth item=consult_anesth name=consults_anesth}}
      {{assign var="operation" value=$consult_anesth->_ref_operation}}
      <tr>
        <td class="button">
          <table class="form">
            <tr>
              <td>
                {{if $consult->_refs_dossiers_anesth|@count > 1}}
                  <button class="search" onclick="reloadDossierAnesth('{{$consult_anesth->_id}}');"
                          {{if $consult->_ref_consult_anesth->_id == $consult_anesth->_id}}disabled{{/if}}>
                    {{tr}}Display{{/tr}}
                  </button>
                {{/if}}
              </td>
              <td colspan="2" style="text-align:center;">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$consult_anesth->_guid}}')">
                  {{tr}}{{$consult_anesth->_class}}{{/tr}}
                  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult->_ref_chir}}
                </span>
              </td>
                <td><strong>IPAQSS:</strong>{{mb_include module=cabinet template=inc_check_ipaqss}}</td>
              <td>
                <span style="float:right;">
                  {{mb_include module=system template=inc_object_history object=$consult_anesth}}
                </span>
              </td>
            </tr>
          </table>
          {{if $smarty.foreach.consults_anesth.last && $ops_sans_dossier_anesth|@count != 0}}
            <button class="down" onclick="createDA('{{$first_operation->_id}}','{{$consult_anesth->_id}}', 1, '{{$first_operation->sejour_id}}');"
              {{if !$perm_to_duplicate}}disabled="disabled" title="{{tr var1=$consult->_ref_praticien->_ref_function}}CConsultAnesth-user_no_this_cabinet{{/tr}}"{{/if}}
              >{{tr}}Duplicate{{/tr}}</button>
          {{/if}}
        </td>
        <td {{if $operation->annulee}}class="hatching"{{/if}}>
          {{if $consult_anesth->operation_id}}
            <form name="addInterv-{{$operation->_id}}" action="?m={{$m}}" method="post" onsubmit="return saveModif(this);">
              <input type="hidden" name="m" value="cabinet" />
              <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
              {{mb_key object=$consult_anesth}}
              <input type="hidden" name="operation_id" value=""/>
              <button type="button" class="unlink notext" onclick="return saveModif(this.form);" style="float:right;"
                      title="{{tr}}CConsultation-action-Delete link to intervention{{/tr}}">
              </button>
            </form>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}', null, { view_tarif: true })">
              Le <strong>{{$operation->_datetime|date_format:$conf.longdate}}</strong>
              {{if $operation->cote}}Coté: {{$operation->cote}}{{/if}}<br/>
              {{if $operation->libelle}}
                <strong>{{$operation->libelle}}</strong>
              {{/if}}
               par le <strong>Dr {{$operation->_ref_chir}}</strong>
            </span><br/>
            {{assign var=sejour value=$consult_anesth->_ref_operation->_ref_sejour}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
              <strong>Séjour du {{$sejour->entree_prevue|date_format:$conf.date}} au {{$sejour->sortie_prevue|date_format:$conf.date}}</strong>
                  {{if $sejour->type!="ambu" && $sejour->type!="exte"}} {{$sejour->_duree_prevue}} jour(s){{/if}}
                  {{mb_value object=$sejour field=type}}
            </span>
          {{else}}
            <div class="warning">Pas d'intervention pour ce dossier d'anesthésie</div>
            {{if $consult->_refs_dossiers_anesth|@count > 1}}
              <form name="deleteDossierAnesth-{{$consult_anesth->_guid}}" action="?m={{$m}}" method="post">
                {{mb_class object=$consult_anesth}}
                {{mb_key   object=$consult_anesth}}
                <input type="hidden" name="del" value="1" />
                <button class="trash" type="button" onclick="deleteDA(this.form);"
                        {{if !$perm_to_duplicate}}
                          disabled="disabled"
                          title="{{tr var1=$consult->_ref_praticien->_ref_function}}CConsultAnesth-user_no_this_cabinet{{/tr}}"
                        {{/if}}
                >{{tr}}CConsultation-action-Delete the anesthesia file{{/tr}}</button>
              </form>
            {{/if}}
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
    {{foreach from=$ops_sans_dossier_anesth item=operation}}
      <tr>
        <td class="button">
          <button class="link" onclick="createDA('{{$operation->_id}}', 0, 0, '{{$operation->sejour_id}}');"
            {{if !$perm_to_duplicate}}disabled="disabled" title="{{tr var1=$consult->_ref_praticien->_ref_function}}CConsultAnesth-user_no_this_cabinet{{/tr}}"{{/if}}
            >Nouveau dossier vierge</button>
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}', null, { view_tarif: true })">
            Le <strong>{{$operation->_datetime|date_format:$conf.longdate}}</strong>
            {{if $operation->cote}}Coté: {{$operation->cote}}{{/if}}<br/>
            {{if $operation->libelle}}
              <strong>{{$operation->libelle}}</strong>
            {{/if}}
            par le <strong>Dr {{$operation->_ref_chir}}</strong>
          </span><br/>
          {{assign var=sejour value=$operation->_ref_sejour}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
              <strong>Séjour du {{$sejour->entree_prevue|date_format:$conf.date}} au {{$sejour->sortie_prevue|date_format:$conf.date}}</strong>
            {{if $sejour->type!="ambu" && $sejour->type!="exte"}} {{$sejour->_duree_prevue}} jour(s){{/if}} {{mb_value object=$sejour field=type}}
            </span>
        </td>
      </tr>
    {{/foreach}}

    {{foreach from=$ops_annulees item=operation}}
      <tr>
        <td class="button">
          <button class="link" disabled>Nouveau dossier vierge</button>
        </td>
        <td class="hatching">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}', null, {view_tarif: true})">
            Le <strong>{{$operation->_datetime|date_format:$conf.longdate}}</strong>
            {{if $operation->cote}}Coté: {{$operation->cote}}{{/if}}<br/>
            {{if $operation->libelle}}
              <strong>{{$operation->libelle}}</strong>
            {{/if}}
            par le <strong>Dr {{$operation->_ref_chir}}</strong>
          </span><br/>
          {{assign var=sejour value=$operation->_ref_sejour}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
              <strong>Séjour du {{$sejour->entree_prevue|date_format:$conf.date}} au {{$sejour->sortie_prevue|date_format:$conf.date}}</strong>
            {{if $sejour->type!="ambu" && $sejour->type!="exte"}} {{$sejour->_duree_prevue}} jour(s){{/if}} {{mb_value object=$sejour field=type}}
            </span>
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
