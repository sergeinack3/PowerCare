{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=compact                  value=false}}
{{mb_default var=show_actions             value=true}}
{{mb_default var=show_merge_chkbx         value=false}}
{{mb_default var=show_files_btn           value=false}}
{{mb_default var=th_colspan               value=2}}
{{mb_default var=td_colspan               value=1}}
{{mb_default var=op_colspan               value=1}}

{{* Left pane *}}
{{mb_default var=can_view_dossier_medical value=false}}
{{mb_default var=isImedsInstalled         value=false}}

{{if $show_files_btn}}
  {{math assign=op_colspan equation='x + 3' x=$td_colspan}}
  {{math assign=g_colspan equation='x + 1' x=$op_colspan}}
{{/if}}

{{mb_ternary var=year_section test=$compact value='category' other='section'}}

{{if $events_by_date || $nb_sejours_annules || $nb_ops_annulees || $nb_consults_annulees}}
  <tr>
    <th colspan="{{$th_colspan}}" class="category" id="inc_vw_patient_th_consult">
      {{if $compact}}
        {{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$patient->_id object=$patient display_center=0 float_right=1}}

        {{if $can->admin}}
          <button type="button" class="merge notext compact me-tertiary" title="{{tr}}Merge{{/tr}}" style="float: left;"
                  onclick="doMerge(this.form);">
            {{tr}}Merge{{/tr}}
          </button>
        {{/if}}

        {{if !$vw_cancelled}}
          {{if $nb_ops_annulees || $nb_sejours_annules || $nb_consults_annulees}}
            {{math assign=cancelled_events equation='x + y + z' x=$nb_ops_annulees y=$nb_sejours_annules z=$nb_consults_annulees}}
            <a class="button search me-tertiary me-dark" href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}&vw_cancelled=1"
               title="{{tr}}dPpatients-action-See cancelled events{{/tr}}">
              {{tr var1=$cancelled_events}}CPatient-action-Show %s canceled{{/tr}}
            </a>
          {{/if}}
        {{/if}}
      {{else}}
        {{tr}}CPatient-Event|pl{{/tr}}

        {{if $patient->_count_all_sejours > 100}}
          <br />
          <div class="small-warning">
            {{tr}}CPatient-msg-This file contains more than 100 stays you can consult all the stays in the complete folder{{/tr}}
            <a class="button search notext me-small me-secondary" href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}"
               title="{{tr}}CPatient-action-Show the complete folder{{/tr}}" style="margin: -1px;">
              {{tr}}Show{{/tr}}
            </a>
          </div>
        {{/if}}

        {{if $nb_sejours_annules || $nb_ops_annulees || $nb_consults_annulees}}
          <br />
          <br />
          <em>
            <span style="padding-left: 80px;">
            {{tr}}CPatient-Of which canceled{{/tr}} :
              {{if $nb_sejours_annules}}
                {{$nb_sejours_annules}} {{tr}}CSejour-stay(|pl){{/tr}}{{if $nb_ops_annulees || $nb_consults_annulees}},{{/if}}
              {{/if}}

              {{if $nb_ops_annulees}}
                {{$nb_ops_annulees}} {{tr}}COperation-intervention(|pl){{/tr}}{{if $nb_consults_annulees}},{{/if}}
              {{/if}}

              {{if $nb_consults_annulees}}
                {{$nb_consults_annulees}} {{tr}}CConsultation|(pl)-lower{{/tr}}
              {{/if}}
            </span>
          </em>
        {{/if}}

        {{if !$vw_cancelled}}
          {{if $nb_ops_annulees || $nb_sejours_annules || $nb_consults_annulees}}
            {{math assign=cancelled_events equation='x + y + z' x=$nb_ops_annulees y=$nb_sejours_annules z=$nb_consults_annulees}}
            <a class="button search me-tertiary me-dark" style="float: right" onclick="reloadPatient('{{$patient->_id}}', null, 1)"
               title="{{tr}}dPpatients-action-See cancelled events{{/tr}}">
              {{tr}}CPatient-action-Show canceled{{/tr}}
            </a>
          {{/if}}
        {{/if}}
      {{/if}}
    </th>
  </tr>
  {{foreach from=$events_by_date key=_year item=_dates}}
    <tr>
      <th colspan="{{$th_colspan}}" class="{{$year_section}}">{{$_year}}</th>
    </tr>
    {{foreach from=$_dates key=_date item=_events}}
      {{foreach from=$_events item=_event}}
        {{mb_include module=dPpatients template=`$_event.event->_class`_event object=$_event.event}}
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
{{/if}}
