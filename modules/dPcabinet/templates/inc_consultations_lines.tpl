{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=see_color value=0}}
<tr>
    <th class="title" colspan="11">
        <strong>
            {{if $plageSel->_id || $plageSel->libelle == "automatique_intervention"}}
                {{if $plageSel->libelle != "automatique_intervention"}}
                  <button class="print notext me-tertiary me-dark" style="float: right;"
                          onclick="PlageConsultation.print({{$plageSel->_id}})">
                      {{tr}}Print{{/tr}}
                  </button>
                  <button class="new notext me-primary" type="button" style="float: right;"
                          onclick="Consultation.editRDVModal(0, '{{$plageSel->chir_id}}', '{{$plageSel->_id}}');">
                      {{tr}}Add{{/tr}}
                  </button>
                {{/if}}
                <div>
                    {{mb_include module=system template=inc_object_notes object=$plageSel}}
                    {{$plageSel->date|date_format:$conf.longdate}}
                </div>
                <div>
                    {{$plageSel->debut|date_format:$conf.time}}
                    {{tr}}To{{/tr}} {{$plageSel->fin|date_format:$conf.time}}
                </div>
                {{if $plageSel->chir_id != $chirSel}}
                    {{tr}}CPlageConsult.remplacement_of{{/tr}} {{$plageSel->_ref_chir->_view}}
                {{elseif $plageSel->remplacant_id}}
                    {{tr}}CConsultation.replaced_by{{/tr}} {{$plageSel->_ref_remplacant->_view}}
                {{elseif $plageSel->pour_compte_id}}
                    {{tr}}CPlageConsult-pour_compte_of{{/tr}} {{$plageSel->_ref_pour_compte->_view}}
                {{/if}}
            {{else}}
                {{tr}}CPlageconsult.none{{/tr}}
            {{/if}}
        </strong>
    </th>
</tr>

<tr>
    <th class="narrow">{{mb_title class=CConsultation field=heure}}</th>
    <th>{{mb_title class=CConsultation field=patient_id}}</th>
    <th>{{mb_title class=CConsultation field=motif}}</th>
    <th>{{mb_title class=CConsultation field=rques}}</th>
    <th id="inc_consult_notify_arrivate" colspan="2">{{tr}}CConsultation-rdv{{/tr}}</th>
    <th id="th_inc_consult_etat" colspan="2">{{mb_title class=CConsultation field=_etat}}</th>
    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
        <th>{{tr}}CAppFine-court{{/tr}}</th>
    {{/if}}
</tr>
{{foreach from=$plageSel->_items item=_item}}
  {{if $_item->_class == "CConsultation" }}
      {{assign var=_consult value =$_item}}
    <tr {{if $_consult->chrono == $_consult|const:'TERMINE'}} class="hatching"{{/if}}>
        {{assign var=consult_id    value=$_consult->_id}}
        {{assign var=patient       value=$_consult->_ref_patient}}
        {{assign var=sejour        value=$_consult->_ref_sejour}}

        {{assign var=href_consult  value="?m=$m&tab=edit_consultation&selConsult=$consult_id"}}
        {{assign var=href_planning value="?m=$m&tab=edit_planning&consultation_id=$consult_id"}}
        {{assign var=href_patient  value="?m=patients&tab=vw_edit_patients&patient_id=$patient->_id"}}

        {{if $m === "oxCabinet"}}
            {{assign var=href_consult value="?m=$m&tab=edit_consultation&consult_id=$consult_id"}}
        {{/if}}

        {{assign var="classe" value=""}}
        {{if !$patient->_id}}
            {{assign var="classe" value="pause_consult"}}
        {{elseif $_consult->premiere}}
            {{assign var="classe" value="premiere_consult"}}
        {{elseif $_consult->derniere}}
            {{assign var="classe" value="derniere_consult"}}
        {{elseif $sejour->_id}}
            {{assign var="classe" value="consult_sejour"}}
        {{/if}}

        <td class="{{$classe}}"
            style="{{if $see_color}}position: relative;{{/if}}">
            {{if $see_color}}
                <div class="me-consult-line-border" style="background-color: #{{$plageSel->color}}"></div>
            {{/if}}
            <div style="float: left">
                {{if $patient->_id}}
                    <a href="#" onclick="if (IdentityValidator.active) {
          IdentityValidator.manage('{{$patient->status}}', {{$patient->_id}}, Consultation.edit.curry({{$_consult->_id}}));
          } else {
          Consultation.edit('{{$_consult->_id}}');
          }">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
            {{$_consult->heure|date_format:$conf.time}}
          </span>
                    </a>
                {{else}}
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
          {{mb_value object=$_consult field=heure}}
        </span>
                {{/if}}
            </div>
        </td>

        <td class="text {{$classe}}">
            {{if !$patient->_id && !$_consult->no_patient}}
                [{{tr}}CConsultation-PAUSE{{/tr}}]
            {{elseif $_consult->groupee && $_consult->no_patient}}
                [{{tr}}CConsultation-MEETING{{/tr}}]
            {{else}}
                {{mb_include module=system template=inc_object_notes object=$patient float="right"}}
                {{if $_consult->_alert_docs}}
                    <i class="far fa-file" style="float: right; margin-right: 5px; font-size: 1.3em;"
                       title="{{tr}}CCompteRendu-alert_docs_object{{/tr}}"></i>
                {{/if}}
                {{if $_consult->visite_domicile}}
                    <i class="fa fa-home" style="font-size: 1.2em;"
                       title="{{tr}}CConsultation-visite_domicile-desc{{/tr}}"></i>
                {{/if}}
                <a href="#" onclick="
          if (IdentityValidator.active) {
              IdentityValidator.manage('{{$patient->status}}', {{$patient->_id}}, Consultation.edit.curry({{$_consult->_id}}));
          } else {
              Consultation.edit('{{$_consult->_id}}');
          }" style="display: inline-block;">
                    {{mb_value object=$patient}}
                </a>
                {{if $sejour->entree_reelle}}
                    <span
                      onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">({{$sejour->entree_reelle|date_format:$conf.time}})</span>
                {{/if}}
            {{/if}}
        </td>

        <td class="text {{$classe}}">
            {{mb_include module=cabinet template=inc_icone_dhe_associe}}

            {{assign var=categorie value=$_consult->_ref_categorie}}

            {{if $categorie->_id}}
                <div>
                    {{mb_include module=cabinet template=inc_icone_categorie_consult
                    consultation=$_consult
                    categorie=$categorie
                    onclick="IconeSelector.changeCategory('$consult_id', this)"
                    display_name=true
                    }}
                </div>
            {{/if}}

            {{if $patient->_id}}
                <a href="{{$href_consult}}" title="{{$_consult->motif}}">
                    {{mb_value object=$_consult field=motif truncate=35}}
                </a>
            {{else}}
                {{mb_value object=$_consult field=motif truncate=35}}
            {{/if}}
        </td>
        <td class="text {{$classe}}">
            {{if $patient->_id}}
                <a href="{{$href_consult}}" title="{{$_consult->rques}}">
                    {{mb_value object=$_consult field=rques truncate=35}}
                </a>
            {{else}}
                {{mb_value object=$_consult field=rques truncate=35}}
            {{/if}}
            {{if "3333tel"|module_active}}
                {{mb_include module=3333tel template=inc_check_3333tel object=$_consult tiny=1}}
            {{/if}}
        </td>
        <td class="{{$classe}} narrow">
            <form name="etatFrm{{$_consult->_id}}" action="?m=dPcabinet" method="post">
                <input type="hidden" name="m" value="dPcabinet"/>
                <input type="hidden" name="dosql" value="do_consultation_aed"/>
                {{mb_key object=$_consult}}
                <input type="hidden" name="chrono" value="{{$_consult|const:'PATIENT_ARRIVE'}}"/>
                <input type="hidden" name="arrivee" value=""/>
            </form>

            <div id="form-motif_annulation-{{$_consult->_id}}" style="display: none">
                <form name="cancelFrm{{$_consult->_id}}" action="?m=dPcabinet" method="post" onsubmit="">
                    <input type="hidden" name="m" value="dPcabinet"/>
                    <input type="hidden" name="dosql" value="do_consultation_aed"/>
                    {{mb_key   object=$_consult}}
                    <input type="hidden" name="chrono" value="{{$_consult|const:'TERMINE'}}"/>
                    <input type="hidden" name="annule" value="1"/>
                    <table class="tbl main">
                        <tr>
                            <th colspan="2" class="title">
                                {{$_consult->_view}}
                                <button type="button" class="cancel notext me-tertiary me-dark"
                                        onclick="Control.Modal.close();"
                                        style="float:right;">{{tr}}Close{{/tr}}</button>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2" class="text">
                                <div class="small-warning">{{tr}}CConsultation-confirm-cancel-1{{/tr}}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right">
                                <strong>{{mb_label object=$_consult field=motif_annulation}}</strong></td>
                            <td>{{mb_field object=$_consult field=motif_annulation typeEnum="radio" separator="<br/>"}}</td>
                        </tr>
                        <tr>
                            <td style="text-align: right"><strong>{{mb_label object=$_consult field=rques}}</strong>
                            </td>
                            <td>{{mb_field object=$_consult field=rques}}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="button">
                                <button type="button" class="tick me-tertiary" onclick="this.form.submit();"
                                        id="submit_cancelFrm{{$_consult->_id}}">{{tr}}Validate{{/tr}}</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            {{if $_consult->_can->edit}}
                <a class="action me-planif-icon me-button me-box-sizing-border notext" href="#"
                   onclick="Consultation.editRDVModal('{{$_consult->_id}}', '{{$plageSel->chir_id}}', '{{$plageSel->_id}}', '{{$patient->_id}}')">
                    <img src="images/icons/planning.png" title="{{tr}}CConsultation-modify_rdv{{/tr}}"/>
                </a>
            {{/if}}
            {{if !$_consult->annule && $_consult->_can->edit}}
                {{if $_consult->chrono == $_consult|const:'PLANIFIE' && $patient->_id}}
                    <button class="tick button notext me-secondary" type="button"
                            onclick="var callback = putArrivee.curry(document.etatFrm{{$_consult->_id}});
                              if (window.IdentityValidator) {
                              IdentityValidator.manage('{{$patient->status}}', '{{$patient->_id}}', callback);
                              }
                              else {
                              callback();
                              }">
                        {{tr}}CConsultation-notify_arrive-patient{{/tr}}
                    </button>
                    <button type="button" class="cancel button notext me-tertiary"
                            onclick="cancelRdv(document.cancelFrm{{$_consult->_id}});">
                        {{tr}}CConsultation-cancel_rdv{{/tr}}
                    </button>
                {{elseif $patient->_id}}
                    <form name="cancel_arrive_{{$_consult->_id}}" action="?m=dPcabinet" method="post">
                        <input type="hidden" name="m" value="cabinet"/>
                        <input type="hidden" name="dosql" value="do_consultation_aed"/>
                        {{mb_key object=$_consult}}
                        <input type="hidden" name="annule" value="0"/>
                        <input type="hidden" name="chrono" value="{{$_consult|const:'PLANIFIE'}}"/>
                    </form>
                    <button type="button" class="tick_cancel notext"
                            onclick="cancelArrivee(document.cancel_arrive_{{$_consult->_id}})">
                        {{tr}}CConsultation-cancel_arrive{{/tr}}
                    </button>
                {{/if}}
            {{elseif $_consult->_can->edit}}
                <form name="cancel_annulation_{{$_consult->_id}}" action="?m=dPcabinet" method="post">
                    <input type="hidden" name="m" value="cabinet"/>
                    <input type="hidden" name="dosql" value="do_consultation_aed"/>
                    {{mb_key object=$_consult}}
                    <input type="hidden" name="chrono" value="{{$_consult|const:'PLANIFIE'}}"/>
                    <input type="hidden" name="annule" value="0"/>
                </form>
                <button class="undo button notext" type="button"
                        onclick="undoCancellation(document.cancel_annulation_{{$_consult->_id}});">
                    {{tr}}Restore{{/tr}}
                </button>
            {{/if}}

            {{if $patient->allow_sms_notification && 'notifications'|module_active && @$modules.notifications->_can->read}}
                {{mb_include module=notifications template=inc_icone_notification_send object=$_consult}}
            {{/if}}
        </td>
        <td class="{{$classe}}">
            {{if $_consult->duree > 1}}
                ({{math equation="a*b" a=$_consult->duree b=$_consult->_ref_plageconsult->_freq}} min)
            {{/if}}
        </td>
        <td class="{{if $_consult->annule}}error{{/if}} {{$classe}} narrow">
            {{if $patient->_id}}
                {{if $_consult->annule}}
                    {{tr}}{{$_consult->motif_annulation}}{{/tr}}
                {{else}}
                    {{$_consult->_etat}}
                {{/if}}
                {{if 'teleconsultation'|module_active &&  $_consult->teleconsultation}}
                  {{mb_include module=teleconsultation template=inc_shortcut_teleconsultation tag="i"}}
                {{/if}}
            {{/if}}
        </td>
        <td class="{{if $_consult->annule}}error{{/if}} {{$classe}} narrow" style="text-align: center">
            <i {{if $_consult->_ref_facture && $_consult->_ref_facture->_id}}
                class="texticon texticon-ok"
                title="{{tr}}CConsultation-has_facture{{/tr}}"
              {{elseif $_consult->_ref_facture && !$_consult->_ref_facture->_id}}
                class="texticon texticon-gray"
                title="{{tr}}CConsultation-has_no_facture{{/tr}}"
              {{/if}}>F</i>
        </td>
        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
            <td>
                {{mb_include module=appFineClient template=inc_buttons_create_add_appfine _object=$_consult refresh=0}}
            </td>
        {{/if}}
    </tr>
  {{else}}
      {{mb_include module=oxCabinet template=inc_evenement_line object=$_item}}
  {{/if}}
    {{foreachelse}}
    <tr>
        <td colspan="{{if "appFineClient"|module_active}}8{{else}}7{{/if}}" class="empty"
            {{if $see_color}}style="position: relative;"{{/if}}>
            {{tr}}CConsultation.none{{/tr}}
        </td>
    </tr>
{{/foreach}}
