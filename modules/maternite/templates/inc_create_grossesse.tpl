{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=creation_mode value=1}}
{{mb_default var=standalone    value=0}}

<form name="editFormGrossesse"
      method="post"
      onsubmit="return onSubmitFormAjax(this {{if $grossesse->_id}}, Control.Modal.close{{/if}})">
    {{mb_class object=$grossesse}}
    {{mb_key   object=$grossesse}}
    <input type="hidden" name="m" value="maternite"/>
    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="callback"
           value="{{if "maternite CGrossesse audipog"|gconf && !$creation_mode}}
              DossierMater.refreshDossierPerinat.curry('{{$grossesse->_id}}')
           {{elseif !$standalone}}
              Grossesse.afterEditGrossesse
           {{else}}
              DossierMater.reloadHistorique.curry('{{$grossesse->_id}}'){{/if}}"
    />
    <input type="hidden" name="_patient_sexe" value="f"/>
    {{if "maternite CGrossesse audipog"|gconf}}
        {{mb_field object=$grossesse field=active hidden=1}}
    {{/if}}

    <table class="form me-margin-0">
        <tr>
            {{mb_include module=system template=inc_form_table_header object=$grossesse colspan="3"}}
        </tr>
        <tr>
            <td>
                <div class="me-poc-container me-padding-left-0 me-padding-right-0" style="justify-content: space-between">
                    <div class="me-list-categories me-margin-0">
                        <div class="me-categorie-form identite me-margin-0 me-no-border">
                            <div class="categorie-form_titre text">
                                {{tr}}CDossierPerinat-form-Pregnancy declaration{{/tr}}
                            </div>
                            <div class="categorie-form_photo">
                                <i class="mdi mdi-baby-bottle" style="font-size: 60px; color: #29B6F6"></i>
                            </div>
                            <div class="categorie-form_fields">
                                <div class="categorie-form_fields-group">
                                    {{me_form_field mb_object=$grossesse mb_field="parturiente_id" field_class="me-no-max-width me-form-icon search"}}
                                        {{mb_field object=$grossesse field="parturiente_id" hidden=1}}
                                        <input type="text"
                                               style="cursor: pointer"
                                               name="_patient_view"
                                               value="{{$grossesse->_ref_parturiente}}"
                                               readonly="readonly" {{if !$grossesse->_id}}
                                               onclick="PatSelector.init();"{{/if}} class="me-w75"/>
                                    {{/me_form_field}}

                                    <div class="small-info text">
                                        {{tr}}CGrossesse-msg-The calculation of the expected time to know the rank of the pregnancy and the menstrual cycle of the patient{{/tr}}
                                    </div>

                                    <div class="me-display-flex me-justify-content-space-between">
                                        {{me_form_field mb_object=$grossesse mb_field="rang" field_class="me-no-max-width width50"}}
                                            {{mb_field object=$grossesse field="rang" value="1" onchange="DossierMater.updateTermePrevu();"}}
                                        {{/me_form_field}}

                                        {{me_form_field mb_object=$grossesse mb_field="cycle" field_class="me-no-max-width width50"}}
                                            {{mb_field object=$grossesse field="cycle" form="editFormGrossesse" value="28" step="1" increment=true size="10" onchange="DossierMater.updateTermePrevu();"}}
                                            <span class="me-margin-left-5">{{tr}}days{{/tr}}</span>
                                        {{/me_form_field}}
                                    </div>

                                    {{if !"maternite CGrossesse audipog"|gconf}}
                                        <div class="me-display-flex me-justify-content-space-between">
                                            {{me_form_field mb_object=$grossesse mb_field="_semaine_grossesse" field_class="me-no-max-width me-margin-top-0 me-margin-bottom-0 width50"}}
                                                {{mb_field object=$grossesse field="_semaine_grossesse" readonly=1 disabled=1}}
                                            {{/me_form_field}}

                                            {{me_form_field mb_object=$grossesse mb_field="num_semaines" field_class="me-no-max-width me-margin-top-0 me-margin-bottom-0 width50"}}
                                                <select name="num_semaines">
                                                    <option value="">{{tr}}None{{/tr}}</option>
                                                        {{foreach from=$grossesse->_specs.num_semaines->_list item=_num_semaines}}
                                                            <option value="{{$_num_semaines}}"
                                                                    {{if $_num_semaines == $grossesse->num_semaines}}selected{{/if}}
                                                                    {{if $_num_semaines == "sup_15"}}
                                                                        style="
                                                              {{if $grossesse->num_semaines == "sup_15"}}
                                                                background: red;
                                                              {{else}}
                                                                display: none;
                                                              {{/if}}
                                                                "
                                                                    {{/if}}>
                                                                {{tr}}CGrossesse.num_semaines.{{$_num_semaines}}{{/tr}}
                                                            </option>
                                                        {{/foreach}}
                                                </select>
                                            {{/me_form_field}}
                                        </div>

                                        {{if $grossesse->datetime_accouchement}}
                                            {{me_form_field mb_object=$grossesse mb_field="_days_relative_acc" field_class="me-no-max-width"}}
                                                {{mb_field object=$grossesse field="_days_relative_acc" readonly=1 disabled=1}}
                                            {{/me_form_field}}
                                        {{/if}}

                                        {{me_form_field mb_object=$grossesse mb_field="active" layout=true field_class="me-no-max-width"}}
                                            {{mb_field object=$grossesse field="active" typeEnum=radio}}
                                        {{/me_form_field}}

                                        {{if $grossesse->multiple}}
                                            <div class="me-display-flex me-justify-content-space-between">
                                                  {{me_form_field mb_object=$grossesse mb_field="multiple" layout=true field_class="me-no-max-width width50"}}
                                                      {{mb_field object=$grossesse field="multiple" typeEnum=radio}}
                                                  {{/me_form_field}}

                                                  {{me_form_field mb_object=$grossesse mb_field="nb_foetus" field_class="me-no-max-width width50"}}
                                                      {{mb_field object=$grossesse field="nb_foetus" value="2" min="2"}}
                                                  {{/me_form_field}}
                                            </div>
                                        {{else}}
                                            {{me_form_field mb_object=$grossesse mb_field="multiple" layout=true field_class="me-no-max-width"}}
                                                {{mb_field object=$grossesse field="multiple" typeEnum=radio}}
                                            {{/me_form_field}}
                                        {{/if}}

                                        {{me_form_field mb_object=$grossesse mb_field="allaitement_maternel" layout=true field_class="me-no-max-width"}}
                                            {{mb_field object=$grossesse field="allaitement_maternel" typeEnum=radio}}
                                        {{/me_form_field}}

                                        {{me_form_field mb_object=$grossesse mb_field="lieu_accouchement" field_class="me-no-max-width"}}
                                            {{mb_field object=$grossesse field="lieu_accouchement"}}
                                        {{/me_form_field}}
                                    {{/if}}

                                    {{me_form_field mb_object=$grossesse mb_field="rques" field_class="me-no-max-width"}}
                                        {{mb_field object=$grossesse field="rques"}}
                                    {{/me_form_field}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="me-list-categories me-margin-0">
                        <div class="me-categorie-form identite me-margin-0 me-no-border">
                            <div class="categorie-form_titre text">
                                {{tr}}CDossierPerinat-form-Provisional dates{{/tr}}
                            </div>
                            <div class="categorie-form_photo">
                              <i class="mdi mdi-calendar-clock" style="font-size: 60px; color: #29B6F6"></i>
                            </div>
                            <div class="categorie-form_fields">
                                <div class="categorie-form_fields-group">
                                    {{me_form_field mb_object=$grossesse mb_field="date_dernieres_regles" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="date_dernieres_regles" form="editFormGrossesse" register=true style="max-width: none; width: 185px;" onchange="DossierMater.updateTermePrevu();"}}
                                        <span class="me-margin-left-5">
                                            {{tr}}CGrossesse-terme_prevu{{/tr}}:
                                            <span id="terme_prevu_ddr">
                                                {{mb_value object=$grossesse field=_terme_prevu_ddr}}
                                            </span>
                                        </span>
                                        <button type="button" class="carriage_return notext me-tertiary"
                                                title="{{tr}}CDossierPerinat-action-Use pregnancy term{{/tr}}"
                                                onclick="DossierMater.useTermePrevu('DDR');"
                                                tabIndex="1000">
                                        </button>
                                    {{/me_form_field}}

                                    {{me_form_field mb_object=$grossesse mb_field="date_debut_grossesse" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="date_debut_grossesse" form="editFormGrossesse" register=true style="max-width: none; width: 185px;" onchange="DossierMater.updateTermePrevu(); DossierMater.updateProvisionalDates()"}}
                                        <span class="me-margin-left-5">
                                            {{tr}}CGrossesse-terme_prevu{{/tr}}:
                                            <span id="terme_prevu_debut_grossesse">
                                                {{mb_value object=$grossesse field=_terme_prevu_debut_grossesse}}
                                            </span>
                                        </span>
                                        <button type="button" class="carriage_return notext me-tertiary"
                                                title="{{tr}}CDossierPerinat-action-Use pregnancy term{{/tr}}"
                                                onclick="DossierMater.useTermePrevu('DG');"
                                                tabIndex="1000">
                                        </button>
                                    {{/me_form_field}}
                                </div>
                                <div id="editFormGrossesse-provisional-dates" class="categorie-form_fields-group" style="{{if !$grossesse->date_debut_grossesse}}display: none;{{/if}}width: 219px;">
                                    {{me_form_field mb_object=$grossesse mb_field="estimate_first_ultrasound_date" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="estimate_first_ultrasound_date" form="editFormGrossesse" register=true readonly=true style="max-width: none;"}}
                                    {{/me_form_field}}

                                    {{me_form_field mb_object=$grossesse mb_field="estimate_second_ultrasound_date" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="estimate_second_ultrasound_date" form="editFormGrossesse" register=true readonly=true style="max-width: none;"}}
                                    {{/me_form_field}}

                                    {{me_form_field mb_object=$grossesse mb_field="estimate_third_ultrasound_date" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="estimate_third_ultrasound_date" form="editFormGrossesse" register=true readonly=true style="max-width: none;"}}
                                    {{/me_form_field}}

                                    {{me_form_field mb_object=$grossesse mb_field="estimate_sick_leave_date" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="estimate_sick_leave_date" form="editFormGrossesse" register=true readonly=true style="max-width: none;"}}
                                    {{/me_form_field}}
                                </div>
                                <div class="categorie-form_fields-group">
                                    {{me_form_field mb_object=$grossesse mb_field="terme_prevu" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="terme_prevu" form="editFormGrossesse" register=true style="max-width: none; width: 185px;" onchange="DossierMater.updateSemaines();"}}
                                    {{/me_form_field}}

                                    {{me_form_field mb_object=$grossesse mb_field="datetime_cloture" field_class="me-no-max-width" animated=false}}
                                        {{mb_field object=$grossesse field="datetime_cloture" form="editFormGrossesse" register=true style="max-width: none; width: 185px;" onchange="DossierMater.updateActive(this.value);"}}
                                    {{/me_form_field}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="button">
                {{if $grossesse->_id}}
                    <button type="button" class="save" onclick="this.form.onsubmit()">
                        {{tr}}Save{{/tr}}
                    </button>
                    <button type="button" class="cancel"
                            onclick="confirmDeletion(this.form, {objName: '{{$grossesse}}', ajax: 1})">
                        {{tr}}Delete{{/tr}}
                    </button>
                {{else}}
                    <button id="button_create_grossesse" type="button" class="save"
                          onclick="this.form.onsubmit()">
                        {{tr}}Create{{/tr}}
                    </button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>
