{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="trClass" value=""}}
{{if $patVitale && $patVitale->_id}}
    {{if $_patient->nom == $patVitale->nom && $_patient->prenom == $patVitale->prenom && $_patient->naissance == $patVitale->naissance}}
        {{assign var="trClass" value="selected"}}
    {{/if}}
{{/if}}

<tbody class="hoverable">
<tr class="{{$trClass}} {{if $_patient->deces}}hatching{{/if}}">
    {{assign var="rowspan" value=1}}
    {{if count($_patient->_ref_consultations) || count($_patient->_ref_sejours)}}
        {{assign var="rowspan" value=2}}
    {{/if}}
    <td style="text-align: center;">
        <input type="checkbox" name="objects_id[]" value="{{$_patient->_id}}" class="merge"
               onclick="checkOnlyTwoSelected(this)"/>
    </td>
    <td>
        <div style="float: right;">
            {{mb_include module=system template=inc_object_notes object=$_patient}}
        </div>

        {{if $patVitale && $_patient->_id == $patVitale->_id}}
            <img style="float: right;" src="images/icons/carte_vitale.png"
                 title="Bénéficiaire associé à la carte Vitale"/>
        {{/if}}

        {{if $_patient->fin_amo && $_patient->fin_amo < $dnow}}
            {{me_img_title src="warning.png" icon="warning" class="me-warning" style="float:right"}}
                Période de droits terminée ({{mb_value object=$_patient field=fin_amo}})
            {{/me_img_title}}
        {{/if}}

        <span style="margin-right: 16px;">{{$_patient}}</span>

        {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_patient}}
    </td>
    <td>{{mb_value object=$_patient field="naissance"}}</td>

    {{if $patVitale && $patVitale->_id}}
        <td>{{mb_value object=$_patient field="matricule"}}</td>
        <td>{{mb_value object=$_patient field="adresse"}}</td>
        <td class="button" rowspan="{{$rowspan}}">
            <button class="tick compact" type="button"
                    onclick="PatientFromSelector.updateFromVitale('{{$_patient->_id}}', '{{$_patient->_view|smarty:nodefaults|JSAttribute}}', '{{$_patient->sexe}}')">
                {{tr}}Select{{/tr}}
            </button>
        </td>
    {{else}}
        <td>
            {{mb_value object=$_patient field=tel}}
            {{if $_patient->tel2}}
                <br/>
                {{mb_value object=$_patient field=tel2}}
            {{/if}}
        </td>
        <td class="text compact">
            <span style="white-space: nowrap;">{{$_patient->adresse|spancate:30}}</span>
            <span style="white-space: nowrap;">{{$_patient->cp}} {{$_patient->ville|spancate:20}}</span>
        </td>
        {{assign var=_patient_id value=$_patient->_id}}
        <td
          title="{{if $sejours_avenir.$_patient_id}}{{tr}}CSejour-msg-avenir{{/tr}}{{/if}} {{if $sejours_encours.$_patient_id}}{{tr}}CSejour-msg-encours{{/tr}}{{/if}}">
            {{if $sejours_avenir.$_patient_id || $sejours_encours.$_patient_id}}
                <i
                  class="far fa-hospital event-icon {{if $sejours_avenir.$_patient_id}}sejour-avenir{{/if}} {{if $sejours_encours.$_patient_id}}sejour-encours{{/if}}"></i>
            {{/if}}
        </td>
        <td class="button" rowspan="{{$rowspan}}" style="white-space: nowrap;">
            {{if $can->edit}}
                <button class="edit compact me-tertiary" type="button"
                        onclick="PatientFromSelector.edit({{$_patient->_id}})">
                    {{tr}}Edit{{/tr}}
                </button>
            {{/if}}
            <button class="tick compact" id="inc_pat_selector_select_pat"
                    type="button"
                    onclick="PatientFromSelector.select({{$_patient->getProperties()|JSAttribute|@json:true|escape:"html"}}, {{$_patient->_ref_medecin_traitant|JSAttribute|@json:true|escape:"html"}});">
                {{tr}}Select{{/tr}}
            </button>
        </td>
    {{/if}}
</tr>

{{if $rowspan == 2}}
    <tr>
        <td colspan="6" class="text">
            <div class="small-info">
                <!-- Consultations du jour -->
                {{foreach from=$_patient->_ref_consultations item=_consult}}
                    <div>
                        Consultation aujourd'hui à {{mb_value object=$_consult field=heure}} avec
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_praticien}}
                    </div>
                {{/foreach}}

                <!-- Admissions du jour -->
                {{foreach from=$_patient->_ref_sejours item=_sejour}}
                    <div>
                        Admission aujourd'hui à {{mb_value object=$_sejour field=entree_prevue format=$conf.time}} pour
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
                    </div>
                {{/foreach}}
            </div>
        </td>
    </tr>
{{/if}}
</tbody>
