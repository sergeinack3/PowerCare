{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=selected_guid value=''}}
{{mb_default var=show_semaine_grossesse value=0}}
{{mb_default var=_event_icon    value=""}}
{{mb_default var=show_files_btn value=0}}
{{mb_default var=td_colspan     value=1}}
{{mb_default var=compact        value=0}}
{{mb_default var=show_actions   value=true}}

{{if isset($_event|smarty:nodefaults)}}
  {{assign var=_event_icon value=$_event.icon}}
{{/if}}

{{if !$_event_icon}}
  {{assign var=_event_icon value=$object->getEventIcon()}}
{{/if}}

<script type="text/javascript">
  Main.add(function () {
    window.CConsultation_event = true;
  });
</script>

{{if $object->_ref_chir->_ref_function->group_id == $g || $object->_canEdit || "dPpatients sharing multi_group"|gconf == "full"}}
  <tr class="{{if $object->_guid == $selected_guid}} selected{{/if}} {{if $object->annule}}me-cancelled{{/if}}">
    {{if $compact}}
      <td class="narrow"></td>
    {{/if}}

    <td colspan="{{$td_colspan}}">
      {{assign var=consult_anesth value=$object->loadRefConsultAnesth()}}
      {{assign var=verification_access value="dPcabinet CConsultation verification_access"|gconf}}

      {{assign var=droit value=0}}
      {{if $verification_access}}
        {{if $object->sejour_id || ($consult_anesth->_id && $consult_anesth->sejour_id) || $object->_canEdit}}
          {{assign var=droit value=1}}
        {{/if}}
      {{elseif $object->_canEdit}}
        {{assign var=droit value=1}}
      {{/if}}

      {{if $droit && $show_actions}}
        <a style="display: inline;" class="actionPat me-planif-icon me-button me-tertiary notext me-tertiary-low-emphasis"
           title="{{tr}}CConsultation-action-Edit consultation-desc{{/tr}}"
           href="?m=cabinet&tab=edit_planning&consultation_id={{$object->_id}}">
          <img src="images/icons/planning.png" alt="{{tr}}Modify{{/tr}}" />
        </a>
      {{elseif $show_actions}}
        <span style="display: none" class="me-inline me-margin-left-16 me-margin-right-16"></span>
      {{/if}}

      {{if $compact}}
      {{mb_include module=dPpatients template=inc_vw_event_icon event_icon=$_event_icon}}

      <a style="display: inline;" href="#{{$object->_guid}}"
         onclick="viewCompleteItem('{{$object->_guid}}'); ViewFullPatient.select(this);">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
            {{tr var1=$object->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
          </span>
      </a>
      {{else}}
      {{if $object->_canEdit}}
      {{mb_include module=dPpatients template=inc_vw_event_icon event_icon=$_event_icon}}

      <a style="display: inline;"
         href="?m=cabinet&tab=edit_consultation&selConsult={{$object->_id}}&chirSel={{$object->_ref_plageconsult->chir_id}}">
        {{else}}
        {{mb_include module=patients template=inc_vw_event_icon event_icon=$_event_icon}}

        <a style="display: inline;" href="#nothing">
          {{/if}}

          <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
            {{if $compact}}
              {{tr var1=$object->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
            {{else}}
              {{tr}}CConsultation-consult-on{{/tr}} {{$object->_datetime|date_format:$conf.datetime}} - {{$object->_etat}}

              {{if 'maternite'|module_active && $show_semaine_grossesse}}
                {{if $object->_semaine_grossesse > 1}}
                  ({{$object->_semaine_grossesse}}e semaine)
                {{else}}
                  ({{$object->_semaine_grossesse}}ère semaine)
                {{/if}}
              {{/if}}
            {{/if}}
          </span>
        </a>
        {{/if}}
    </td>

    <td colspan="{{$td_colspan}}" style="text-align: left;" {{if $object->annule}}class="cancelled"{{/if}}>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_praticien}}
      <div class="me-cancelled-text" style="display: none">{{tr}}CConsultation-annule-court{{/tr}}</div>
    </td>

    {{if $compact}}
      <td class="narrow"></td>
    {{/if}}

    {{if $show_files_btn}}
      <td style="text-align:right;">
        {{if $object->_canRead}}
          {{if $object->_type === "anesth"}}
            {{foreach from=$object->_refs_dossiers_anesth item=_dossier_anesth name=foreach_anesth}}
              {{mb_include module=patients template=inc_form_docitems_button object=$_dossier_anesth compact=true}}
            {{/foreach}}
          {{else}}
            {{mb_include module=patients template=inc_form_docitems_button object=$object compact=true}}
          {{/if}}
        {{/if}}
      </td>
    {{/if}}
  </tr>
{{elseif "dPpatients sharing multi_group"|gconf == "limited"}}
  <tr>
    {{if $compact}}
      <td class="narrow"></td>
    {{/if}}

    <td colspan="{{$td_colspan}}">
      <span style="display: none" class="me-inline me-margin-left-16 me-margin-right-16"></span>
      {{mb_include module=dPpatients template=inc_vw_event_icon event_icon=$_event_icon}}

      <span>
        {{if $compact}}
          {{tr var1=$object->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
        {{else}}
          {{tr}}CConsultation-consult-on{{/tr}} {{$object->_datetime|date_format:$conf.datetime}}
        {{/if}}
      </span>
    </td>

    <td colspan="{{$td_colspan}}" {{if $object->annule}}class="cancelled" {{else}}style="background-color:#afa;" class="me-group-cell"{{/if}}>
      {{$object->_ref_chir->_ref_function->_ref_group->text|upper}}
      <div class="me-cancelled-text" style="display: none">{{tr}}CConsultation-annule-court{{/tr}}</div>
    </td>
  </tr>
{{/if}}
