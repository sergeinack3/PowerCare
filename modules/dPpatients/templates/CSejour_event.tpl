{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=selected_guid     value=''}}
{{mb_default var=show_files_btn    value=0}}
{{mb_default var=td_colspan        value=1}}
{{mb_default var=op_colspan        value=1}}
{{mb_default var=compact           value=0}}
{{mb_default var=padding           value=""}}
{{mb_default var=_event_icon       value=""}}
{{mb_default var=show_actions      value=true}}
{{mb_default var=show_multiple_NDA value=true}}

{{if isset($_event|smarty:nodefaults)}}
  {{assign var=_event_icon value=$_event.icon}}
{{/if}}

{{if !$_event_icon}}
  {{assign var=_event_icon value=$object->getEventIcon()}}
{{/if}}

{{if $object->group_id == $g || "dPpatients sharing multi_group"|gconf == "full"}}
  <tr  class="{{if $object->_guid == $selected_guid}}selected{{/if}} {{if $object->annule}}me-cancelled{{/if}}" id="{{$object->_guid}}"
    {{if isset($collision_sejour|smarty:nodefaults) && $object->_id == $collision_sejour}}
    style="border: 2px solid red;"
    {{elseif $object->_is_proche}}
    style="border: 2px solid blue;"
    {{/if}}>

    {{if $compact}}
      <td class="narrow" {{if $padding}}style="padding-left: {{$padding}}em;"{{/if}}>
        {{if $show_merge_chkbx && $can->admin && 'Ox\Mediboard\PlanningOp\CSejour::getAllowMerge'|static_call:null}}
          <input type="checkbox" name="objects_id[]" value="{{$object->_id}}" class="merge"
                 onclick="checkOnlyTwoSelected(this);" />
        {{/if}}
      </td>
    {{/if}}

    {{if $object->_canEdit && $show_actions}}
      {{mb_script module=planningOp script=sejour ajax=true}}
    {{/if}}
    <td colspan="{{$td_colspan}}" {{if !$compact && $padding}}style="padding-left: {{$padding}};"{{/if}}>
      {{if $object->_canEdit && $show_actions}}
        <a style="display: inline;" class="actionPat me-planif-icon me-button me-tertiary notext me-tertiary-low-emphasis" title="{{tr}}CSejour-title-modify{{/tr}}"
           href="#" onclick="Sejour.editModal('{{$object->_id}}');">
          <img src="images/icons/planning.png" alt="Planifier" />
        </a>
      {{elseif $show_actions}}
        <span style="display: none" class="me-inline me-margin-left-16 me-margin-right-16"></span>
      {{/if}}

      {{if $compact}}
        {{mb_include module=dPpatients template=inc_vw_event_icon event_icon=$_event_icon}}
        <a style="display: inline;" href="#{{$object->_guid}}"
           onclick="{{if $can_view_dossier_medical && !$app->user_prefs.limit_prise_rdv}}loadSejour('{{$object->_id}}');{{else}}viewCompleteItem('{{$object->_guid}}');{{/if}} ViewFullPatient.select(this);"
           {{if $can->admin}}style="padding-right: 14px;"{{/if}}>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
              {{$object->_shortview}}
            </span>
        </a>
      {{else}}
        {{mb_include module=dPpatients template=inc_vw_event_icon event_icon=$_event_icon}}

        {{assign var=multiple_NDA value=0}}
        {{assign var=NDA_sejour value=$object->_NDA}}

        {{if $show_multiple_NDA && isset($sejours_by_NDA.$NDA_sejour|smarty:nodefaults) && $sejours_by_NDA.$NDA_sejour|@count > 1}}
          {{assign var=multiple_NDA value=1}}
        {{/if}}

        {{if $multiple_NDA}}
          <span onmouseover="ObjectTooltip.createDOM(this, 'list_sejours_{{$object->_NDA}}');">
        {{/if}}

        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$object _show_numdoss_modal=1}}

        {{if $multiple_NDA}}
          </span>
          <div id="list_sejours_{{$object->_NDA}}" style="display: none;">
            <table class="form">
              {{foreach from=$sejours_by_NDA.$NDA_sejour item=_object}}
                {{mb_include module=patients template=CSejour_event show_multiple_NDA=false object=$_object}}
              {{/foreach}}
            </table>
          </div>
        {{/if}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
        {{$object->_shortview}}

          {{if $object->_nb_files_docs}}
            - ({{mb_include module=patients template=inc_detail_docitems_event}})
          {{/if}}
        </span>
      {{/if}}

      {{if $object->presence_confidentielle}}
        {{mb_include module=planningOp template=inc_badge_sejour_conf}}
      {{/if}}
    </td>

    <td colspan="{{$td_colspan}}" style="text-align: left;" {{if $object->annule}}class="cancelled"{{/if}}>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_praticien}}
      <div class="me-cancelled-text" style="display: none">{{tr}}CSejour-annule-court{{/tr}}</div>
    </td>

    {{if $compact}}
      <td class="narrow">
        {{if $show_merge_chkbx}}
          <button class="lookup notext compact me-tertiary" onclick="popEtatSejour('{{$object->_id}}');">{{tr}}Lookup{{/tr}}</button>
        {{/if}}
      </td>
    {{/if}}

    {{if $show_files_btn}}
      <td style="text-align:right;">
        {{if $object->_canRead}}
          {{if $isImedsInstalled}}
            <div onclick="view_labo_sejour('{{$object->_id}}');" style="float: left;">
              {{mb_include module=Imeds template=inc_sejour_labo sejour=$object link="#1"}}
            </div>
          {{/if}}
          <div style="clear: both;">
            {{mb_include module=patients template=inc_form_docitems_button object=$object compact=true}}
          </div>
        {{/if}}
      </td>
    {{/if}}
  </tr>
  {{if isset($_event|smarty:nodefaults)}}
    {{foreach from=$_event.related key=_related_date item=_related_events}}
      {{foreach from=$_related_events item=_related_event}}
        {{assign var=_related_object value=$_related_event.event}}

        {{assign var=_related_event_icon value=$_related_event.icon}}
        {{if !$_related_event_icon}}
          {{assign var=_related_event_icon value=$_related_object->getEventIcon()}}
        {{/if}}

        {{if $_related_object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
          <tr class="{{if $_related_object->_guid == $selected_guid}}selected{{/if}} {{if $_related_object->annule}}me-cancelled{{/if}}">
            {{if $compact}}
              <td class="narrow"></td>
            {{/if}}

            <td colspan="{{$td_colspan}}">
              {{if $compact}}
              {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

              <a style="display: inline;" href="#{{$_related_object->_guid}}"
                 onclick="viewCompleteItem('{{$_related_object->_guid}}'); ViewFullPatient.select(this);">
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_related_object->_guid}}');">
                    {{tr var1=$_related_object->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
                  </span>
              </a>
              {{else}}
              {{if $_related_object->_canEdit && $show_actions}}
              <a style="display: inline;" class="actionPat me-planif-icon me-button me-tertiary notext me-tertiary-low-emphasis" title="Modifier la consultation"
                 href="?m=cabinet&tab=edit_planning&consultation_id={{$_related_object->_id}}">
                <img src="images/icons/planning.png" alt="modifier" />
              </a>

              {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

              <a style="display: inline;"
                 href="?m=cabinet&tab=edit_consultation&selConsult={{$_related_object->_id}}&chirSel={{$_related_object->_ref_plageconsult->chir_id}}">
                {{elseif $_related_object->_canEdit}}
                {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

                <a style="display: inline;"
                   href="?m=cabinet&tab=edit_consultation&selConsult={{$_related_object->_id}}&chirSel={{$_related_object->_ref_plageconsult->chir_id}}">
                  {{else}}
                    {{if $show_actions}}
                      <span style="display: none" class="me-inline me-margin-left-16 me-margin-right-16"></span>
                    {{/if}}
                  {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

                  <a style="display: inline;" href="#nothing">
                    {{/if}}

                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_related_object->_guid}}');">
                      {{if $compact}}
                        {{tr var1=$_related_object->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
                      {{else}}
                        {{tr}}CConsultation-consult-on{{/tr}} {{$_related_object->_datetime|date_format:$conf.datetime}}

                        {{if $_related_object->_nb_files_docs}}
                          - ({{mb_include module=patients template=inc_detail_docitems_event object=$_related_object}})
                        {{/if}}
                      {{/if}}
                    </span>
                  </a>
                  {{/if}}
            </td>

            <td colspan="{{$td_colspan}}" style="text-align: left;" {{if $_related_object->annule}}class="cancelled"{{/if}}>
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_related_object->_ref_chir}}
              <div class="me-cancelled-text" style="display: none">{{tr}}CConsultation-annule-court{{/tr}}</div>
            </td>

            {{if $compact}}
              <td class="narrow"></td>
            {{/if}}

            {{if $show_files_btn}}
              <td style="text-align: right;">
                {{if $_related_object->_canRead}}
                    {{if $_related_object->_type === "anesth"}}
                        {{foreach from=$_related_object->_refs_dossiers_anesth item=_dossier_anesth name=foreach_anesth}}
                            {{mb_include module=patients template=inc_form_docitems_button object=$_dossier_anesth compact=true}}
                        {{/foreach}}
                    {{else}}
                        {{mb_include module=patients template=inc_form_docitems_button object=$_related_object compact=true}}
                    {{/if}}
                {{/if}}
              </td>
            {{/if}}
          </tr>
        {{/if}}

        {{if $_related_object|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
          <tr class="{{if $_related_object->_guid == $selected_guid}}selected{{/if}} {{if $_related_object->annulee}}me-cancelled{{/if}}">
            {{if $compact}}
              <td class="narrow">
                {{if $show_merge_chkbx && $can->admin}}
                  <input type="checkbox" name="operation_ids[]" class="merge" value="{{$_related_object->_id}}"
                         onclick="checkOnlyTwoSelected(this);" />
                {{/if}}
              </td>
            {{/if}}

            <td colspan="{{$td_colspan}}">
              {{if $compact}}
              {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

              <a style="display: inline;" href="#{{$_related_object->_guid}}"
                 onclick="viewCompleteItem('{{$_related_object->_guid}}'); ViewFullPatient.select(this);">
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_related_object->_guid}}');">
                    {{tr var1=$_related_object->_datetime|date_format:$conf.date}}COperation-Intervention on %s-court{{/tr}}
                  </span>
              </a>
              {{else}}
              {{mb_ternary var=tab_editor test=$_related_object->plageop_id value='vw_edit_planning' other='vw_edit_urgence'}}
              {{assign var=link_editor value="?m=planningOp&tab=$tab_editor&operation_id=`$_related_object->_id`"}}

              {{if $_related_object->_canEdit && $show_actions}}
              <a style="display: inline;" class="actionPat me-planif-icon me-button me-tertiary notext me-tertiary-low-emphasis" title="Modifier l'intervention"
                 href="{{$link_editor}}">
                <img src="images/icons/planning.png" alt="modifier" />
              </a>

              {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

              <a style="display: inline;" href="{{$link_editor}}">
                {{elseif $_related_object->_canEdit}}
                {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

                <a style="display: inline;" href="{{$link_editor}}">
                  {{else}}
                    {{if $show_actions}}
                      <span style="display: none" class="me-inline me-margin-left-16 me-margin-right-16"></span>
                    {{/if}}
                  {{mb_include module=dPpatients template=inc_vw_event_icon indent=1 event_icon=$_related_event_icon}}

                  <a style="display: inline;" href="#nothing">
                    {{/if}}

                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_related_object->_guid}}')">
                      {{if $compact}}
                        {{tr var1=$_related_object->_datetime|date_format:$conf.date}}COperation-Intervention on %s-court{{/tr}}
                      {{else}}
                        {{tr}}dPplanningOp-COperation of{{/tr}} {{$_related_object->_datetime|date_format:$conf.date}}

                        {{if $_related_object->_nb_files_docs}}
                          - ({{mb_include module=patients template=inc_detail_docitems_event object=$_related_object}})
                        {{/if}}
                      {{/if}}
                    </span>
                  </a>
                  {{/if}}
            </td>

            <td colspan="{{$td_colspan}}" style="text-align: left;" {{if $_related_object->annulee}}class="cancelled"{{/if}}>
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_related_object->_ref_chir}}
              <div class="me-cancelled-text" style="display: none">{{tr}}COperation-annulee-court{{/tr}}</div>
            </td>

            {{if $compact}}
              <td class="narrow"></td>
            {{/if}}

            {{if $show_files_btn}}
              <td style="text-align: right;">
                {{if $_related_object->_canRead}}
                  {{mb_include module=patients template=inc_form_docitems_button object=$_related_object compact=true}}
                {{/if}}
              </td>
            {{/if}}
          </tr>
          {{if $_related_object->_ref_consult_anesth && $_related_object->_ref_consult_anesth->_id}}
            {{assign var=consult_anesth value=$_related_object->_ref_consult_anesth}}
            {{assign var=consult value=$consult_anesth->_ref_consultation}}
            {{assign var=consult_anesth_icon value=$consult_anesth->getEventIcon()}}
            <tr>
              {{if $compact}}
                <td class="narrow"></td>
              {{/if}}

              <td colspan="{{$td_colspan}}">
                {{if $consult->_canRead && $show_actions}}
                  <a style="display: inline;" class="actionPat me-planif-icon me-button me-tertiary notext me-tertiary-low-emphasis" title="Modifier la consultation"
                     href="?m=cabinet&tab=edit_planning&consultation_id={{$consult->_id}}">
                    <img src="images/icons/planning.png" alt="modifier" />
                  </a>
                {{elseif $show_actions}}
                  <span style="display: none" class="me-inline me-margin-left-16 me-margin-right-16"></span>
                {{/if}}

                {{if $compact}}
                {{mb_include module=dPpatients template=inc_vw_event_icon indent=2 event_icon=$consult_anesth_icon}}

                <a style="display: inline;" href="#{{$consult_anesth->_guid}}"
                   onclick="viewCompleteItem('{{$consult_anesth->_guid}}'); ViewFullPatient.select(this);">
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$consult_anesth->_guid}}');">
                      {{tr var1=$consult->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
                    </span>
                </a>
                {{else}}
                {{if $consult->_canEdit}}
                {{mb_include module=dPpatients template=inc_vw_event_icon indent=2 event_icon=$consult_anesth_icon}}

                <a style="display: inline;"
                   href="?m=cabinet&tab=edit_consultation&selConsult={{$consult->_id}}&chirSel={{$consult->_ref_plageconsult->chir_id}}&dossier_anesth_id={{$consult_anesth->_id}}">
                  {{else}}
                  {{mb_include module=dPpatients template=inc_vw_event_icon indent=2 event_icon=$consult_anesth_icon}}

                  <a style="display: inline;" href="#nothing">
                    {{/if}}

                    <span onmouseover="ObjectTooltip.createEx(this, '{{$consult_anesth->_guid}}');">
                      {{if $compact}}
                        {{tr var1=$consult->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
                      {{else}}
                        {{tr}}CConsultation-consult-on{{/tr}} {{$consult->_datetime|date_format:$conf.datetime}} - {{$consult->_etat}}
                      {{/if}}
                    </span>
                  </a>
                  {{/if}}
              </td>

              <td colspan="{{$td_colspan}}" style="text-align: left;" {{if $consult->annule}}class="cancelled"{{/if}}>
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult_anesth->_ref_consultation->_ref_chir}}
                <div class="me-cancelled-text" style="display: none">{{tr}}COperation-annulee-court{{/tr}}</div>
              </td>

              {{if $compact}}
                <td class="narrow"></td>
              {{/if}}

              {{if $show_files_btn}}
                <td style="text-align: right;">
                  {{if $consult->_canRead}}
                    {{mb_include module=patients template=inc_form_docitems_button object=$consult_anesth compact=true}}
                  {{/if}}
                </td>
              {{/if}}
            </tr>
          {{/if}}
        {{/if}}
      {{/foreach}}
    {{/foreach}}
  {{/if}}
{{elseif "dPpatients sharing multi_group"|gconf == "limited"}}
  <tr>
    {{if $compact}}
      <td class="narrow"></td>
    {{/if}}

    <td colspan="{{$td_colspan}}">
      {{if $show_actions}}
        <span style="display: none" class="me-inline me-margin-left-16 me-margin-right-16"></span>
      {{/if}}
      {{mb_include module=dPpatients template=inc_vw_event_icon event_icon=$_event_icon}}
      {{$object->_shortview}}
    </td>

    <td colspan="{{$op_colspan}}"
        {{if $object->annule}}class="cancelled"{{else}}style="background-color:#afa;" class="me-group-cell"{{/if}}>
      {{$object->_ref_group->text|upper}}
      <div class="me-cancelled-text" style="display: none">{{tr}}CSejour-annule-court{{/tr}}</div>
    </td>
  </tr>
{{/if}}
