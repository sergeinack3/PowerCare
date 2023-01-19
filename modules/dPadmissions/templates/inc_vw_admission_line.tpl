{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$_sejour->_ref_patient}}
{{assign var=number_op value=$_sejour->_ref_operations|@count}}
{{mb_default var=single_line value=false}}

<td {{if $print_global}}style="display: none;"{{/if}}>
    <input type="checkbox" name="print_doc" value="{{$_sejour->_id}}"
           onchange="Admissions.updatePrintSelectionButtonDisplay();"/>
</td>

<td colspan="{{if $print_global}}1{{else}}2{{/if}}" class="text">
    {{if $canPlanningOp->read}}
        <div style="float: right; {{if $print_global}}display: none;{{/if}}">
            {{mb_include module=system template=inc_object_notes object=$_sejour}}
        </div>
    {{/if}}

    <span class="CPatient-view me-patient-view">
    {{if "maternite"|module_active && $_sejour->_ref_first_affectation->_ref_parent_affectation->_id}}
        {{assign var=sejour_maman value=$_sejour->_ref_first_affectation->_ref_parent_affectation->_ref_sejour}}
        <img src="style/mediboard_ext/images/icons/grossesse.png"
             onmouseover="ObjectTooltip.createEx(this, 'CPatient-{{$sejour_maman->patient_id}}');"
             style="background-color: rgb(255, 215, 247); border-radius: 50%"/>
    {{/if}}
    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">{{$patient}}</span>
    <span {{if $print_global}}style="display: none;"{{/if}}>
      {{mb_include module=patients template=inc_status_icon}}
    </span>
    {{if $print_global}}
        <div class="me-patient-details">
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}
      </div>
    {{/if}}
        {{mb_include module=patients template=inc_icon_bmr_bhre}}
  </span>

    {{if !$print_global}}
        <div class="me-patient-details">
            {{if "dPsante400"|module_active}}
                {{mb_include module=dPsante400 template=inc_manually_ipp_nda sejour=$_sejour patient=$patient
                callback=Admissions.reloadAdmissionLine.curry("`$_sejour->_id`")}}
            {{/if}}
            {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour _show_numdoss_modal=1}}
        </div>
    {{/if}}


    {{if $patient->_ref_IPP}}
        <form name="editIPP{{$patient->_id}}" method="post" class="prepared">
            <input type="hidden" class="notNull" name="id400" value="{{$patient->_ref_IPP->id400}}"/>
            <input type="hidden" class="notNull" name="object_id" value="{{$patient->_id}}"/>
            <input type="hidden" class="notNull" name="object_class" value="CPatient"/>
        </form>
        {{if $_sejour->_ref_NDA}}
            <form name="editNumdos{{$_sejour->_id}}" method="post" class="prepared">
                <input type="hidden" class="notNull" name="id400" value="{{$_sejour->_ref_NDA->id400}}" size="8"/>
                <input type="hidden" class="notNull" name="object_id" value="{{$_sejour->_id}}"/>
                <input type="hidden" class="notNull" name="object_class" value="CSejour"/>
            </form>
        {{/if}}
    {{/if}}

    {{if !$patient->nom_jeune_fille}}
        <br/>
        <div class="small-warning"> Le nom de naissance est obligatoire mais n'a pas été renseigné pour cette patiente
            (circonstances particulières)
        </div>
    {{/if}}
</td>

{{* Icones contextuelles (appels contextuels, notifications, prestations,... *}}
{{if $canPlanningOp->read && $flag_contextual_icons}}
    <td class="narrow" style="white-space: normal; {{if $print_global}}display: none;{{/if}}">
        {{mb_include module=hospi template=inc_button_send_prestations_sejour}}

        {{if "web100T"|module_active}}
            {{mb_include module=web100T template=inc_button_iframe}}
        {{/if}}

        {{if "softway"|module_active}}
            {{mb_include module=softway template=inc_button_synthese}}
        {{/if}}

        {{if "novxtelHospitality"|module_active}}
            {{mb_include module=novxtelHospitality template=inc_button_novxtel_hospitality}}
        {{/if}}

        {{if 'notifications'|module_active}}
            {{if $_sejour->_ref_notifications}}
                {{foreach from=$_sejour->_ref_notifications item=_notification}}
                {{assign var=message_notif value=$_notification->_message}}
                {{if $message_notif->status == "scheduled"}}
                    {{assign var=class_sms value="texticon texticon-gray"}}
                    {{elseif $message_notif->status == "transmitted"}}
                      {{assign var=class_sms value="texticon texticon-timestamp"}}
                    {{elseif $message_notif->status == "delivered"}}
                      {{assign var=class_sms value="texticon texticon-ok"}}
                    {{else}}
                    {{assign var=class_sms value="texticon texticon-ko"}}
                {{/if}}
                {{assign var=notification_status_title value='Ox\Core\CAppUI::tr'|static_call:$_notification->_status}}
              <span class="{{$class_sms}}"
                    title="{{$notification_status_title}}">SMS</span>
                {{/foreach}}
            {{/if}}
        {{/if}}

        {{if "dPadmissions General show_deficience"|gconf}}
            {{mb_include module=patients template=inc_vw_antecedents type=deficience callback="Admissions.reloadAdmissionLine.curry(`$_sejour->_id`)"}}
        {{/if}}
    </td>
{{/if}}

<td>
    {{if $canAdmissions->edit}}
        {{if $conf.dPplanningOp.COperation.verif_cote}}
            {{foreach from=$_sejour->_ref_operations item=curr_op}}
                {{if $curr_op->cote == "droit" || $curr_op->cote == "gauche"}}
                    <form name="editCoteOp{{$curr_op->_id}}" action="?" method="post" class="prepared">
                        <input type="hidden" name="m" value="planningOp"/>
                        <input type="hidden" name="dosql" value="do_planning_aed"/>
                        {{mb_key object=$curr_op}}
                        {{mb_label object=$curr_op field="cote_admission"}} :
                        {{mb_field emptyLabel="Choose" object=$curr_op field="cote_admission" onchange="submitCote(this.form);"}}
                    </form>
                    <br/>
                {{/if}}
            {{/foreach}}
        {{/if}}
        <button
          class="not-printable {{if !$_sejour->entree_reelle}}tick me-primary{{else}}edit notext{{/if}}"
          onclick="IdentityValidator.manage(
            '{{$patient->status}}',
            '{{$patient->_id}}',
            Admissions.validerEntree.curry('{{$_sejour->_id}}',null, Admissions.reloadAdmissionLine.curry('{{$_sejour->_id}}'))
            );">
            {{if !$_sejour->entree_reelle}}{{tr}}CSejour-admit{{/tr}}{{else}}Modifier Admission{{/if}}
        </button>
        {{if $_sejour->entree_reelle}}
            {{'Ox\Core\CMbDT::format'|static_call:"":$_sejour->entree_reelle|date_format:"%Hh%M"}}
            <br/>
        {{/if}}


    {{elseif $_sejour->entree_reelle}}
        {{if ($_sejour->entree_reelle < $date_min) || ($_sejour->entree_reelle > $date_max)}}
            {{$_sejour->entree_reelle|date_format:"%Hh%M"}}
            <br>
        {{else}}
            {{$_sejour->entree_reelle|date_format:"%Hh%M"}}
        {{/if}}
        {{if $_sejour->mode_sortie}}
            <br/>
            {{tr}}CSejour.mode_sortie.{{$_sejour->mode_sortie}}{{/tr}}
        {{/if}}

        {{if $_sejour->etablissement_entree_id}}
            <br/>
            {{$_sejour->_ref_etablissement_provenance}}
        {{/if}}
    {{else}}
        -
    {{/if}}
</td>

{{if $canPlanningOp->read && $flag_dmp}}
    <td {{if $print_global}}style="display: none;"{{/if}}>
        {{mb_include module=dmp template=inc_button_dmp sejour=$_sejour compact=true}}
    </td>
{{/if}}

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    <td {{if $print_global}}style="display: none;"{{/if}} class="me-ws-wrap me-text-align-center">
        {{mb_include module=appFineClient template=inc_buttons_create_add_appfine refresh=0 _object=$_sejour loadJS=0}}
        {{mb_include module=appFineClient template=inc_status_folder _sejour_appFine=$_sejour loadJS=0}}
    </td>
{{/if}}

{{if "dPplanningOp CSejour use_phone"|gconf}}
    <td class="button">
        {{mb_include module=planningOp template=vw_appel_sejour type=admission sejour=$_sejour}}
    </td>
{{/if}}

<td class="text">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien classe="me-wrapped"}}
</td>

{{* DHE *}}
{{if $canPlanningOp->read}}
    <td {{if $print_global}}style="display: none;"{{/if}}>
        <a class="action me-planif-icon me-button actionPat notext me-margin-2" title="Modifier le séjour"
           href="#editDHE"
           onclick="Sejour.editModal({{$_sejour->_id}}, 0, 0, Admissions.reloadAdmissionLine.curry('{{$_sejour->_id}}')); return false;">
            <img src="images/icons/planning.png"/>
        </a>
        {{if $number_op == 1}}
            {{foreach from=$_sejour->_ref_operations item=_op}}
                <button class="print notext me-margin-2"
                        title="{{tr}}admissions-action-Print the DHE from the intervention{{/tr}}"
                        onclick="Admissions.printDHE('operation_id', {{$_op->_id}}); return false;">
                </button>
            {{/foreach}}
        {{elseif $number_op > 1}}
            <button class="print notext me-margin-2"
                    title="{{tr}}admissions-action-Print the DHE from the intervention{{/tr}}"
                    onclick="Admissions.chooseDHE('{{$_sejour->_id}}');" class="button print">
                {{tr}}Print{{/tr}}</button>
        {{else}}
            <button class="print notext me-margin-2" title="{{tr}}admissions-action-Print the DHE of the stay{{/tr}}"
                    onclick="Admissions.printDHE('sejour_id', {{$_sejour->_id}}); return false;">
            </button>
        {{/if}}
    </td>
{{/if}}
<td class="me-ws-wrap">
    {{assign var=first_operation value=$_sejour->_ref_first_operation}}
    {{if $_sejour->_passage_bloc}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$first_operation->_guid}}');">
            {{$_sejour->_passage_bloc|date_format:$conf.datetime}}
        </span>
    {{else}}
        {{tr}}COperation.none{{/tr}}
    {{/if}}
</td>
<td>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
    {{if $_sejour->presence_confidentielle}}
        {{mb_include module=planningOp template=inc_badge_sejour_conf}}
        <br/>
    {{/if}}
      {{$_sejour->entree_prevue|date_format:$conf.time}}
    <br/>
    {{$_sejour->type|upper|truncate:1:"":true}}
      {{$_sejour->_ref_operations|@count}} Int.
  </span>
</td>

<td class="me-ws-wrap me-min-w120">
    {{if !($_sejour->type == 'exte') && !($_sejour->type == 'consult') && $_sejour->annule != 1}}
        {{if !$print_global}}
            {{mb_include template=inc_form_prestations sejour=$_sejour edit=$canAdmissions->edit with_print=1 only_souhait=1}}
        {{/if}}

        {{mb_include module=hospi template=inc_placement_sejour sejour=$_sejour which="first"}}
    {{/if}}
</td>

<td {{if $print_global}}style="display: none;"{{/if}} class="me-ws-wrap">
    {{if !isset($reloadLine|smarty:nodefaults) || (isset($reloadLine|smarty:nodefaults) && $reloadLine)}}
        {{if $single_line}}
            {{mb_include module=forms template=inc_widget_ex_class_register object=$_sejour event_name=preparation_entree cssStyle="display: inline-block;"}}
        {{else}}
            {{mb_include module=forms template=inc_widget_ex_class_register_multiple object=$_sejour cssStyle="display: inline-block;"}}
        {{/if}}
    {{/if}}

    {{if $canPlanningOp->read}}
        <a href="#showDocs" title="{{tr}}admissions-action-Display document and file|pl{{/tr}}" class="button"
           onclick="Admissions.showDocs('{{$_sejour->_id}}')">
            <i class="far fa-file" aria-hidden="true"></i>
            {{tr}}CCompteRendu|pl{{/tr}}
        </a>
    {{/if}}
</td>

<td {{if $print_global}}style="display: none;"{{/if}} class="me-text-align-center">
    {{if $canAdmissions->edit}}
        <form name="editSaisFrm{{$_sejour->_id}}" action="?" method="post" class="prepared">
            <input type="hidden" name="m" value="planningOp"/>
            <input type="hidden" name="dosql" value="do_sejour_aed"/>
            {{mb_key object=$_sejour}}
            <input type="hidden" name="patient_id" value="{{$_sejour->patient_id}}"/>

            {{if !$_sejour->entree_preparee}}
                <input type="hidden" name="entree_preparee" value="1"/>
                <input type="hidden" name="_entree_preparee_trigger" value="1"/>
                {{mb_field object=$_sejour field=type hidden=true}}
                <button class="tick" type="button" onclick="submitAdmission(this.form, 1);">
                    {{tr}}CSejour-entree_preparee{{/tr}}
                </button>
            {{else}}
                <input type="hidden" name="entree_preparee" value="0"/>
                <button class="cancel" type="button" onclick="submitAdmission(this.form, 1);">
                    {{tr}}Cancel{{/tr}}
                </button>
            {{/if}}

            {{if ($_sejour->entree_modifiee == 1) && ($conf.dPplanningOp.CSejour.entree_modifiee == 1)}}
                <img src="images/icons/warning.png" title="Le dossier a été modifié, il faut le préparer"/>
            {{/if}}
        </form>
    {{else}}
        {{mb_value object=$_sejour field="entree_preparee"}}
    {{/if}}
</td>

<td class="text">
    {{foreach from=$_sejour->_ref_operations item=_op}}
        {{assign var=dossier_anesth value=$_op->_ref_consult_anesth}}
        {{if $dossier_anesth->_id}}
            {{assign var=consult_anesth value=$dossier_anesth->_ref_consultation}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$consult_anesth->_guid}}');" class="me-ws-nowrap">
        {{mb_value object=$consult_anesth field=_date}}
                {{if $consult_anesth->chrono == 64}}
                    <i class='me-icon tick me-success' title="{{tr}}CConsultation-Consultation completed{{/tr}}"></i>
                {{/if}}
      </span>
            <br/>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult_anesth->_ref_praticien classe="me-wrapped"}}
        {{/if}}
    {{/foreach}}
</td>

<td class="button">
    {{if $_sejour->_couvert_c2s}}
        <div><strong>C2S</strong></div>
    {{/if}}
    {{if $_sejour->_couvert_ald}}
        <div><strong {{if $_sejour->ald}}style="color: red;"{{/if}}>ALD</strong></div>
    {{/if}}
</td>

{{if $app->user_prefs.show_dh_admissions}}
    {{mb_include module=admissions template=inc_operations_depassement operations=$_sejour->_ref_operations sejour=$_sejour}}
{{/if}}
