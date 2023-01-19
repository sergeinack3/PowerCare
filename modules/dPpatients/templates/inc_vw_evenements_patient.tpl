{{*
 * @package Mediboard\dPpatients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if 'oxCabinet'|module_active}}
    {{mb_script module=oxCabinet script=appel_sih ajax=$ajax}}
    {{mb_script module=oxCabinet script=timeline_implement ajax=$ajax}}
{{/if}}

{{mb_default var=edit_mode value=1}}
{{mb_default var=view_mode value=0}}
{{mb_default var=print value=0}}
{{mb_default var=use_table value=1}}

{{assign var=mode_DHE value="oxCabinet DHE mode_DHE"|gconf}}

{{if $dossier_medical}}
  {{assign var=evenements_patient value=$dossier_medical->_ref_evenements_patient}}
{{/if}}

<script>
  Main.add(function () {
    var evenement_dossier = $('evenement_dossier');
    if (evenement_dossier) {
      evenement_dossier.up("fieldset").down("span.count_elts").update("({{$evenements_patient|@count}})");
    }
  });
</script>
{{if $view_mode}}
<div id="list_evenements">
  <fieldset>
    <legend>
      {{tr}}mod-dPpatients-tab-ajax_edit_evenements_patient{{/tr}}
    </legend>

    {{/if}}

    {{if !$view_mode}}
      <form name="mailing_send">
    {{/if}}

    {{if $use_table}}
    <table class="tbl me-no-align me-no-box-shadow">
      {{/if}}
      {{if $view_mode}}
        <tr>
          <td colspan="11">
            {{if $patient->_can->edit}}
              <button type="button" class="new me-primary" onclick="EvtPatient.editEvenements('{{$patient->_id}}','0');">
                {{tr}}CEvenementPatient-action-Add a patient event{{/tr}}
              </button>
            {{/if}}
          </td>
        </tr>
      {{/if}}
      <tr>
        <th class="narrow">{{mb_title class=CEvenementPatient field=date}}</th>
        <th>{{mb_title class=CEvenementPatient field=libelle}}</th>
        <th>{{mb_title class=CEvenementPatient field=type_evenement_patient_id}}</th>
        {{if $edit_mode == 1}}
          <th>{{tr}}CEvenementPatient-document|pl{{/tr}}</th>
          {{if $app->user_prefs.tamm_manage_billing || ($app->user_prefs.UISTYLE != "pluus")}}
            <th>{{tr}}CEvenementPatient-Invoice{{/tr}}</th>
          {{/if}}
          <th>{{mb_title class=CEvenementPatient field=rappel}}</th>
          <th>{{mb_title class=CEvenementPatient field=alerter}}</th>
          <th class="narrow">Notification</th>
          {{if $view_mode}}
            <th class="narrow"></th>
          {{/if}}
        {{elseif !$use_table}}
          <th>{{mb_title class=CEvenementPatient field=description}}</th>
          <th>{{tr}}CPatient{{/tr}}</th>
          <th class="narrow">{{mb_title class=CPatient field=naissance}}</th>

          {{if $mailing}}
            <th class="narrow">{{tr}}Mailing{{/tr}}</th>
            <th class="narrow" colspan="2">{{tr}}CPatientEventSentMail{{/tr}}</th>
          {{else}}
            <th class="narrow">{{tr}}common-Notification|pl{{/tr}}</th>
            <th class="narrow">{{tr}}Treated{{/tr}}</th>
            {{if "loinc"|module_active || "snomed"|module_active}}
              <th class="narrow" title="{{tr}}CAntecedent-Nomenclature-desc{{/tr}}">{{tr}}CAntecedent-Nomenclature{{/tr}}</th>
            {{/if}}
          {{/if}}
        {{/if}}
      </tr>

      {{if $mailing}}
        <tr>
          <th colspan="6"></th>
          <th class="me-text-align-right">
            <input type="checkbox" onclick="$$('input[name=mailing_select]').invoke('setValue', this.checked);">
          </th>
          <th>{{tr}}CPatientEventSentMail.type.email{{/tr}}</th>
          <th>{{tr}}CPatientEventSentMail.type.postal{{/tr}}</th>
        </tr>
      {{/if}}

      {{foreach from=$evenements_patient item=_evenement}}
        <tr {{if $_evenement->cancel}}class="hatching"{{/if}}>
          {{* Date *}}
          <td style="text-align: right;">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_guid}}')">
              {{mb_value object=$_evenement field=date}}
            </span>
          </td>

          {{* Title, Practitioner, Description *}}
          <td class="text">
            {{mb_value object=$_evenement field=libelle}}

            {{if $_evenement->praticien_id}}
              <br/>
              <span class="compact">
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_evenement->_ref_praticien}}
              </span>
            {{/if}}

            {{if $_evenement->description && $use_table}}
              <br/>
              <span class="compact">{{mb_value object=$_evenement field=description}}</span>
            {{/if}}
          </td>

         {{* Event type *}}
          {{if $_evenement->type == "evt"}}
            <td class="text">
              {{$_evenement->_ref_type_evenement_patient}}
            </td>
          {{elseif $edit_mode}}
            <td>
              {{if 'oxCabinet'|module_active && !$print}}
                <button type="button" class="edit notext"
                        onclick="AppelSIH.modifyDistantEvt('{{$_evenement->_id}}', '{{$mode_DHE}}');">
                    {{tr}}Modify{{/tr}}
                </button>
              {{/if}}

              <span class="texticon texticon-exc">
                {{mb_value object=$_evenement field=type}}
              </span>
              {{if $_evenement->parent_id}}
                <br/><span class="texticon texticon-at">
                    {{mb_label object=$_evenement field=parent_id}}: {{$_evenement->loadRefParent()}}
                  </span>
              {{/if}}
            </td>
          {{/if}}

         {{* Display Tamm-SIH distant document access button *}}
          {{if $edit_mode == 1 && $_evenement->type != "evt"}}
            <td>
              {{if $mode_DHE === "tamm"}}
                <button type="button" class="far fa-copy me-primary"
                        onclick="TdBTamm.modalDocuments('{{$patient->_id}}', null, null, '{{$_evenement->_guid}}', Control.Modal.refresh);">
                  {{tr}}CDocumentItem-action-Copy a document{{/tr}}
                </button>
                <button class="add" onclick="Control.Modal.close(); DocumentV2.addDocument('{{$_evenement->_guid}}', '{{$patient->_id}}');">
                  {{tr}}CPatient-action-add-document{{/tr}}
                </button>
              {{elseif $mode_DHE === "appel_contextuel" && $_evenement->_ref_context_id400->_id && $_evenement->_ref_sih_id400->_id}}
                <button class="add" onclick="TdBTamm.addDistantDoc(this)" data-patient-id="{{$patient->_id}}"
                        data-context-guid="{{$_evenement->_ref_context_id400->id400}}"
                        data-sih-id="{{$_evenement->_ref_sih_id400->id400}}"
                        data-cabinet-id="{{$_evenement->_ref_cabinet_id400->id400}}"
                        data-evenement-id="{{$_evenement->_id}}">
                  {{tr}}CPatient-action-add-document{{/tr}}
                </button>
              {{/if}}
            </td>
            <td colspan="5"></td>
          {{/if}}

          {{* Edit mode *}}
          {{if $edit_mode == 1 && $_evenement->type == "evt"}}
            <td>
              {{assign var=patient_id value=$dossier_medical->_ref_object->_id}}
              {{if $view_mode && $patient->_can->edit}}
                <button type="button" class="far fa-copy me-primary"
                        onclick="TdBTamm.modalDocuments('{{$patient->_id}}', null, null, '{{$_evenement->_guid}}', Control.Modal.refresh);">
                  {{tr}}CDocumentItem-action-Copy a document{{/tr}}
                </button>
                {{mb_include module=patients template=inc_button_add_doc context_guid=$_evenement->_guid patient=$dossier_medical->_ref_object
                callback="function(){EvtPatient.refreshContentEvenements('`$patient->_id`');}"}}
                {{mb_include module=patients template=inc_widget_count_documents object=$_evenement show_object_class=false
                callback="function(){EvtPatient.refreshContentEvenements('`$patient->_id`');}"}}
              {{else}}
                {{mb_include module=patients template=inc_widget_count_documents object=$_evenement show_object_class=false
                callback="function() { refreshWidget('evenement', 'evenement_dossier')}"}}
              {{/if}}
            </td>
            {{if $app->user_prefs.tamm_manage_billing || ($app->user_prefs.UISTYLE != "pluus")}}
              <td>
                {{if $_evenement->praticien_id && $patient->_can->edit}}
                  {{mb_include module=facturation template=inc_button_fact_evt object=$_evenement}}
                {{/if}}
              </td>
            {{/if}}
            <td>
              {{mb_value object=$_evenement field=rappel}}
            </td>
            <td>
              {{mb_value object=$_evenement field=alerter}}
            </td>
            <td class="text-align-center">
              {{if $_evenement->type_evenement_patient_id && $_evenement->_ref_type_evenement_patient->notification}}
                {{if $_evenement->_ref_notification && $_evenement->_ref_notification->_id
                && ($_evenement->_ref_notification->_message->status == 'sent' || $_evenement->_ref_notification->_message->status == 'delivered')}}
                  {{assign var=notif_send_at value=$_evenement->_ref_notification->datetime|date_format:$conf.date}}
                  <i class="fa fa-lg fa-envelope" style="color: forestgreen; cursor: help;"
                     title="{{tr var1="2015"}}CEvenementPatient.notif_send_at{{/tr}}"></i>
                {{else}}
                  <i class="fa fa-lg fa-envelope" style="color: darkslategrey; cursor: help;"
                     title="{{tr}}CEvenementPatient.notif_not_send{{/tr}}"></i>
                {{/if}}
              {{else}}
                <i class="fa fa-lg fa-times" style="color: firebrick; cursor: help;"
                   title="{{tr}}CEvenementPatient.notif_empty{{/tr}}"></i>
              {{/if}}
            </td>
            {{if $view_mode}}
              <td style="text-align: center">
                {{if $_evenement->owner_id == $app->user_id || $app->user_id == $_evenement->praticien_id || $app->_ref_user->isAdmin()}}
                  <form name="delEvenement{{$_evenement->_id}}" method="post">
                    {{mb_class class=CEvenementPatient}}
                    {{mb_key object=$_evenement}}

                    <button type="button" class="notext trash me-tertiary me-dark"
                            onclick="confirmDeletion(this.form, {typeName: 'l\'evenement', objName: '{{$_evenement->libelle|smarty:nodefaults|JSAttribute}}'},
                              EvtPatient.refreshEvenementsPatient.curry('{{$patient->_id}}'))">{{tr}}Delete{{/tr}}</button>
                  </form>
                  <button type="button" class="edit notext me-tertiary"
                          onclick="EvtPatient.editEvenements('{{$patient->_id}}','{{$_evenement->_id}}');">{{tr}}Edit{{/tr}}</button>
                {{/if}}
                {{assign var=user_courant_id value=$app->user_id}}
                {{if $_evenement->alerter && !$_evenement->traitement_user_id && isset($_evenement->_ref_users.$user_courant_id|smarty:nodefaults)}}
                  <form name="traite_alerte_Evenement{{$_evenement->_guid}}" method="post">
                    {{mb_class class=CEvenementPatient}}
                    {{mb_key object=$_evenement}}
                    <input type="hidden" name="traitement_user_id" value="{{$app->user_id}}" />
                    <button type="button" class="tick notext me-secondary"
                            onclick="onSubmitFormAjax(this.form, EvtPatient.refreshEvenementsPatient.curry('{{$patient->_id}}'));">
                      {{tr}}Treat-Alerte{{/tr}}
                    </button>
                  </form>
                {{/if}}
              </td>
            {{/if}}

          {{* Not edit mode *}}
          {{elseif !$use_table}}
            {{* Description *}}
            <td>{{mb_value object=$_evenement field=description}}</td>

            {{* Patient *}}
            <td>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_ref_patient->_guid}}')">
              {{$_evenement->_ref_patient}}
              </span>
            </td>

            {{* Date of birth *}}
            <td>{{mb_value object=$_evenement->_ref_patient field=naissance}}</td>

            {{if $mailing}}
              {{* Mailing *}}
              <td class="me-text-align-right">
                {{if $_evenement->_ref_type_evenement_patient->mailing_model_id}}
                  {{if !$_evenement->_ref_patient->email || $_evenement->_ref_patient->allow_email !== '1' ||
                  !$_evenement->_ref_patient->cp || !$_evenement->_ref_patient->ville}}
                    <i class="fas fa-exclamation-triangle" style="color: red;" title="{{tr}}CPatientEventSendMail-Missing mail or postal address{{/tr}}"></i>
                  {{/if}}
                  <input type="checkbox" name="mailing_select" value="{{$_evenement->_id}}">
                {{/if}}
              </td>
              {{assign var=email value=false}}
              {{assign var=postal value=false}}
              {{if $_evenement->_refs_sent_mail && count($_evenement->_refs_sent_mail) > 0}}
                {{foreach from=$_evenement->_refs_sent_mail item=_sent_mail}}
                  {{if $_sent_mail->type == 'email'}}
                    {{assign var=email value=$_sent_mail}}
                  {{/if}}
                  {{if $_sent_mail->type == 'postal'}}
                    {{assign var=postal value=$_sent_mail}}
                  {{/if}}
                {{/foreach}}
              {{/if}}

              <td class="narrow me-text-align-center">
                {{if $email}}
                  <i class="fas fa-check" style="color: green" title="{{tr}}CPatientEventSentMail-Sent the{{/tr}} {{$email->datetime|date_format:$conf.datetime}}"></i>
                {{else}}
                  <i class="fas fa-times" style="color: red;"></i>
                {{/if}}
              </td>
              <td class="narrow me-text-align-center">
                {{if $postal}}
                  <i class="fas fa-check"
                     style="color: green"
                     title="{{tr}}CPatientEventSentMail-Sent the{{/tr}} {{$postal->datetime|date_format:$conf.datetime}}"></i>
                {{else}}
                  <i class="fas fa-times" style="color: red;"></i>
                {{/if}}
              </td>
            {{else}}
              {{* Notification status *}}
              <td class="me-text-align-center">
                {{if $_evenement->type_evenement_patient_id && $_evenement->_ref_type_evenement_patient->notification}}
                  {{if $_evenement->_ref_notification && ($_evenement->_ref_notification->status == 'sent' || $_evenement->_ref_notification->status == 'delivered')}}
                    <i class="fa fa-lg fa-envelope not-printable" style="color: forestgreen; cursor: help;"
                       title="{{tr}}CEvenementPatient.notif_send{{/tr}}"></i>
                    <i class="only-printable" style="color: forestgreen;">{{tr}}CEvenementPatient.notif_send{{/tr}}</i>
                  {{else}}
                    <i class="fa fa-lg fa-envelope not-printable" style="color: darkslategrey; cursor: help;"
                       title="{{tr}}CEvenementPatient.notif_not_send{{/tr}}"></i>
                    <i class="only-printable" style="color: darkslategrey;">{{tr}}CEvenementPatient.notif_not_send{{/tr}}</i>
                  {{/if}}
                {{else}}
                  <i class="fa fa-lg fa-times not-printable" style="color: firebrick; cursor: help;"
                     title="{{tr}}CEvenementPatient.notif_empty{{/tr}}"></i>
                  <i class="only-printable" style="color: firebrick; cursor: help;">X</i>
                {{/if}}
              </td>

              {{* Action *}}
              {{if !$_evenement->traitement_user_id}}
                <td class="me-text-align-center">
                  <button class="process-button me-secondary me-notext"
                          style="padding: 0 3px 0 3px;"
                          onclick="AlertEvent.processEvent(this)"
                          type="button"
                          data-id="{{$_evenement->_id}}">
                    <i class="fas fa-check"></i>
                  </button>
                </td>
              {{else}}
                <td class="me-text-align-center"><i class="fas fa-check" style="color: green"></i></td>
              {{/if}}

              {{* Snomed and Loinc codes *}}
              {{if "loinc"|module_active || "snomed"|module_active}}
                <td>
                  {{if "loinc"|module_active}}
                    {{mb_include module=loinc  template=inc_vw_tag_loinc  object=$_evenement}}
                  {{/if}}

                  {{if "snomed"|module_active}}
                    {{mb_include module=snomed template=inc_vw_tag_snomed object=$_evenement}}
                  {{/if}}
                </td>
              {{/if}}
            {{/if}}
          {{/if}}
          </tr>
        {{foreachelse}}
        <tr>
          <td class="empty text" colspan="11">{{tr}}CEvenementPatient.none{{/tr}}</td>
        </tr>
      {{/foreach}}
      {{if $use_table}}
    </table>
    {{/if}}

    {{if !$view_mode}}
      </form>
    {{/if}}

    {{if $view_mode}}
  </fieldset>
</div>
{{/if}}
