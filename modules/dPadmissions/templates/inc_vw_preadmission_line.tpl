{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$curr_consult->_ref_patient}}
{{assign var=dossiers_anesth value=$curr_consult->_refs_dossiers_anesth}}

{{assign var=_sejour value=""}}
{{assign var=first_dossier_anesth value=$dossiers_anesth|smarty:nodefaults|@reset}}
{{if $first_dossier_anesth && $first_dossier_anesth->_ref_sejour->_id}}
  {{assign var=_sejour value=$first_dossier_anesth->_ref_sejour}}
{{elseif $curr_consult->_next_sejour_and_operation.CSejour->_id}}
  {{assign var=_sejour value=$curr_consult->_next_sejour_and_operation.CSejour}}
{{/if}}

<tr id="consultation{{$curr_consult->_id}}" data-id="{{$curr_consult->_id}}"
    class="sejour sejour-type-default {{if $_sejour}}sejour-type-{{$_sejour->type}} {{if !$_sejour->facturable}} non-facturable {{/if}}{{/if}}">

  {{if is_array($curr_consult->_next_sejour_and_operation)}}
    {{if $curr_consult->_next_sejour_and_operation.COperation->_id}}
      {{assign var="curr_adm" value=$curr_consult->_next_sejour_and_operation.COperation->_ref_sejour}}
      {{assign var="type_event" value="COperation"}}
    {{else}}
      {{assign var="curr_adm" value=$curr_consult->_next_sejour_and_operation.CSejour}}
      {{assign var="type_event" value="CSejour"}}
    {{/if}}
  {{/if}}

  {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    <td class="button" rowspan="{{$dossiers_anesth|@count}}">
      {{if $_sejour && $_sejour->_id}}
        <input type="checkbox" name="order_checkbox" style="float: left;" title="{{tr}}CAppFineClient-msg-select sejour to send form{{/tr}}" />
      {{/if}}

      {{mb_include module=appFineClient template=inc_create_account_appFine loadJS=0 idex=$curr_consult->_ref_patient->_ref_appFine_idex patient=$curr_consult->_ref_patient}}

    {{if $_sejour && $_sejour->_id}}
      {{mb_include module=appFineClient template=inc_buttons_action_preadmission loadJS=0 _sejour_appFine=$_sejour _patient_appFine=$patient}}
    {{/if}}
    </td>
  {{/if}}

  <td class="text patient_td" rowspan="{{$dossiers_anesth|@count}}" colspan="2">
    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
      {{$patient}}
    </span>

    {{mb_include module=patients template=inc_status_icon}}
    {{mb_include module=patients template=inc_icon_bmr_bhre}}
  </td>

  <td class="text" rowspan="{{$dossiers_anesth|@count}}">
    {{if $curr_consult->_id}}
      <div class="{{if $curr_consult->chrono == 64}}small-success{{else}}small-info{{/if}}" style="margin: 0;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_consult->_guid}}')">{{$curr_consult->heure|date_format:$conf.time}}</span>
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_consult->_ref_plageconsult->_ref_chir}}
      </div>
    {{else}}
      <div class="small-warning" style="margin: 0;">
        Consultation préanesthésique non créée
      </div>
    {{/if}}
  </td>

  {{foreach from=$dossiers_anesth item=_dossier name=dossiers_anesth}}
    {{if !$smarty.foreach.dossiers_anesth.first}}
      <tr class="more" data-consult_id="{{$curr_consult->consultation_id}}">
    {{/if}}

    {{if $_dossier->_etat_dhe_anesth != "non_associe"}}
      {{assign var=_sejour value=""}}
      {{if $_dossier->_ref_sejour->_id}}
        {{assign var=_sejour value=$_dossier->_ref_sejour}}
      {{elseif $curr_consult->_next_sejour_and_operation.CSejour->_id}}
        {{assign var=_sejour value=$curr_consult->_next_sejour_and_operation.CSejour}}
      {{/if}}

      {{assign var=cell_style value="background: #ccc;"}}

      {{if     $_sejour->type == 'ambu'}} {{assign var=cell_style value="background: #faa;"}}
      {{elseif $_sejour->type == 'comp'}} {{assign var=cell_style value="background: #fff;"}}
      {{elseif $_sejour->type == 'exte'}} {{assign var=cell_style value="background: #afa;"}}
      {{elseif in_array($_sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$_sejour->praticien_id)}}
        {{assign var=cell_style value="background: #ff6;"}}
      {{/if}}

      {{if !$_sejour->facturable}}
        {{assign var=cell_style value="$cell_style background-image:url(images/icons/ray_vertical.gif); background-repeat:repeat;"}}
      {{/if}}

      <td class="text" style="{{$cell_style}}">
        {{foreach from=$_sejour->_ref_operations item=_op name=op_sejour}}
          {{if $smarty.foreach.op_sejour.first}}
            <button class="print notext" style="float: right;" title="{{tr}}admissions-action-Print the DHE from the intervention{{/tr}}"
                    onclick="Admissions.printDHE('operation_id', {{$_op->_id}}); return false;">
            </button>

            <a href="#showDocs" title="{{tr}}admissions-action-Display document and file|pl{{/tr}}"
               class="button" onclick="Admissions.showDocs('{{$_sejour->_id}}');" style="float: right;">
              <i class="far fa-file" style="font-size: 1.4em; padding-left: 2px;" aria-hidden="true"></i>
            </a>
          {{/if}}
        {{foreachelse}}
          <button class="print notext" style="float: right;" title="{{tr}}admissions-action-Print the DHE of the stay{{/tr}}"
                  onclick="Admissions.printDHE('sejour_id', {{$_sejour->_id}}); return false;">
          </button>
        {{/foreach}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
      </td>

      <td class="text" style="{{$cell_style}}">
        <div>
          {{if "dmp"|module_active && $_sejour}}
            <span style="float: right;">
              {{mb_include module=dmp template=inc_button_dmp patient=$patient compact=true}}
            </span>
          {{/if}}
          {{mb_include module=system template=inc_object_notes object=$_sejour float=right}}
          {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}
        </div>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{if $_sejour->presence_confidentielle}}
            {{mb_include module=planningOp template=inc_badge_sejour_conf}}
          {{/if}}
        {{$_sejour->entree|date_format:$conf.date}}
        </span>
      </td>

      {{if !$_sejour->annule && $_dossier && $_dossier->_ref_sejour->_id}}

        <td class="text" style="{{$cell_style}}">
          {{mb_include template=inc_form_prestations sejour=$_sejour edit=$canAdmissions->edit}}
          {{mb_include module=hospi template=inc_placement_sejour sejour=$_sejour}}
        </td>

        <td style="{{$cell_style}}">
          {{if $canAdmissions->edit}}
            <form name="editSaisFrm{{$_sejour->_id}}" action="?" method="post">
              <input type="hidden" name="m" value="dPplanningOp" />
              <input type="hidden" name="dosql" value="do_sejour_aed" />
              <input type="hidden" name="sejour_id" value="{{$_sejour->_id}}" />
              <input type="hidden" name="patient_id" value="{{$_sejour->patient_id}}" />
              {{mb_field object=$_sejour field=type hidden=true}}

              {{mb_include module=forms template=inc_widget_ex_class_register_multiple object=$_sejour cssStyle="display: inline-block;"}}

              {{if !$_sejour->entree_preparee}}
                <input type="hidden" name="entree_preparee" value="1" />
                <input type="hidden" name="_entree_preparee_trigger" value="1" />
                <button class="tick" type="button" onclick="submitPreAdmission(this.form);">
                  {{tr}}CSejour-entree_preparee{{/tr}}
                </button>
              {{else}}
                <input type="hidden" name="entree_preparee" value="0" />
                <button class="cancel" type="button" onclick="submitPreAdmission(this.form);">
                  {{tr}}Cancel{{/tr}}
                </button>
              {{/if}}
              {{if ($_sejour->entree_modifiee == 1) && ($conf.dPplanningOp.CSejour.entree_modifiee == 1)}}
                <img src="images/icons/warning.png" title="Le dossier a été modifié, il faut le préparer" />
              {{/if}}
            </form>
          {{else}}
            {{mb_value object=$_sejour field="entree_preparee"}}
          {{/if}}
        </td>

        <td style="{{$cell_style}}">
          {{if $_sejour->_couvert_c2s}}
            {{me_img_title src="tick.png" icon="tick" class="me-success"}}
              Droits C2S en cours
            {{/me_img_title}}
          {{else}}
            -
          {{/if}}
        </td>

        {{if $app->user_prefs.show_dh_admissions}}
          {{mb_include module=admissions template=inc_operations_depassement operations=$_sejour->_ref_operations sejour=$_sejour}}
        {{/if}}

      {{elseif $_sejour->annule}}
        <td colspan="4" class="cancelled">
          Annulé
        </td>

      {{else}}
        <td colspan="4" class="button" style="{{$cell_style}}">
          {{if $type_event == "COperation"}}
            Intervention non associée à la consultation
            {{if $canAdmissions->edit}}
              <br />
              <form name="addOpFrm-{{$curr_consult->_id}}" action="?m={{$m}}" method="post">
              <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="m" value="dPcabinet" />
              {{mb_key object=$_dossier}}
              <input type="hidden" name="operation_id" value="{{$curr_consult->_next_sejour_and_operation.COperation->_id}}" />
              <input type="hidden" name="postRedirect" value="m={{$m}}" />
              <button type="submit" class="tick">
                Associer l'intervention
              </button>
              </form>
            {{/if}}
          {{else}}
            Séjour non associé à la consultation
            {{if $canAdmissions->edit}}
              <br />
              <form name="addOpFrm-{{$curr_consult->_id}}" action="?m={{$m}}" method="post">
              <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="m" value="dPcabinet" />
              {{mb_key object=$_dossier}}
              <input type="hidden" name="sejour_id" value="{{$_sejour->_id}}" />
              <input type="hidden" name="postRedirect" value="m={{$m}}" />
              <button type="submit" class="tick">
                Associer le séjour
              </button>
              </form>
            {{/if}}
          {{/if}}
        </td>
      {{/if}}

    {{else}}
      <td colspan="6" class="button">
        <span class="texticon texticon-stup texticon-stroke" style="white-space: nowrap;"
              title="{{tr}}CConsultation-_etat_dhe_anesth-non_associe{{/tr}}">
          {{tr}}COperation-event-dhe{{/tr}}
        </span>
        {{if $canPlanningOp->edit}}
        :
          <button onclick="openDHEModal('{{$curr_consult->patient_id}}');" class="button new">
            Créer une demande d'hospitalisation
          </button>
        {{/if}}
      </td>
    {{/if}}
  {{/foreach}}
</tr>
