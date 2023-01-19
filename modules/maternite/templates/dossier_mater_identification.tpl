{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=sejour  value=$grossesse->_ref_last_sejour}}
{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}

{{mb_script module=patients  script=patient   ajax=1}}
{{mb_script module=maternite script=grossesse ajax=1}}

<table class="main">
  <tr>
    <td>
      <fieldset>
        <legend>{{tr}}CGrossesse-Identification{{/tr}}</legend>
        <table class="layout" style="width: 100%">
          {{if !$grossesse->_id}}
            <tr>
              <td>{{mb_include module=maternite template=inc_create_grossesse}}</td>
            </tr>
          {{else}}
            <tr>
              <td colspan="2">
                <div class="only-printable">
                  {{mb_include module=maternite template=inc_dossier_mater_header show_buttons=0}}
                </div>
                <table class="form not-printable me-no-align me-no-box-shadow">
                  <tr>{{mb_include module=system template=inc_form_table_header object=$grossesse}}</tr>
                  <tr>
                    {{if $grossesse->active}}
                      <th class="title me-padding-top-2 me-padding-bottom-2">
                        {{mb_value object=$grossesse field=_semaine_grossesse}} SA
                        + {{mb_value object=$grossesse field=_reste_semaine_grossesse}} j
                      </th>
                    {{else}}
                      <th class="title me-padding-top-2 me-padding-bottom-2">{{tr}}CGrossesse-Completed{{/tr}}</th>
                    {{/if}}
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td class="halfPane">
                <table class="form me-no-box-shadow me-small-form">
                  <tr>
                    <th class="category me-h6" colspan="3">
                        {{tr}}CGrossesse-Mother{{/tr}}
                    </th>
                  </tr>
                  <tr>
                    <th class="halfPane">
                      <button style="float:left;" class="edit notext not-printable me-tertiary"
                              onclick="Patient.editModal('{{$patient->_id}}', '0', 'window.parent.DossierMater.reloadGrossesse');">
                        {{tr}}Edit{{/tr}}
                      </button>
                      {{mb_label object=$patient field=nom}}
                    </th>
                    <td>{{mb_value object=$patient field=nom}}</td>
                      {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
                        <td rowspan="10">{{mb_include module=dPpatients template=vw_datamatrix_ins}}</td>
                      {{/if}}
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=nom_jeune_fille}}</th>
                    <td>{{mb_value object=$patient field=nom_jeune_fille}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=prenom}}</th>
                    <td>{{mb_value object=$patient field=prenom}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=naissance}}</th>
                    <td>{{mb_value object=$patient field=naissance}} ({{mb_value object=$patient field=_age}})</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=adresse}}</th>
                    <td>{{mb_value object=$patient field=adresse}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=cp}}</th>
                    <td>{{mb_value object=$patient field=cp}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=ville}}</th>
                    <td>{{mb_value object=$patient field=ville}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=tel}}</th>
                    <td>{{mb_value object=$patient field=tel}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=tel2}}</th>
                    <td>{{mb_value object=$patient field=tel2}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$patient field=tel_autre}}</th>
                    <td>{{mb_value object=$patient field=tel_autre}}</td>
                  </tr>
                  {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
                    <tr>
                      <th>{{tr}}CINSPatient{{/tr}}</th>
                      <td>
                          {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
                      </td>
                    </tr>
                  {{/if}}
                  <tr>
                    <th class="category" colspan="3">{{tr}}CCorrespondantPatient|pl{{/tr}}</th>
                  </tr>
                  {{foreach from=$patient->_ref_correspondants_patient item=_correspondant}}
                    <tr>
                      <th>
                        {{tr}}CCorrespondantPatient-relation-court{{/tr}}
                      </th>
                      <td>
                        <strong>{{$_correspondant}}</strong>
                      </td>
                    </tr>
                    <tr>
                      <th>
                        {{tr}}CCorrespondantPatient-nom{{/tr}} {{tr}}CCorrespondantPatient-prenom{{/tr}}
                      </th>
                      <td>
                        {{$_correspondant->nom}} {{$_correspondant->prenom}}
                      </td>
                    </tr>
                    <tr>
                      <th>
                        {{tr}}CCorrespondantPatient-adresse{{/tr}}
                      </th>
                      <td>
                        {{$_correspondant->adresse}}
                      </td>
                    </tr>
                    <tr>
                      <th>
                      </th>
                      <td>
                        {{$_correspondant->cp}} {{$_correspondant->ville}}
                      </td>
                    </tr>
                    <tr>
                      <th>
                        {{tr}}CCorrespondantPatient-tel{{/tr}}
                      </th>
                      <td>
                        {{$_correspondant->tel}}
                      </td>
                    </tr>
                    <tr>
                      <th>
                        {{tr}}CCorrespondantPatient-mob{{/tr}}
                      </th>
                      <td>
                        {{$_correspondant->mob}}
                      </td>
                    </tr>
                    {{foreachelse}}
                    <tr>
                      <td class="empty" colspan="2">
                        {{tr}}CCorrespondant.none{{/tr}}
                      </td>
                    </tr>
                  {{/foreach}}
                </table>
              </td>
              <td>
                <form name="editFormGrossesse" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
                  <input type="hidden" name="m" value="maternite" />
                  {{mb_class object=$grossesse}}
                  {{mb_key   object=$grossesse}}
                  <input type="hidden" name="del" value="0" />
                  <input type="hidden" name="_patient_sexe" value="f" />
                  <table class="form me-small-form me-no-box-shadow">
                    <tr>
                      <th class="category me-h6" colspan="2">{{tr}}CAntecedent-dossier_medical_id-court{{/tr}}</th>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=active}}</th>
                      <td>{{mb_field object=$grossesse field=active}}</td>
                    </tr>
                    <tr>
                      <th class="halfPane">{{mb_label object=$grossesse field=group_id}}</th>
                      <td>{{$grossesse->_ref_group}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$patient field=_IPP}}</th>
                      <td>
                        <span class="idex-special idex-special-IPP">{{mb_value object=$patient field=_IPP}}</span>
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label class=CSejour field=_NDA}}</th>
                      <td>
                        {{if $sejour && $sejour->_id}}
                          <span class="idex-special idex-special-NDA">{{mb_value object=$sejour field=_NDA}}</span>
                        {{else}}
                            {{tr}}CGrossesse-back-sejours.empty{{/tr}}
                        {{/if}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=id_reseau}}</th>
                      <td>{{mb_field object=$grossesse field=id_reseau}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=rang}}</th>
                      <td>{{mb_field object=$grossesse field=rang}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=cycle}}</th>
                      <td>{{mb_field object=$grossesse field=cycle}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=date_dernieres_regles}}</th>
                      <td>
                        {{mb_field object=$grossesse field=date_dernieres_regles form=editFormGrossesse register=true}}
                        <span class="compact">TP
                          <span id="terme_prevu_ddr">
                            {{mb_value object=$grossesse field=_terme_prevu_ddr}}
                          </span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=date_debut_grossesse}}</th>
                      <td>
                        {{mb_field object=$grossesse field=date_debut_grossesse form=editFormGrossesse register=true onchange="DossierMater.updateTermePrevu()"}}
                        <span class="compact">TP
                          <span id="terme_prevu_debut_grossesse">
                            {{mb_value object=$grossesse field=_terme_prevu_debut_grossesse}}
                          </span>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=terme_prevu}}</th>
                      <td>{{mb_field object=$grossesse field=terme_prevu form=editFormGrossesse register=true}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=datetime_accouchement}}</th>
                      <td>{{mb_value object=$grossesse field=datetime_accouchement}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label class=CNaissance field=num_naissance}}</th>
                      <td>
                        <ul>
                          {{foreach from=$grossesse->_ref_naissances item=_naissance}}
                            <li>
                              {{mb_value object=$_naissance field=num_naissance}} - {{$_naissance->date_time|date_format:"%Y"}}
                            </li>
                          {{/foreach}}
                        </ul>
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=multiple}}</th>
                      <td>{{mb_field object=$grossesse field=multiple onchange="Grossesse.showField('show_foetus_field', this.value);"}}</td>
                    </tr>
                    <tr id="show_foetus_field" {{if !$grossesse->multiple}}style="display: none;"{{/if}}>
                      <th>{{mb_label object=$grossesse field=nb_foetus}}</th>
                      <td colspan="2">{{mb_field object=$grossesse field=nb_foetus increment=true form=editFormGrossesse min=2}}</td>
                    </tr>
                    {{if $grossesse->date_debut_grossesse}}
                      <tr>
                        <th class="category" colspan="2">
                            {{tr}}CDossierPerinat-form-Provisional dates{{/tr}}
                        </th>
                      </tr>
                      <tr>
                        <th>{{mb_label object=$grossesse field=estimate_first_ultrasound_date}}</th>
                        <td>{{mb_field object=$grossesse field=estimate_first_ultrasound_date form=editFormGrossesse register=true readonly=true}}</td>
                      </tr>
                      <tr>
                        <th>{{mb_label object=$grossesse field=estimate_second_ultrasound_date}}</th>
                        <td>{{mb_field object=$grossesse field=estimate_second_ultrasound_date form=editFormGrossesse register=true readonly=true}}</td>
                      </tr>
                      <tr>
                        <th>{{mb_label object=$grossesse field=estimate_third_ultrasound_date}}</th>
                        <td>{{mb_field object=$grossesse field=estimate_third_ultrasound_date form=editFormGrossesse register=true readonly=true}}</td>
                      </tr>
                      <tr>
                        <th>{{mb_label object=$grossesse field=estimate_sick_leave_date}}</th>
                        <td>{{mb_field object=$grossesse field=estimate_sick_leave_date form=editFormGrossesse register=true readonly=true}}</td>
                      </tr>
                    {{/if}}
                    <tr>
                      <th class="category" colspan="2">
                          {{tr}}CAntecedent|pl{{/tr}}
                      </th>
                    </tr>
                    <tr>
                      <td colspan="2" class="button">
                        {{mb_include module=patients template=vw_antecedents_allergies}}
                      </td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=nb_grossesses_ant}}</th>
                      <td>{{mb_field object=$grossesse field=nb_grossesses_ant}}</td>
                    </tr>
                    <tr>
                      <th>{{mb_label object=$grossesse field=nb_accouchements_ant}}</th>
                      <td>{{mb_field object=$grossesse field=nb_accouchements_ant}}</td>
                    </tr>

                    <tr>
                      <td colspan="2" class="button">
                        {{if $grossesse->_id}}
                          <button type="button" class="save not-printable" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
                          <button type="button" class="cancel not-printable"
                                  onclick="confirmDeletion(this.form, {objName: '{{$grossesse}}', ajax: 1}, {onComplete: Control.Modal.close})">
                            {{tr}}Delete{{/tr}}
                          </button>
                        {{else}}
                          <button id="button_create_grossesse" type="button" class="save not-printable"
                                  onclick="this.form.onsubmit()">{{tr}}Create{{/tr}}</button>
                        {{/if}}
                      </td>
                    </tr>
                  </table>
                </form>
              </td>
            </tr>
          {{/if}}
        </table>
      </fieldset>
    </td>
  </tr>
  {{if $grossesse->_id}}
    <tr>
      <td>
        <fieldset>
          <legend>{{tr}}CSejour-Medical follow-up{{/tr}}</legend>
          <table class="layout" style="width: 100%">
            <tr>
              <td class="thirdPane">
                <table class="form me-no-box-shadow me-no-align">
                  <tr>
                    <th class="category me-text-align-left me-padding-top-0 me-padding-bottom-0">{{tr}}CPatient-medecin_traitant{{/tr}}</th>
                  </tr>
                  <tr>
                    <td>
                      {{if $patient->medecin_traitant}}
                        {{$patient->_ref_medecin_traitant}}
                      {{else}}
                        <span class="empty">{{tr}}CPatient-No doctor{{/tr}}</span>
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </td>

              {{if $app->user_prefs.UISTYLE != "tamm"}}
                <td class="thirdPane">
                  <table class="form me-no-box-shadow me-no-align">
                    <tr>
                      <th class="category me-text-align-left me-padding-top-0 me-padding-bottom-0">{{tr}}CGrossesse-Referring doctor{{/tr}}</th>
                    </tr>
                    <tr>
                      <td>
                        {{if $sejour && $sejour->_id}}
                          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
                        {{else}}
                          <span class="empty">{{tr}}CGrossesse-Referring doctor.none{{/tr}} ({{tr}}{{tr}}CGrossesse-back-sejours.empty{{/tr}}{{/tr}})</span>
                        {{/if}}
                      </td>
                    </tr>
                  </table>
                </td>
              {{/if}}

              <td class="thirdPane">
                <table class="form me-no-box-shadow me-no-align">
                  <tr>
                    <th class="category me-text-align-left me-padding-top-0 me-padding-bottom-0">{{tr}}CGrossesse-Other correspondents{{/tr}}</th>
                  </tr>
                  <tr>
                    <td>
                      {{if $patient->_ref_medecins_correspondants|@count}}
                        <ul>
                          {{foreach from=$patient->_ref_medecins_correspondants item=correspondant}}
                            <li>{{$correspondant}}</li>
                          {{/foreach}}
                        </ul>
                      {{else}}
                        <span class="empty">{{tr}}CCorrespondant.none{{/tr}}</span>
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
  {{/if}}
</table>
