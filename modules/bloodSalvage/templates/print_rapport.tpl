{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=operation value=$blood_salvage->_ref_operation}}
{{assign var=patient value=$blood_salvage->_ref_operation->_ref_patient}}
{{assign var=consult_anesth value=$blood_salvage->_ref_operation->_ref_consult_anesth}}
{{assign var=const_med value=$patient->_ref_constantes_medicales}}
{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
{{assign var=ant value=$dossier_medical->_ref_antecedents_by_type}}
{{if !$ant}}
  {{assign var=no_alle value=0}}
{{else}}
  {{assign var=no_alle value=$ant&&!array_key_exists("alle",$ant)}}
{{/if}}

<table class="print">
  <tr>
    <th class="title" colspan="4">
      <a href="#" onclick="window.print()">
        {{tr}}CBloodSalvage.report-long{{/tr}}
      </a>
    </th>
  </tr>
  <tr>
    <td class="halfPane" {{if $no_alle}}colspan="2"{{/if}}>
      <table style="width:100%;">
        <tr>
          <th class="category" colspan="2">{{tr}}CPatient-Patient information{{/tr}}</th>
        </tr>
        <tr>
          <td colspan="2">{{$patient->_view}}</td>
        </tr>
        {{if $patient->nom_jeune_fille}}
          <tr>
            <th>{{mb_label object=$patient field=nom_jeune_fille}}</th>
            <td>{{$patient->nom_jeune_fille}}</td>
          </tr>
        {{/if}}
        <tr>
          <td colspan="2">
            Né{{if $patient->sexe != "m"}}e{{/if}} le {{mb_value object=$patient field=naissance}}
            ({{$patient->_age}})
            - sexe {{mb_value object=$patient field=sexe}}<br />
            {{if $patient->profession}}{{tr}}CPatient-profession{{/tr}} : {{$patient->profession}}<br />{{/if}}
            {{if $patient->medecin_traitant}}{{tr}}medecinTraitant{{/tr}} : {{$patient->_ref_medecin_traitant->_view}}<br />{{/if}}
            <br />
            {{if $const_med->poids}}<strong>{{$const_med->poids}} kg</strong> - {{/if}}
            {{if $const_med->taille}}<strong>{{$const_med->taille}} cm</strong> - {{/if}}
            {{if $const_med->_imc}}{{tr}}CConstantesMedicales-_imc{{/tr}} :
              <strong>{{$const_med->_imc}}</strong>
              {{if $const_med->_imc_valeur}}({{$const_med->_imc_valeur}}){{/if}}
            {{/if}}
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <table>
              {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
              {{if $dossier_medical->groupe_sanguin != "?" || $dossier_medical->rhesus != "?"}}
                <tr>
                  <th>{{tr}}CDossierMedical-groupe_sanguin-desc{{/tr}}</th>
                  <td style="white-space: nowrap;font-size:130%;"><b>&nbsp;{{tr}}{{$dossier_medical->groupe_sanguin}}{{/tr}}
                      &nbsp;{{tr}}{{$dossier_medical->rhesus}}{{/tr}}</b></td>
                </tr>
              {{/if}}
              {{if $consult_anesth->rai && $consult_anesth->rai!="?"}}
                <tr>
                  <th>{{tr}}CConsultAnesth-rai{{/tr}}</th>
                  <td style="white-space: nowrap;font-size:130%;"><b>&nbsp;{{tr}}CConsultAnesth.rai.{{$consult_anesth->rai}}{{/tr}}</b>
                  </td>
                </tr>
              {{/if}}
              <tr>
                <th>{{tr}}CConsultAnesth-ASA{{/tr}}</th>
                <td><b>{{tr}}COperation.ASA.{{$consult_anesth->_ASA}}{{/tr}}</b></td>
              </tr>
              <tr>
                <th>{{tr}}CConsultAnesth-_vst{{/tr}}</th>
                <td style="white-space: nowrap;">
                  <b>
                    {{if $const_med->_vst}}{{$const_med->_vst}} ml{{/if}}
                  </b>
                </td>
              </tr>
              {{if $consult_anesth->_psa}}
                <tr>
                  <th>{{tr}}CConsultAnesth-_psa{{/tr}}</th>
                  <td style="white-space: nowrap;">
                    <b>{{$consult_anesth->_psa}} ml {{tr}}from{{/tr}} GR</b>
                  </td>
                  <td colspan="2"></td>
                </tr>
              {{/if}}
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table style="width:100%;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}CBloodSalvage.operations{{/tr}}
          </th>
        </tr>
        <tr>
          <td>
            {{$operation->_view}} <br />
            {{foreach from=$operation->_codes_ccam item=curr_code}}
              {{$curr_code}};
            {{/foreach}}
            <br />
            {{mb_label object=$operation field=cote}} : {{mb_value object=$operation field=cote}}<br />
            {{if $operation->libelle}}{{mb_label object=$operation field=libelle}} : {{mb_value object=$operation field=libelle}}
              <br />
            {{/if}}
            {{tr}}CBloodSalvage.anesthesia{{/tr}} : {{if $operation->_ref_type_anesth}}{{$operation->_ref_type_anesth->name}}  {{/if}}
            <br />
            {{tr}}CBloodSalvage.anesthesist{{/tr}} : {{$operation->_ref_anesth->_view}}
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table style="width:100%;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}CCellSaver.name{{/tr}}
          </th>
        </tr>
        <tr>
          <td>
            {{tr}}CCellSaver.modele{{/tr}} : {{$blood_salvage->_ref_cell_saver->_view}} <br />
            {{tr}}CBloodSalvage-receive_kit_ref{{/tr}}
            :{{if $blood_salvage->receive_kit_ref}} {{tr}}CBloodSalvage-receive_kit_ref_number{{/tr}} {{$blood_salvage->receive_kit_ref}} {{/if}}
            {{tr}}CBloodSalvage-receive_kit_lot{{/tr}}
            :{{if $blood_salvage->receive_kit_lot}} {{tr}}CBloodSalvage-receive_kit_lot_number{{/tr}} {{$blood_salvage->receive_kit_lot}} {{/if}} <br />
            {{tr}}CBloodSalvage-wash_kit_ref{{/tr}}:{{if $blood_salvage->wash_kit_ref}} {{tr}}CBloodSalvage-receive_kit_ref_number{{/tr}} {{$blood_salvage->wash_kit_ref}} {{/if}}
            {{tr}}CBloodSalvage-wash_kit_lot{{/tr}}:{{if $blood_salvage->wash_kit_lot}} {{tr}}CBloodSalvage-receive_kit_lot_number{{/tr}} {{$blood_salvage->wash_kit_lot}} {{/if}}
            <br />
            {{tr}}CBloodSalvage-anticoagulant_cip{{/tr}} : {{$anticoagulant}} <br /> <br />
            {{tr}}CBloodSalvage-nurse_sspi.report{{/tr}}{{if $tabAffected|@count>1}}s{{/if}} :
            {{foreach from=$tabAffected item=nurse name=affect}}
              {{$nurse->_ref_personnel->_ref_user->_view}} &nbsp;
              {{foreachelse}} - {{/foreach}}
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table style="width:100%;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}CBloodSalvage.timers{{/tr}}
          </th>
        </tr>
        <tr>
          <td>
            {{mb_label object=$blood_salvage field=_recuperation_start}} : {{mb_value object=$blood_salvage field=_recuperation_start}}
            <br />
            {{mb_label object=$blood_salvage field=_recuperation_end}} : {{mb_value object=$blood_salvage field=_recuperation_end}}
            <br />
            {{mb_label object=$blood_salvage field=_transfusion_start}} : {{mb_value object=$blood_salvage field=_transfusion_start}}
            <br />
            {{mb_label object=$blood_salvage field=_transfusion_end}} : {{mb_value object=$blood_salvage field=_transfusion_end}}<br />
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table style="width:100%;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}CBloodSalvage.volumes{{/tr}}
          </th>
        </tr>
        <tr>
          <td>
            {{mb_label object=$blood_salvage field=hgb_pocket}} : {{mb_value object=$blood_salvage field=hgb_pocket}} {{'Ox\Mediboard\BloodSalvage\CBloodSalvage'|const:HGB_POCKET_UNIT}}<br />
            {{mb_label object=$blood_salvage field=hgb_patient}} : {{mb_value object=$blood_salvage field=hgb_patient}} {{'Ox\Mediboard\BloodSalvage\CBloodSalvage'|const:HGB_PATIENT_UNIT}}<br /><br />
            {{mb_label object=$blood_salvage field=transfused_volume}} : {{mb_value object=$blood_salvage field=transfused_volume}}{{'Ox\Mediboard\BloodSalvage\CBloodSalvage'|const:TRANSFUSED_VOLUME_UNIT}}<br />
            {{mb_label object=$blood_salvage field=wash_volume}} : {{mb_value object=$blood_salvage field=wash_volume}} {{'Ox\Mediboard\BloodSalvage\CBloodSalvage'|const:WASH_VOLUME_UNIT}}<br />
            {{mb_label object=$blood_salvage field=saved_volume}} : {{mb_value object=$blood_salvage field=saved_volume}} {{'Ox\Mediboard\BloodSalvage\CBloodSalvage'|const:SAVED_VOLUME_UNIT}}<br />
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table style="width:100%;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}module-dPqualite-court{{/tr}}
          </th>
        </tr>
        <tr>
          <td>
            {{if $blood_salvage->type_ei_id}} {{tr}}CTypeEi-type_ei_id-desc{{/tr}} : {{$blood_salvage->_ref_incident_type->_view}} {{else}} {{tr}}CTypeEi.type_signalement-transfusion.none{{/tr}} {{/if}}
            <br />
            {{tr}}BloodSalvage.quality-protocole{{/tr}} : {{mb_value object=$blood_salvage field="sample"}}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
