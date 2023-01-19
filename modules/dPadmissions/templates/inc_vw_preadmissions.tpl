{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=prestations ajax=1}}
{{mb_default var=is_modal value=0}}

<script>

  callbackModal = function() {
    window.parent.Control.Modal.close();
    window.parent.see_consult_without_dhe();
  };

  var callbackDHE = {{if $is_modal}}callbackModal{{else}}Admissions.updateListPreAdmissions{{/if}};

  openDHEModal = function(pat_id) {
    var url = new Url('dPplanningOp','vw_edit_planning');
    url.addParam('pat_id', pat_id);
    url.addParam('operation_id', 0);
    url.addParam('sejour_id',0);
    url.addParam('dialog',1);
    url.modal({width: '95%',height: '95%', onclose: callbackDHE});
    url.modalObject.observe('afterClose', callbackDHE);
  };

  {{if !$is_modal}}
    Prestations.callback = reloadPreAdmission;
    Calendar.regField(getForm("changeDatePreAdmissions").date, null, {noView: true});
  {{/if}}
</script>


<table class="tbl" id="table_preadmissions">
  <tbody>
    {{foreach from=$listConsultations item=curr_consult}}
      {{mb_include module=admissions template="inc_vw_preadmission_line" nodebug=true}}
    {{foreachelse}}
      <tr>
        <td colspan="11" class="empty">Aucune pré-admission</td>
      </tr>
    {{/foreach}}
  </tbody>
  <thead>
    {{if !$is_modal}}
      <tr>
        <th class="title" colspan="11">
          <a href="#" onclick="Admissions.updateListPreAdmissions('{{$hier}}');" style="display: inline"><<<</a>
          {{$date|date_format:$conf.longdate}}
          <form name="changeDatePreAdmissions" action="?" method="get">
            <input type="hidden" name="m" value="{{$m}}" />
            <input type="hidden" name="tab" value="vw_idx_preadmission" />
            <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
          </form>
          <a href="#" onclick="Admissions.updateListPreAdmissions('{{$demain}}');" style="display: inline">>>></a>
          <span style="margin-left: -270px;">
              <label for="sejour_prepared" title="Afficher les pré-admissions préparées sans consultation préanesthésique" style="float: right;font-size: 12px;">{{tr}}Pre_admission-sejour_prepared{{/tr}}</label>
              <input type="checkbox" name="sejour_prepared" value="{{$sejour_prepared}}" {{if $sejour_prepared}}checked{{/if}} style="float: right;"
                     onchange="Admissions.pre_admission_sejour_prepared = this.checked ? 1 : 0; Admissions.updateListPreAdmissions();"/>
            </span>
          <br />
          <select name="filter" style="float:right;width: 260px;" onchange="Admissions.pre_admission_filter = $V(this); Admissions.updateListPreAdmissions();">
            <option value="">&mdash; Toutes les pré-admissions</option>
            <option value="dhe" {{if $filter == "dhe"}}selected{{/if}}>Pré-admissions sans intervention prévue</option>
          </select>

          <span style="float: right;">
              <form name="changeTypePEC" method="get">
                {{foreach from=$sejour->_specs.type_pec->_list item=_type_pec}}
                  <label>
                    {{$_type_pec}} <input type="checkbox" name="type_pec[]" value="{{$_type_pec}}" {{if in_array($_type_pec, $type_pec)}}checked{{/if}}
                                          onclick="Admissions.pre_admission_type_pec = $V(this.form.elements['type_pec[]']); Admissions.updateListPreAdmissions();" />
                  </label>
                {{/foreach}}
              </form>
              <select name="period" onchange="Admissions.pre_admission_period = $V(this); Admissions.updateListPreAdmissions();">
                <option value=""      {{if !$period          }}selected{{/if}}>&mdash; {{tr}}dPAdmission.admission all the day{{/tr}}</option>
                <option value="matin" {{if $period == "matin"}}selected{{/if}}>{{tr}}dPAdmission.admission morning{{/tr}}</option>
                <option value="soir"  {{if $period == "soir" }}selected{{/if}}>{{tr}}dPAdmission.admission evening{{/tr}}</option>
              </select>
            </span>

          <em style="float: left; font-weight: normal;">
            {{$listConsultations|@count}} pré-admissions ce jour {{if $filter}}sans interventions{{/if}}
          </em>
        </th>
      </tr>
    {{/if}}
    <tr>
      {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
        <th></th>
      {{/if}}
      <th colspan="3">Consultation préanesthésique</th>
      <th colspan="6">Hospitalisation</th>
    </tr>
    <tr>
      {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
        <th>
          <input type="checkbox" name="order_checkbox" style="margin-right: 10px;" onclick="appFineClient.toggleAdmission('listPreAdmissions', 'order_checkbox', this.checked)"
            title="{{tr}}CAppFineClient-msg-select sejour to send form{{/tr}}" />{{tr}}CAppFine{{/tr}}
          <button style="margin-top: 1%;" type="submit"
            onclick="appFineClient.relaunchPatientsAdmission('listPreAdmissions', 'order_checkbox')">
            <i class="fas fa-share fa-lg" title="{{tr}}CAppFineClient-msg-Relaunch patient dashboard task{{/tr}}"></i>
            {{tr}}CAppFineClient-relaunch{{/tr}}
          </button>
        </th>
      {{/if}}
      <th>
        {{mb_colonne class="CConsultation" field="patient_id" order_col=$order_col_pre order_way=$order_way_pre order_suffixe="_pre" url="?m=$m&tab=vw_idx_preadmission"}}
      </th>
      <th class="narrow"><input type="text" size="3" onkeyup="Admissions.filter(this, 'table_preadmissions', 'patient_td')"></th>
      <th>
        {{mb_colonne class="CConsultation" field="heure" order_col=$order_col_pre order_way=$order_way_pre order_suffixe="_pre" url="?m=$m&tab=vw_idx_preadmission"}}
      </th>
      <th>Praticien</th>
      <th>Admission</th>
      <th>Chambre</th>
      <th>Préparé</th>
      <th>C2S</th>
      {{if $app->user_prefs.show_dh_admissions}}
        <th class="narrow" colspan="2">DH</th>
      {{/if}}
    </tr>
  </thead>
</table>

{{mb_include module=forms template=inc_widget_ex_class_register_multiple_end event_name=preparation_entree object_class="CSejour"}}
