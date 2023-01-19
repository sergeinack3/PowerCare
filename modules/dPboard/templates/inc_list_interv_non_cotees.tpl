{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $board}}
    <script>
        Control.Tabs.setTabCount('actes_non_cotes', {{$interventions|@count}} + {{$consultations|@count}} + {{$sejours|@count}});
    </script>
{{/if}}

{{mb_default var=chirSel value=false}}

<script>
    editConsultation = function (consult_id, callback) {
        new Url('cabinet', 'ajax_full_consult')
          .addParam("consult_id", consult_id)
          .modal({
                width:   "95%",
                height:  "95%",
                onClose: callback || Prototype.emptyFunction
            }
          );
    };

    showNonCotees = function () {
        {{if !$board}}
        var form = getForm('filterObjects');
        $V(form.begin_date, '{{$date_begin_op_non_cotees}}');
        $V(form.end_date, '{{$date_end_op_non_cotees}}');
        $V(form.objects_whithout_codes, 1);
        $('doFilterCotation').click();
        {{else}}
        new Url('board', 'listInterventionNonCotees')
          .addParam('praticien_id', '{{$chirSel->_id}}')
          .addParam('begin_date', '{{$date_begin_op_non_cotees}}')
          .addParam('end_date', '{{$date_end_op_non_cotees}}')
          .addParam('board', '1')
          .addParam('objects_whithout_codes', '1')
          .requestUpdate('actes_non_cotes');
        {{/if}}
    };
</script>

{{assign var=isAnesth value=$app->_ref_user->isAnesth()}}
{{assign var=isChirurgien value=$app->_ref_user->isChirurgien()}}

<table class="tbl me-no-align me-no-box-shadow">
    <tr>
        {{if $chirSel && $object_classes|@count == 1 && ($display_consultations || ($display_operations && ($ccam_codes || $libelle)) || $display_seances)}}
            <th class="narrow">
                <input type="checkbox" name="select_all_objects" onchange="checkAllObjects(this);"/>
            </th>
        {{/if}}
        {{if $all_prats}}
            <th>{{tr}}common-Practitioner|pl{{/tr}}</th>
        {{/if}}
        <th>{{tr}}common-Patient{{/tr}}</th>
        <th>{{tr}}common-Event{{/tr}}</th>
        <th class="narrow">{{tr}}CFilterCotation-non_coded_acts{{/tr}}</th>
        <th class="narrow">{{tr}}CFilterCotation-planned_acts_codes{{/tr}}</th>
        {{if $display_operations}}
            <th>{{tr}}CFilterCotation-chir-coded-acts{{/tr}}</th>
            <th>{{tr}}CFilterCotation-chir-coding-status{{/tr}}</th>
            {{if $isAnesth || $isChirurgien}}
                <th class="narrow">{{tr}}mod-dPboard-Surcharge of fee|pl-court{{/tr}}</th>
            {{/if}}
            <th>{{tr}}CFilterCotation-anesth-coded-acts{{/tr}}</th>
            <th>{{tr}}CFilterCotation-anesth-coding-status{{/tr}}</th>
        {{else}}
            <th>{{tr}}CFilterCotation-coded-acts{{/tr}}</th>
            <th>{{tr}}CFilterCotation-coding-status{{/tr}}</th>
            {{if $isAnesth}}
                <th class="narrow">{{tr}}mod-dPboard-Surcharge of fee|pl-court{{/tr}}</th>
            {{/if}}
        {{/if}}
        {{if $show_unexported_acts}}
            <th>{{tr}}CFilterCotation-unexported_acts{{/tr}}</th>
        {{/if}}
    </tr>
    {{if $display_operations}}
        {{if !$all_prats && $total_operations_non_cotees}}
            <tr class="clear">
                <td colspan="9">
                    <div class="small-warning" style="cursor: pointer;" onclick="showNonCotees();">
                        Attention, vous avez {{$total_operations_non_cotees}} intervention(s) non cotée(s) durant les 3
                        derniers mois.
                    </div>
                </td>
            </tr>
        {{/if}}
        <tr>
            <th class="section" colspan="10">Interventions ({{$interventions|@count}} / {{$totals.COperation}})</th>
        </tr>
        {{foreach from=$interventions item=_interv}}
            {{mb_ternary var=_anesth_id test=$_interv->anesth_id value=$_interv->anesth_id other=$_interv->_ref_plageop->anesth_id}}
            {{assign var=codes_ccam value=$_interv->codes_ccam}}
            <tr class="alternate">
                {{if ($ccam_codes || $libelle) && $chirSel && $object_classes|@count == 1 && $display_operations}}
                    <td class="narrow">
                        <input type="checkbox" class="select_objects" data-guid="{{$_interv->_guid}}"
                               name="select_{{$_interv->_guid}}" onchange="checkObject();"/>
                    </td>
                {{/if}}
                {{if $all_prats}}
                    <td class="text">
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_interv->_ref_chir}}
                        {{if $_interv->_ref_anesth}}
                            <br/>
                            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_interv->_ref_anesth}}
                        {{/if}}
                    </td>
                {{/if}}
                <td class="text">
                    {{assign var=patient value=$_interv->_ref_patient}}
                    {{assign var=sejour  value=$_interv->_ref_sejour}}
                    <a href="{{$patient->_dossier_cabinet_url}}">
                        <strong class="{{if !$sejour->entree_reelle}}patient-not-arrived{{/if}}"
                                onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                            {{$patient}}
                        </strong>

                        {{mb_include module=patients template=inc_icon_bmr_bhre}}
                    </a>
                </td>
                <td class="text">
                    <a href="#1" onclick="Operation.dossierBloc('{{$_interv->_id}}', updateActes, 'codage_tab'); return
                      false;">
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$_interv->_guid}}')">
                          {{$_interv}}
                        </span>
                    </a>
                    {{if $sejour->libelle}}
                        <div class="compact">
                            {{$sejour->libelle}}
                        </div>
                    {{/if}}
                    {{if $_interv->libelle}}
                        <div class="compact">
                            {{$_interv->libelle}}
                        </div>
                    {{/if}}
                </td>
                <td>
                    {{if !$_interv->_count_actes && !$_interv->_ext_codes_ccam}}
                        <div class="empty">Aucun prévu</div>
                    {{else}}
                        {{$_interv->_actes_non_cotes}} acte(s)
                    {{/if}}
                </td>
                <td class="text">
                    {{foreach from=$_interv->_ext_codes_ccam item=code}}
                        <div>
                            {{$code->code}}
                        </div>
                    {{/foreach}}
                </td>

                <td>
                    {{foreach from=$_interv->_ref_actes_ccam item=_acte}}
                        {{if $_acte->executant_id != $_anesth_id}}
                            <div class="">
                                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acte->_ref_executant initials=border}}
                                <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                                    {{$_acte->code_acte}}-{{$_acte->code_activite}}-{{$_acte->code_phase}}
                                    {{if $_acte->modificateurs}}
                                        MD:{{$_acte->modificateurs}}
                                    {{/if}}
                                    {{if $_acte->montant_depassement}}
                                        DH:{{$_acte->montant_depassement|currency}}
                                    {{/if}}
                                </span>
                            </div>
                        {{/if}}
                    {{/foreach}}
                </td>
                <td>
                    {{if $_interv->chir_id|array_key_exists:$_interv->_ref_codages_ccam}}
                        {{assign var=_chir_id value=$_interv->chir_id}}
                        {{assign var=_codage value=$_interv->_ref_codages_ccam[$_chir_id][0]}}

                        {{if $_codage->locked}}
                            <i class="fa fa-check" style="color: #078227"></i>
                            Validée
                        {{else}}
                            <i class="fa fa-times" style="color: #820001"></i>
                            En cours
                        {{/if}}
                    {{/if}}
                </td>

                {{if $isAnesth}}
                    <td>
                        {{mb_value object=$_interv field=depassement_anesth}}
                        {{if $_interv->commentaire_depassement_anesth}}
                            <i class="fa fa-lg fa-info-circle" style="color: #2946c9;"
                               title="{{$_interv->commentaire_depassement_anesth}}"></i>
                        {{/if}}
                        {{if $_interv->depassement_anesth}}
                            <br>
                            {{mb_value object=$_interv field=reglement_dh_anesth}}
                        {{/if}}
                    </td>
                {{elseif $isChirurgien}}
                    <td>
                        {{mb_value object=$_interv field=depassement}}
                        {{if $_interv->depassement}}
                            <br>
                            {{mb_value object=$_interv field=reglement_dh_chir}}
                        {{/if}}
                    </td>
                {{/if}}

                <td>
                    {{foreach from=$_interv->_ref_actes_ccam item=_acte}}
                        {{if $_acte->executant_id == $_anesth_id}}
                            <div class="">
                                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acte->_ref_executant initials=border}}
                                <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                                {{$_acte->code_acte}}-{{$_acte->code_activite}}-{{$_acte->code_phase}}
                                    {{if $_acte->modificateurs}}
                                        MD:{{$_acte->modificateurs}}
                                    {{/if}}
                                    {{if $_acte->montant_depassement}}
                                        DH:{{$_acte->montant_depassement|currency}}
                                    {{/if}}
                                </span>
                            </div>
                        {{/if}}
                    {{/foreach}}
                </td>
                <td>
                    {{if $_anesth_id|array_key_exists:$_interv->_ref_codages_ccam}}
                        {{assign var=_codage value=$_interv->_ref_codages_ccam[$_anesth_id][0]}}

                        {{if $_codage->locked}}
                            <i class="fa fa-check" style="color: #078227"></i>
                            Validée
                        {{else}}
                            <i class="fa fa-times" style="color: #820001"></i>
                            En cours
                        {{/if}}
                    {{/if}}
                </td>
                {{if $show_unexported_acts}}
                    <td>
                        {{foreach from=$_interv->_ref_actes_ccam item=_acte}}
                            {{if !$_acte->sent}}
                                <div>
                                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                                        {{$_acte->code_acte}}-{{$_acte->code_activite}}-{{$_acte->code_phase}}
                                    </span>
                                </div>
                            {{/if}}
                        {{/foreach}}
                    </td>
                {{/if}}
            </tr>
            {{foreachelse}}
            <tr>
                <td colspan="9" class="empty">{{tr}}COperation.none_non_cotee{{/tr}}</td>
            </tr>
        {{/foreach}}
    {{/if}}

    {{if $display_consultations}}
        {{if !$all_prats && $total_consultations_non_cotees}}
            <tr class="clear">
                <td colspan="9">
                    <div class="small-warning" style="cursor: pointer;" onclick="showNonCotees();">
                        Attention, vous avez {{$total_consultations_non_cotees}} consultation(s) non cotée(s) durant les
                        3 derniers mois.
                    </div>
                </td>
            </tr>
        {{/if}}
        <tr>
            <th class="section" colspan="9">Consultations ({{$consultations|@count}} / {{$totals.CConsultation}})</th>
        </tr>
        {{foreach from=$consultations item=consult}}
            {{assign var=patient value=$consult->_ref_patient}}
            {{assign var=sejour value=$consult->_ref_sejour}}
            <tr class="alternate">
                {{if $chirSel && $object_classes|@count == 1 && $display_consultations}}
                    <td class="narrow">
                        <input type="checkbox" class="select_objects" data-guid="{{$consult->_guid}}"
                               name="select_{{$consult->_guid}}" onchange="checkObject();"/>
                    </td>
                {{/if}}
                {{if $all_prats}}
                    <td class="text">
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult->_ref_chir}}
                    </td>
                {{/if}}
                <td class="text">
                    <a href="{{$patient->_dossier_cabinet_url}}">
                        <strong class="{{if !$consult->_ref_sejour->entree_reelle}}patient-not-arrived{{/if}}"
                                onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                            {{$patient}}
                        </strong>
                    </a>
                </td>
                <td>
                    {{if $modules.dPcabinet->_can->read && !@$offline}}
                    <a href="#1" onclick="editConsultation('{{$consult->_id}}', updateActes);return false;">
                        {{else}}
                        <a href="#1" title="Impossible d'accéder à la consultation">
                            {{/if}}
                            <span onmouseover="ObjectTooltip.createEx(this, '{{$consult->_guid}}')">
                              {{tr}}CConsultation-consult-on{{/tr}} {{$consult->_datetime|date_format:$conf.date}}
                            </span>
                        </a>
                        {{if $sejour->libelle}}
                            <div class="compact">{{$sejour->libelle}}</div>
                        {{/if}}
                </td>

                <td>
                    {{if !$consult->_count_actes && !$consult->_ext_codes_ccam}}
                        <div class="empty">Aucun prévu</div>
                    {{else}}
                        {{$consult->_actes_non_cotes}} acte(s)
                    {{/if}}
                </td>

                <td class="text">
                    {{foreach from=$consult->_ext_codes_ccam item=code}}
                        <div>{{$code->code}}</div>
                    {{/foreach}}
                </td>

                <td>
                    {{foreach from=$consult->_ref_actes_ccam item=_acte}}
                        <div class="">
                            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acte->_ref_executant initials=border}}
                            <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                            {{$_acte->code_acte}}-{{$_acte->code_activite}}-{{$_acte->code_phase}}
                                {{if $_acte->modificateurs}}
                                    MD:{{$_acte->modificateurs}}
                                {{/if}}
                                {{if $_acte->montant_depassement}}
                                    DH:{{$_acte->montant_depassement|currency}}
                                {{/if}}
                          </span>
                        </div>
                    {{/foreach}}
                    {{foreach from=$consult->_ref_actes_ngap item=_acte}}
                        {{if $_acte->executant_id == $consult->_ref_praticien->_id}}
                            <div class="">
                                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acte->_ref_executant initials=border}}
                                <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte->_guid}}')">
                                    {{if $_acte->quantite > 1}}
                                        {{mb_value object=$_acte field=quantite}} x
                                    {{/if}}{{$_acte->code}}
                                    {{if $_acte->coefficient != 1}}
                                        ({{mb_value object=$_acte field=coefficient}})
                                    {{/if}}
                                    {{if $_acte->complement}}
                                        <span class="circled" title="{{mb_value object=$_acte field=complement}}">
                                            {{$_acte->complement}}</span>
                                    {{/if}}
                                    {{mb_value object=$_acte field=_tarif}}
                                </span>
                            </div>
                        {{/if}}
                    {{/foreach}}
                </td>
                <td></td>
                {{if $display_operations}}
                    <td></td>
                    <td></td>
                {{/if}}
                {{if $show_unexported_acts}}
                    <td></td>
                {{/if}}
            </tr>
            {{foreachelse}}
            <tr>
                <td colspan="9" class="empty">{{tr}}CConsultation.none_non_cotee{{/tr}}</td>
            </tr>
        {{/foreach}}
    {{/if}}

    {{if $display_sejours}}
        {{mb_include module=board template=inc_list_sejours_non_cotes class='CSejour'
        objects=$sejours total_non_cotes=$total_sejours_non_cotes}}
    {{/if}}

    {{if $display_seances}}
        {{mb_include module=board template=inc_list_sejours_non_cotes class='CSejour-seance'
        objects=$seances total_non_cotes=$total_seances_non_cotees}}
    {{/if}}
</table>
