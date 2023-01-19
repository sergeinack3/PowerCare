{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=maternite template=vw_edit_consultation_post_natale}}

{{if !$consult_post_natale->_id}}
    <div class="small-info">{{tr}}CConsultationPostNatale-msg_min_info_for_create{{/tr}}</div>
    {{mb_return}}
{{/if}}

<script>
    Main.add(function () {
        Control.Tabs.create('tab-consult_postnatale-{{$consult_post_natale->_guid}}', true, {
            foldable: true {{if $print}},
            unfolded: true{{/if}}});
        Control.Tabs.create('tab-naissances-{{$consult_post_natale->_guid}}', true, {
            foldable: true {{if $print}},
            unfolded: true{{/if}}});
    });
</script>
<ul id="tab-consult_postnatale-{{$consult_post_natale->_guid}}" class="control_tabs">
    <li><a
          href="#interrogatoire-{{$consult_post_natale->_guid}}">{{tr}}CConsultationPostNatale-interrogatoire{{/tr}}</a>
    </li>
    <li><a href="#examen_clinique-{{$consult_post_natale->_guid}}">{{tr}}CConsultationPostNatale-examen{{/tr}}</a></li>
    <li>
        <a
          href="#information_prescriptions-{{$consult_post_natale->_guid}}">{{tr}}CConsultationPostNatale-info_prescription{{/tr}}</a>
    </li>
</ul>

<div id="interrogatoire-{{$consult_post_natale->_guid}}" style="display: none;">
    <table class="main">
        <tr>
            {{if !'oxCabinet'|module_active}}
                <td class="halfPane">
                    <fieldset>
                        <legend>{{tr}}CConsultationPostNatale-new_borns{{/tr}}</legend>
                        {{if !$dossier->admission_id}}
                            {{mb_include module=maternite template=inc_dossier_mater_admission_choix_sejour}}
                        {{elseif $grossesse->_ref_naissances|@count}}
                            <ul id="tab-naissances-{{$consult_post_natale->_guid}}" class="control_tabs small">
                                {{foreach from=$grossesse->_ref_naissances item=naissance}}
                                    {{assign var=enfant value=$naissance->_ref_sejour_enfant->_ref_patient}}
                                    <li>
                                        <a href="#tab-{{$consult_post_natale->_guid}}-{{$naissance->_guid}}">
                                            {{$enfant}} -
                                            {{mb_value object=$enfant field=sexe}}
                                            <br/>
                                            {{mb_value object=$enfant field=naissance}}
                                            ({{mb_value object=$enfant field=_age}})
                                        </a>
                                    </li>
                                {{/foreach}}
                                <li style="float: right;">
                                    <button type="button" class="add not-printable me-float-none" style="float: left;"
                                            {{if !$grossesse->active}}disabled{{/if}}
                                            onclick="Naissance.edit(0, null, '{{$dossier->admission_id}}')">{{tr}}CNaissance{{/tr}}</button>
                                </li>
                            </ul>
                        {{else}}
                            <div class="big-info">
                                {{tr}}CNaissance.none{{/tr}}
                                <br/>
                                <button type="button" class="add not-printable me-float-none" style="float: left;"
                                        {{if !$grossesse->active}}disabled{{/if}}
                                        onclick="Naissance.edit(0, null, '{{$dossier->admission_id}}')">{{tr}}CNaissance{{/tr}}</button>
                            </div>
                        {{/if}}

                        {{foreach from=$grossesse->_ref_naissances item=naissance}}
                            {{assign var=naissance_id value=$naissance->_id}}
                            {{assign var=enfant value=$naissance->_ref_sejour_enfant->_ref_patient}}
                            <div id="tab-{{$consult_post_natale->_guid}}-{{$naissance->_guid}}">
                                <form name="Consult-postnatale-enfant-{{$consult_post_natale->_guid}}-{{$naissance->_guid}}"
                                      method="post"
                                      onsubmit="return onSubmitFormAjax(this);">
                                    {{assign var=_consult_postnat_enfant value=$empty_postnat_enfant}}
                                    {{if isset($consult_post_natale->_ref_consult_enfants_by_naissance.$naissance_id|smarty:nodefaults)}}
                                        {{assign var=_consult_postnat_enfant value=$consult_post_natale->_ref_consult_enfants_by_naissance.$naissance_id}}
                                    {{/if}}
                                    {{mb_class object=$_consult_postnat_enfant}}
                                    {{mb_key   object=$_consult_postnat_enfant}}
                                    <input type="hidden" name="naissance_id" value="{{$naissance->_id}}"/>
                                    <input type="hidden" name="consultation_post_natale_id"
                                           value="{{$consult_post_natale->_id}}"/>
                                    <input type="hidden" name="_count_changes" value="0"/>
                                    <table class="form me-no-align me-no-box-shadow me-small-form">
                                        <tr>
                                            <th
                                              class="halfPane">{{mb_label object=$_consult_postnat_enfant field=enfant_present}}</th>
                                            <td
                                              class="narrow">{{mb_field object=$_consult_postnat_enfant field=enfant_present default=""}}</td>
                                            <th class="narrow">{{mb_label object=$_consult_postnat_enfant field=poids}}</th>
                                            <td>{{mb_field object=$_consult_postnat_enfant field=poids}} g</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=etat_enfant}}</th>
                                            <td colspan="3">
                                                {{mb_field object=$_consult_postnat_enfant field=etat_enfant
                                                style="width: 20em;" emptyLabel="CConsultationPostNatEnfant.etat_enfant."}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=date_deces}}</th>
                                            <td colspan="3">
                                                {{mb_field object=$_consult_postnat_enfant field=date_deces register=true
                                                form="Consult-postnatale-enfant-`$consult_post_natale->_guid`-`$naissance->_guid`"}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=rehospitalisation}}</th>
                                            <td
                                              colspan="3">{{mb_field object=$_consult_postnat_enfant field=rehospitalisation default=""}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=motif_rehospitalisation}}</th>
                                            <td
                                              colspan="3">{{mb_field object=$_consult_postnat_enfant field=motif_rehospitalisation}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=etat_enfant}}</th>
                                            <td colspan="3">
                                                {{mb_field object=$_consult_postnat_enfant field=allaitement
                                                style="width: 20em;" emptyLabel="CConsultationPostNatEnfant.allaitement."}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{tr}}CConsultationPostNatale-if_articificial{{/tr}}</th>
                                            <td colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=arret_allaitement}}</th>
                                            <td colspan="3">
                                                {{mb_field object=$_consult_postnat_enfant field=arret_allaitement register=true
                                                form="Consult-postnatale-enfant-`$consult_post_natale->_guid`-`$naissance->_guid`"}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=nb_semaines_allaitement}}</th>
                                            <td
                                              colspan="3">{{mb_field object=$_consult_postnat_enfant field=nb_semaines_allaitement}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=motif_arret_allaitement}}</th>
                                            <td
                                              colspan="3">{{mb_field object=$_consult_postnat_enfant field=motif_arret_allaitement}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{tr}}CConsultationPostNatale-if_complement{{/tr}}</th>
                                            <td colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <th>{{tr}}Nature{{/tr}}</th>
                                            <td>
                                                {{mb_field object=$_consult_postnat_enfant field=complement_eau typeEnum=checkbox}}
                                                {{mb_label object=$_consult_postnat_enfant field=complement_eau typeEnum=checkbox}}
                                                <br/>
                                                {{mb_field object=$_consult_postnat_enfant field=complement_eau_sucree typeEnum=checkbox}}
                                                {{mb_label object=$_consult_postnat_enfant field=complement_eau_sucree typeEnum=checkbox}}
                                                <br/>
                                                {{mb_field object=$_consult_postnat_enfant field=complement_prepa_lactee typeEnum=checkbox}}
                                                {{mb_label object=$_consult_postnat_enfant field=complement_prepa_lactee typeEnum=checkbox}}
                                            </td>
                                            <th>{{tr}}Medium{{/tr}}</th>
                                            <td>
                                                {{mb_field object=$_consult_postnat_enfant field=complement_tasse typeEnum=checkbox}}
                                                {{mb_label object=$_consult_postnat_enfant field=complement_tasse typeEnum=checkbox}}
                                                <br/>
                                                {{mb_field object=$_consult_postnat_enfant field=complement_cuillere typeEnum=checkbox}}
                                                {{mb_label object=$_consult_postnat_enfant field=complement_cuillere typeEnum=checkbox}}
                                                <br/>
                                                {{mb_field object=$_consult_postnat_enfant field=complement_biberon typeEnum=checkbox}}
                                                {{mb_label object=$_consult_postnat_enfant field=complement_biberon typeEnum=checkbox}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$_consult_postnat_enfant field=indication_complement}}</th>
                                            <td
                                              colspan="3">{{mb_field object=$_consult_postnat_enfant field=indication_complement}}</td>
                                        </tr>
                                    </table>
                                </form>
                            </div>
                        {{/foreach}}
                    </fieldset>
                </td>
            {{/if}}
            <td class="halfPane">
                <fieldset>
                    <legend>{{tr}}CNaissance-Mother{{/tr}}</legend>
                    <form name="Consult-postnatale-maman-{{$consult_post_natale->_guid}}" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$consult_post_natale}}
                        {{mb_key   object=$consult_post_natale}}
                        <input type="hidden" name="_count_changes" value="0"/>
                        <table class="form me-no-align me-no-box-shadow me-small-form">
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=patho_postacc}}</th>
                                <td
                                  colspan="3">{{mb_field object=$consult_post_natale field=patho_postacc default=""}}</td>
                            </tr>
                            <tr>
                                <th>{{tr}}CConsultationPostNatale-if_yes{{/tr}}
                                    , {{mb_label object=$consult_post_natale field=hospi_postacc}}</th>
                                <td
                                  colspan="3">{{mb_field object=$consult_post_natale field=hospi_postacc default=""}}</td>
                            </tr>
                            <tr>
                                <th>{{tr}}CConsultationPostNatale-if_yes{{/tr}},</th>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=date_hospi_postacc}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=date_hospi_postacc register=true
                                    form="Consult-postnatale-maman-`$consult_post_natale->_guid`"}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=duree_hospi_postacc}}</th>
                                <td colspan="3">{{mb_field object=$consult_post_natale field=duree_hospi_postacc}}j
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=motif_hospi_postacc}}</th>
                                <td colspan="3">{{mb_field object=$consult_post_natale field=motif_hospi_postacc}}</td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=troubles_fct}}</th>
                                <td
                                  colspan="3">{{mb_field object=$consult_post_natale field=troubles_fct default=""}}</td>
                            </tr>
                            <tr>
                                <th class="halfPane">Si oui,</th>
                                <td class="narrow">
                                    {{mb_field object=$consult_post_natale field=doul_pelv typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=doul_pelv typeEnum=checkbox}}
                                </td>
                                <td class="narrow">
                                    {{mb_field object=$consult_post_natale field=pert_urin typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=pert_urin typeEnum=checkbox}}
                                </td>
                                <td>
                                    {{mb_field object=$consult_post_natale field=leucorrhees typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=leucorrhees typeEnum=checkbox}}
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    {{mb_field object=$consult_post_natale field=pertes_gaz typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=pertes_gaz typeEnum=checkbox}}
                                </td>
                                <td>
                                    {{mb_field object=$consult_post_natale field=metrorragies typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=metrorragies typeEnum=checkbox}}
                                </td>
                                <td>
                                    {{mb_field object=$consult_post_natale field=pertes_fecales typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=pertes_fecales typeEnum=checkbox}}
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    {{mb_field object=$consult_post_natale field=compl_episio typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=compl_episio typeEnum=checkbox}}
                                </td>
                                <td colspan="2">
                                    {{mb_field object=$consult_post_natale field=baby_blues typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=baby_blues typeEnum=checkbox}}
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=autres_troubles typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=autres_troubles typeEnum=checkbox}}
                                    {{mb_label object=$consult_post_natale field=desc_autres_troubles style="display:none"}}
                                    {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CConsultationPostNatale-desc_autres_troubles"}}
                                    {{mb_field object=$consult_post_natale field=desc_autres_troubles style="width: 20em;" placeholder=$placeholder}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=retour_couches}}</th>
                                <td
                                  colspan="3">{{mb_field object=$consult_post_natale field=retour_couches default=""}}</td>
                            </tr>
                            <tr>
                                <th>Si oui, {{mb_label object=$consult_post_natale field=date_retour_couches}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=date_retour_couches register=true
                                    form="Consult-postnatale-maman-`$consult_post_natale->_guid`"}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{tr}}CConsultationPostNatale-sexualite_contraception-title{{/tr}}</th>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=reprise_rapports}}</th>
                                <td
                                  colspan="3">{{mb_field object=$consult_post_natale field=reprise_rapports default=""}}</td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=contraception}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=contraception
                                    style="width: 20em;" emptyLabel="CConsultationPostNatale.contraception."}}
                                    <br/>
                                    {{mb_label object=$consult_post_natale field=desc_contraception style="display:none"}}
                                    {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CConsultationPostNatale-desc_contraception"}}
                                    {{mb_field object=$consult_post_natale field=desc_contraception style="width: 20em;" placeholder=$placeholder}}
                                </td>
                            </tr>
                        </table>
                    </form>
                </fieldset>
            </td>
        </tr>
    </table>
</div>

<div id="examen_clinique-{{$consult_post_natale->_guid}}" style="display: none;">
    <table class="main">
        <tr>
            <td class="halfPane">
                <fieldset>
                    <legend>{{tr}}CConsultationPostNatale-constantes_id{{/tr}}</legend>
                    {{assign var=constantes value=$consult_post_natale->_ref_constantes}}
                    {{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:'list_constantes'}}
                    <form name="Consult-postnatale-constantes-{{$consult_post_natale->_guid}}" action="?" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$constantes}}
                        {{mb_key   object=$constantes}}
                        {{mb_field object=$constantes field=patient_id hidden=true}}
                        {{mb_field object=$constantes field=context_class hidden=true}}
                        {{mb_field object=$constantes field=context_id hidden=true}}
                        {{mb_field object=$constantes field=datetime hidden=true}}
                        {{mb_field object=$constantes field=user_id hidden=true}}
                        {{mb_field object=$constantes field=_unite_ta hidden=1}}
                        <input type="hidden" name="_count_changes" value="0"/>
                        <input type="hidden" name="_object_guid" value="{{$consult_post_natale->_guid}}">
                        <input type="hidden" name="_object_field" value="consult_postnatale_constantes_id">
                        <table class="form me-no-box-shadow me-no-align me-small-form">
                            <tr>
                                <th class="halfPane">
                                    {{mb_label object=$constantes field=ta}}
                                    <small class="opacity-50">({{$constants_list.ta.unit}})</small>
                                </th>
                                <td>
                                    {{mb_field object=$constantes field=_ta_systole size=3}}
                                    /
                                    {{mb_field object=$constantes field=_ta_diastole size=3}}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    {{mb_label object=$constantes field=co_expire}}
                                    <small class="opacity-50">(ppm)</small>
                                </th>
                                <td>{{mb_field object=$constantes field=co_expire size=3}}</td>
                            </tr>
                            <tr>
                                <th>
                                    {{mb_label object=$constantes field=poids}}
                                    <small class="opacity-50">(kg)</small>
                                </th>
                                <td>
                                    {{mb_field object=$constantes field=poids size=3}}
                                </td>
                            </tr>
                        </table>
                    </form>
                </fieldset>
            </td>
            <td class="halfPane">
                <fieldset>
                    <legend>Examen</legend>
                    <form name="Consult-postnatale-examen-{{$consult_post_natale->_guid}}" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$consult_post_natale}}
                        {{mb_key   object=$consult_post_natale}}
                        <input type="hidden" name="_count_changes" value="0"/>
                        <table class="form me-no-align me-no-box-shadow me-small-form">
                            <tr>
                                <th class="halfPane">{{mb_label object=$consult_post_natale field=exam_seins}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=exam_seins
                                    style="width: 15em;" emptyLabel="CConsultationPostNatale.exam_seins."}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=exam_cic_perin}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=exam_cic_perin
                                    style="width: 15em;" emptyLabel="CConsultationPostNatale.exam_cic_perin."}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=exam_cic_cesar}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=exam_cic_cesar
                                    style="width: 15em;" emptyLabel="CConsultationPostNatale.exam_cic_cesar."}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=exam_speculum}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=exam_speculum
                                    style="width: 15em;" emptyLabel="CConsultationPostNatale.exam_speculum."}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=exam_TV}}</th>
                                <td colspan="3">
                                    {{mb_field object=$consult_post_natale field=exam_TV style="width: 15em;"
                                    emptyLabel="CConsultationPostNatale.exam_TV."}}
                                </td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=exam_stat_pelv}}</th>
                                <td class="narrow">
                                    {{mb_field object=$consult_post_natale field=exam_stat_pelv style="width: 15em;"
                                    emptyLabel="CConsultationPostNatale.exam_stat_pelv."}}
                                </td>
                                <th
                                  class="narrow">{{mb_label object=$consult_post_natale field=exam_stat_pelv_testing}}</th>
                                <td>{{mb_field object=$consult_post_natale field=exam_stat_pelv_testing}}</td>
                            </tr>
                            <tr>
                                <th>{{mb_label object=$consult_post_natale field=exam_autre}}</th>
                                <td colspan="3">
                                    {{if !$print}}
                                        {{mb_field object=$consult_post_natale field=exam_autre}}
                                    {{else}}
                                        {{mb_value object=$consult_post_natale field=exam_autre}}
                                    {{/if}}
                                </td>
                            </tr>
                        </table>
                    </form>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <fieldset>
                    <legend>{{tr}}CConsultationPostNatale-exam_conclusion-court{{/tr}}</legend>
                    <form name="Consult-postnatale-conclusion-examen-{{$consult_post_natale->_guid}}" method="post"
                          onsubmit="return onSubmitFormAjax(this);">
                        {{mb_class object=$consult_post_natale}}
                        {{mb_key   object=$consult_post_natale}}
                        <input type="hidden" name="_count_changes" value="0"/>
                        <table class="form me-no-box-shadow me-no-align me-small-form">
                            <tr>
                                <th class="halfPane">{{mb_label object=$consult_post_natale field=exam_conclusion}}</th>
                                <td>
                                    {{mb_field object=$consult_post_natale field=exam_conclusion separator="<br />"
                                    typeEnum=radio emptyLabel="CConsultationPostNatale.exam_conclusion."}}
                                </td>
                            </tr>
                        </table>
                    </form>
                </fieldset>
            </td>
        </tr>
    </table>
</div>

<div id="information_prescriptions-{{$consult_post_natale->_guid}}" style="display: none;">
    <form name="Consult-postnatale-info-presc-{{$consult_post_natale->_guid}}" method="post"
          onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$consult_post_natale}}
        {{mb_key   object=$consult_post_natale}}
        <input type="hidden" name="_count_changes" value="0"/>
        <table class="form me-no-align me-no-box-shadow">
            <tr>
                <th class="halfPane">{{mb_label object=$consult_post_natale field=infos_remises}}</th>
                <td>
                    {{if !$print}}
                        {{mb_field object=$consult_post_natale field=infos_remises}}
                    {{else}}
                        {{mb_value object=$consult_post_natale field=infos_remises}}
                    {{/if}}
                </td>
            </tr>
            <tr>
                <th>{{tr}}CConsultation-back-examcomp{{/tr}}</th>
                <td>
                    {{mb_field object=$consult_post_natale field=exam_comp_FCV typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=exam_comp_FCV typeEnum=checkbox}}
                    <br/>
                    {{mb_field object=$consult_post_natale field=exam_comp_biologie typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=exam_comp_biologie typeEnum=checkbox}}
                    <br/>
                    {{mb_field object=$consult_post_natale field=exam_comp_autre typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=exam_comp_autre typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=exam_comp_autre_desc style="display:none"}}
                    {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CConsultationPostNatale-exam_comp_autre_desc"}}
                    {{mb_field object=$consult_post_natale field=exam_comp_autre_desc style="width: 20em;" placeholder=$placeholder}}
            </tr>
            <tr>
                <th>{{mb_label object=$consult_post_natale field=reeduc}}</th>
                <td>{{mb_field object=$consult_post_natale field=reeduc default=""}}</td>
            </tr>
            <tr>
                <th>Si oui,</th>
                <td>
                    {{mb_field object=$consult_post_natale field=reeduc_perin typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=reeduc_perin typeEnum=checkbox}}
                    <br/>
                    {{mb_field object=$consult_post_natale field=reeduc_abdo typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=reeduc_abdo typeEnum=checkbox}}
                    <br/>
                    {{mb_field object=$consult_post_natale field=reeduc_autre typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=reeduc_autre typeEnum=checkbox}}
                    {{mb_label object=$consult_post_natale field=reeduc_autre_desc style="display:none"}}
                    {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CConsultationPostNatale-reeduc_autre_desc"}}
                    {{mb_field object=$consult_post_natale field=reeduc_autre_desc style="width: 20em;" placeholder=$placeholder}}
                </td>
            </tr>
            <tr>
                <th>{{mb_label object=$consult_post_natale field=contraception_presc}}</th>
                <td>
                    {{mb_field object=$consult_post_natale field=contraception_presc
                    style="width: 20em;" emptyLabel="CConsultationPostNatale.contraception_presc."}}
                    {{mb_label object=$consult_post_natale field=autre_contraception_presc style="display:none"}}
                    {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CConsultationPostNatale-autre_contraception_presc"}}
                    {{mb_field object=$consult_post_natale field=autre_contraception_presc style="width: 20em;" placeholder=$placeholder}}
                </td>
            </tr>
            <tr>
                <th>{{mb_label object=$consult_post_natale field=arret_travail}}</th>
                <td>{{mb_field object=$consult_post_natale field=arret_travail default=""}}</td>
            </tr>
        </table>
    </form>
</div>
