{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_atcd value=1}}
{{mb_default var=atcd_absence value=0}}
{{if $show_atcd}}
  {{mb_include module=soins template=inc_vw_antecedents}}
{{/if}}

{{if $dossier_medical->_id}}
  {{if $dossier_medical->_count_allergies}}
    <script>
      ObjectTooltip.modes.allergies = {
        module: "patients",
        action: "ajax_vw_allergies",
        sClass: "tooltip"
      };
    </script>
    <span class="texticon texticon-allergies-warning"
          onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}', 'allergies');">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>
  {{elseif $count_abs_allergie}}
    <span class="texticon texticon-allergies-ok"
          onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}', 'allergies');">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>
  {{elseif $dossier_medical->_ref_allergies|@count}}
    <span class="texticon texticon-allergies-ok"
          title="{{tr}}CAntecedent-No known allergy-desc{{/tr}}">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>
  {{/if}}

    {{mb_include module=soins template=inc_vw_traitements}}
{{/if}}
{{if $patient->_refs_patient_handicaps}}
  <button class="deficience me-small notext"
          title="{{foreach from=$patient->_refs_patient_handicaps item=_handicap}}{{tr}}CPatientHandicap.handicap.{{$_handicap->handicap}}{{/tr}}. {{/foreach}}"></button>
{{/if}}
