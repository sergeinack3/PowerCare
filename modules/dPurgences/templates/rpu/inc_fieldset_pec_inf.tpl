{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modal value=0}}
{{mb_default var=insert_submit_button value=false}}

{{assign var=gerer_circonstance value=$conf.dPurgences.gerer_circonstance}}

<script>
    changeMotifSfmu = function (form) {
        {{if "dPurgences CRPU defer_sfmu_diag_inf"|gconf}}
        $V(form.diag_infirmier, form.motif_sfmu_autocomplete_view.value);
        {{/if}}
    };

    checkIsRemplacant = function (remplacant_id, date) {
        var url = new Url('urgences', 'ajax_check_remplacant');
        url.addParam('remplacant_id', remplacant_id);
        url.addParam('date', date);
        url.requestJSON(function (data) {
            $('resp_remplacant').innerHTML = data;
        });
    };
</script>

{{if $modal}}
    <button type="button" class="search" onclick="Modal.open('pec_inf_div')">{{tr}}CRPU-pec_inf-desc{{/tr}}</button>
{{/if}}

{{if $modal}}
<div id="pec_inf_div" style="display: none;">
    {{else}}
    <fieldset class="me-small">
        <legend>{{tr}}CRPU-pec_inf-desc{{/tr}}</legend>
        {{/if}}

        <table class="form me-no-align me-no-box-shadow me-small-form">
            {{if $modal && $rpu->_ref_sejour->type === "urg"}}
                <tr>
                    <th colspan="4" class="title">
                        <button type="button" class="cancel notext" onclick="Control.Modal.close();"
                                style="float: right;">{{tr}}Close{{/tr}}</button>
                        {{tr}}CRPU-pec_inf-desc{{/tr}}
                    </th>
                </tr>
            {{/if}}
            <tr>
                <th style="width: 10em;">{{mb_label object=$rpu field="_responsable_id"}}</th>
                <td colspan="3">
                    <select name="_responsable_id" style="width: 15em;" class="{{$rpu->_props._responsable_id}}"
                            onchange="checkIsRemplacant(this.value, $V(this.form._entree)); {{$submit_ajax}}">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{mb_include module=mediusers template=inc_options_mediuser selected=$rpu->_responsable_id list=$listResponsables}}
                    </select>

                    <span id="resp_remplacant"></span>
                </td>
            </tr>

            {{if $view_mode == "infirmier"}}
                {{if "dPurgences Display check_date_pec_ioa"|gconf && $rpu->_id}}
                    <tr>
                        <th>{{mb_label object=$rpu field=ioa_id}}</th>
                        <td colspan="3">{{mb_include module="urgences" template=inc_vw_rpu_pec_ioa}}</td>
                    </tr>
                {{/if}}
                <tr>
                    <th>{{mb_label object=$rpu field="ide_responsable_id"}}</th>
                    <td colspan="3">
                        {{if "dPurgences Display see_ide_ref"|gconf || "dPurgences CRPU impose_ide_referent"|gconf}}
                            {{mb_field object=$rpu field="ide_responsable_id" hidden=true onchange=$submit_ajax}}
                            <input type="text" name="ide_responsable_id_view" class="autocomplete"
                                   value="{{$rpu->_ref_ide_responsable}}"
                                   placeholder="&mdash; {{tr}}Choose{{/tr}}"/>
                            <script>
                                Main.add(function () {
                                    var form = getForm("editRPU");
                                    new Url("urgences", "ajax_ide_responsable_autocomplete")
                                      .autoComplete(form.ide_responsable_id_view, null, {
                                          minChars:      2,
                                          method:        "get",
                                          select:        "view",
                                          dropdown:      true,
                                          updateElement: function (selected) {
                                              var id = selected.get("id");
                                              $V(form.ide_responsable_id, id);
                                              $V(form.ide_responsable_id_view, selected.get("name"));
                                          }.bind(form)
                                      });
                                });
                            </script>
                        {{/if}}

                        {{if $rpu->_id && "dPurgences Display check_date_pec_inf"|gconf}}
                            {{mb_include module="urgences" template=inc_vw_rpu_pec_inf}}
                        {{/if}}
                    </td>
                </tr>
                {{if !"dPurgences Display check_date_pec_ioa"|gconf}}
                    <tr>
                        <th>{{mb_label object=$rpu field="ioa_id"}}</th>
                        <td colspan="3">
                            {{mb_field object=$rpu field="ioa_id" hidden=true onchange=$submit_ajax}}
                            <input type="text" name="ioa_id_view" class="autocomplete" value="{{$rpu->_ref_ioa}}"
                                   placeholder="&mdash; {{tr}}Choose{{/tr}}"/>
                            <script>
                                Main.add(function () {
                                    var form = getForm("editRPU");
                                    new Url("urgences", "ajax_ide_responsable_autocomplete")
                                      .addParam("field", "ioa_id_view")
                                      .autoComplete(form.ioa_id_view, null, {
                                          minChars:      2,
                                          method:        "get",
                                          select:        "view",
                                          dropdown:      true,
                                          updateElement: function (selected) {
                                              var id = selected.get("id");
                                              $V(form.ioa_id, id);
                                              $V(form.ioa_id_view, selected.get("name"));
                                          }.bind(form)
                                      });
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <th>{{mb_label object=$rpu field="pec_ioa"}}</th>
                        <td colspan="3">
                            {{mb_field object=$rpu field="pec_ioa" form="editRPU" register=true onchange=$submit_ajax}}
                        </td>
                    </tr>
                {{/if}}
            {{/if}}

            {{if "dPurgences Display display_cimu"|gconf && !"dPurgences CRPU french_triage"|gconf}}
                {{assign var=notnull value=""}}
                {{if "dPurgences CRPU cimu_accueil"|gconf}}
                    {{assign var=notnull value="notNull"}}
                {{/if}}
                <tr>
                    <th>{{mb_label object=$rpu field="cimu"}} {{if $rpu->_count_rpu_reevaluations_pec > 0}}({{tr}}common-initial|f{{/tr}}){{/if}}</th>
                    <td colspan="3">
                        {{mb_field object=$rpu field="cimu" form="editRPU" emptyLabel="Choose" class=$notnull onchange=$submit_ajax}}
                    </td>
                </tr>
            {{/if}}

            {{if "dPurgences CRPU french_triage"|gconf}}
                <tr>
                    <th>{{mb_label object=$rpu field=french_triage}}</th>
                    <td colspan="3">
                        {{mb_field object=$rpu field=french_triage form="editRPU" emptyLabel="Choose" onchange=$submit_ajax}}
                    </td>
                </tr>
            {{/if}}

            {{if $can->edit}}
                {{if $gerer_circonstance}}
                    <tr>
                        <th>{{mb_label object=$rpu field="circonstance"}}</th>
                        <td colspan="3">
                            {{mb_field object=$rpu field="circonstance" autocomplete="true,1,10,true,true" form=editRPU size=20 onchange=$submit_ajax}}
                        </td>
                    </tr>
                {{/if}}

                {{if "dPurgences CRPU display_motif_sfmu"|gconf}}
                    {{assign var=notnull value=""}}
                    {{if "dPurgences CRPU motif_sfmu_accueil"|gconf || $rpu->_id && 'dPurgences CRPU gestion_motif_sfmu'|gconf == '2'}}
                        {{assign var=notnull value="notNull"}}
                    {{/if}}
                    <tr>
                        <th>{{mb_label object=$rpu field="motif_sfmu" class=$notnull}}</th>
                        <td
                          colspan="3">{{mb_field object=$rpu field="motif_sfmu" autocomplete="true,1,10,true,true" form=editRPU size=50 class=$notnull onchange="changeMotifSfmu(this.form); $submit_ajax"}}
                            <button type="button" class="search notext"
                                    onclick="CCirconstance.searchMotifSFMU(this.form)">
                                {{tr}}Search{{/tr}}
                            </button>
                        </td>
                    </tr>
                {{/if}}

                {{if $rpu->motif_entree}}
                    <tr>
                        <th>{{mb_label object=$rpu field="motif_entree"}}</th>
                        <td>{{mb_value object=$rpu field="motif_entree" onchange=$submit_ajax}}</td>
                    </tr>
                {{/if}}


                {{assign var=notnull value=""}}
                {{if $rpu->_id && ("dPurgences Display check_ccmu"|gconf == '2') && "dPurgences CRPU impose_degre_urgence"|gconf}}
                    {{assign var=notnull value="notNull"}}
                {{/if}}
                <tr>
                    <th>{{mb_label object=$rpu field="ccmu"}} {{if $rpu->_count_rpu_reevaluations_pec > 0}}({{tr}}common-initial|f{{/tr}}){{/if}}</th>
                    <td colspan="3">
                        {{mb_field object=$rpu field="ccmu" emptyLabel="Choose" style="width: 15em;" onchange=$submit_ajax class=$notnull}}
                    </td>
                </tr>

                <tr>
                    <th>{{mb_label object=$rpu field="diag_infirmier"}}</th>
                    <td>
                        {{mb_field object=$rpu field="diag_infirmier" class="autocomplete" form="editRPU" onchange=$submit_ajax
                        aidesaisie="validate: function() { form.onsubmit() },
                                      validateOnBlur: 0,
                                      resetSearchField: 0,
                                      resetDependFields: 0"}}
                    </td>
                    <th class="narrow">{{mb_label object=$rpu field="pec_douleur"}}</th>
                    <td>
                        {{mb_field object=$rpu field="pec_douleur" class="autocomplete" form="editRPU" onchange=$submit_ajax
                        aidesaisie="validate: function() { form.onsubmit() },
                                      validateOnBlur: 0,
                                      resetSearchField: 0,
                                      resetDependFields: 0"}}
                    </td>
                </tr>
            {{else}}
                <th>{{mb_label object=$rpu field="motif_entree"}}</th>
                <td colspan="3">
                    {{mb_field object=$rpu field="motif_entree" class="autocomplete" form="editRPU" onchange=$submit_ajax
                    aidesaisie="validate: function() { form.onsubmit() },
                                      validateOnBlur: 0,
                                      resetSearchField: 0,
                                      resetDependFields: 0"}}
                </td>
            {{/if}}

            <tr>
                <th>{{mb_label object=$rpu field="date_at"}}</th>
                <td
                  colspan="3">{{mb_field object=$rpu field="date_at" form="editRPU" register=true onchange=$submit_ajax}}</td>
            </tr>

          {{* We need to inject submit button when this template is used as form widget *}}
          {{if $insert_submit_button}}
              <tr>
                <td colspan="4" class="button">
                    <button type="submit" class="save">{{tr}}common-action-Save{{/tr}}</button>
                </td>
              </tr>
          {{/if}}
        </table>

        {{if $rpu->_count_rpu_reevaluations_pec > 0}}
            {{mb_include module=urgences template=rpu/inc_table_reeval_pec}}
        {{/if}}

        {{if $modal}}
</div>
{{else}}
    </fieldset>
{{/if}}
