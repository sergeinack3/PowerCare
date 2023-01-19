{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        {{if "dPImeds"|module_active}}
        ImedsResultsWatcher.loadResults();
        {{/if}}
    });
</script>

{{assign var=show_confirmation value="mpm general confirmation"|gconf}}

<div id="list_naissances">
    <table class="main tbl">
        <tbody>
        {{foreach from=$services_selected key=_nom_service item=naissances}}
            <tr>
                <th class="section" colspan="22">{{tr}}CService{{/tr}} &horbar;
                    {{if $_nom_service == 'NP'}}
                        {{tr}}CService-Not placed{{/tr}}
                    {{else}}
                      {{$_nom_service}}
                    {{/if}}
                </th>
            </tr>
            {{foreach from=$naissances item=_naissance}}
                {{assign var=sejour            value=$_naissance->_ref_sejour_enfant}}
                {{assign var=sejour_mere       value=$_naissance->_ref_sejour_maman}}
                {{assign var=examen_nouveau_ne value=$_naissance->_ref_last_examen_nouveau_ne}}
                {{assign var=grossesse         value=$sejour_mere->_ref_grossesse}}
                {{assign var=dossier_perinat   value=$grossesse->_ref_dossier_perinat}}
                {{assign var=patient           value=$sejour->_ref_patient}}
                {{assign var=last_affecation   value=$sejour->_ref_last_affectation}}
                {{assign var=lit               value=$last_affecation->_ref_lit}}
                {{assign var=constantes        value=$patient->_ref_first_constantes}}
                {{assign var=prescription      value=$sejour->_ref_prescription_sejour}}
                {{assign var=oea_exam          value=$examen_nouveau_ne->_oea_exam}}
                <tr>
                    <td>{{mb_value object=$lit field=_view}}</td>
                    <td>{{mb_value object=$_naissance field=num_naissance}}</td>
                    <td>
                        {{if $_naissance->_service_neonatalogie}}
                            {{tr}}common-Yes{{/tr}}
                        {{else}}
                            {{tr}}common-No{{/tr}}
                        {{/if}}
                    </td>
                    <td>
                        <a href="#1" onclick="Sejour.showDossierSoinsModal('{{$sejour->_id}}',
                          'dossier_traitement_compact', {afterClose: Tdb.listNaisances.curry(getForm('tdbNaissances'))});">
                            <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
                                {{mb_value object=$patient field=_view}}
                            </strong>
                        </a>
                    </td>
                    <td>{{mb_value object=$_naissance field=date_time}}</td>
                    <td>{{mb_value object=$patient field=sexe}}</td>
                    <td>{{mb_value object=$constantes field=_poids_g}}</td>
                    <td>{{mb_value object=$_naissance field=rques}}</td>
                    <td>{{mb_value object=$_naissance field=type_allaitement emptyLabel="None"}}</td>
                    <td>
                        {{$grossesse->_semaine_grossesse}} {{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}
                        + {{mb_value object=$grossesse field=_reste_semaine_grossesse}} j
                    </td>
                    <td>
                        {{if $_naissance->by_caesarean}}
                            {{tr}}CNaissance-by_caesarean-court{{/tr}}
                        {{else}}
                            {{tr}}CAccouchement-Vaginal delivery{{/tr}}
                        {{/if}}
                    </td>
                    <td>
                        {{if $examen_nouveau_ne->_guthrie_administration && $examen_nouveau_ne->guthrie_datetime && $examen_nouveau_ne->guthrie_user_id}}
                            {{assign var=administrateur_guthrie value=$examen_nouveau_ne->_ref_guthrie_user_id}}
                            <i class="fa fa-check" style="color: #078227"></i>
                            <span onmouseover="ObjectTooltip.createDOM(
                              this,
                              'guthrie_{{$_naissance->_id}}');">
                                {{tr}}common-Yes{{/tr}} -
                                {{$examen_nouveau_ne->guthrie_datetime|date_format:$conf.date}}
                            </span>
                            <div id="guthrie_{{$_naissance->_id}}" style="display: none;">
                                <table class="tbl">
                                    <tr>
                                        <th colspan="2">{{tr}}CNaissance-Guthrie{{/tr}}</th>
                                    </tr>
                                    <tr>
                                        <th>{{tr}}common-Date{{/tr}}</th>
                                        <th>{{tr}}Who{{/tr}}</th>
                                    </tr>
                                    <tr>
                                        <td>{{$examen_nouveau_ne->guthrie_datetime|date_format:$conf.datetime}}</td>
                                        <td>
                                            <span onmouseover="ObjectTooltip.createEx(
                                              this,
                                              '{{$administrateur_guthrie->_guid}}');">
                                              {{$administrateur_guthrie->_view}}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        {{else}}
                            <i class="fa fa-times" style="color: #820001"></i>
                            {{tr}}common-No{{/tr}}
                        {{/if}}
                    </td>
                    <td class="button">
                        <form name="guthrieSent_{{$_naissance->_id}}" method="post"
                              onsubmit="return onSubmitFormAjax(this);">
                            {{mb_class object=$examen_nouveau_ne}}
                            {{mb_key   object=$examen_nouveau_ne}}
                            <input type="hidden" name="grossesse_id" value="{{$_naissance->grossesse_id}}"/>

                            {{if !$examen_nouveau_ne->_id}}
                                <input type="hidden" name="date" value="{{$dnow|date_format:"%Y-%m-%d"}}"/>
                                <input type="hidden" name="examinateur_id" value="{{$app->user_id}}"/>
                            {{/if}}
                            <input type="hidden" name="naissance_id" value="{{$_naissance->_id}}"/>

                            {{mb_field object=$examen_nouveau_ne typeEnum="checkbox" field=guthrie_envoye
                            onchange="this.form.onsubmit();"}}
                        </form>
                    </td>
                    <td>
                        <span id="oea-{{$_naissance->_id}}">
                            {{mb_include module=maternite template=inc_oea object=$_naissance}}
                        </span>
                    </td>
                    <td>
                        {{if "dPImeds"|module_active}}
                            <div style="margin-bottom: 2px;">
                                {{mb_include module=Imeds template=inc_sejour_labo link="#1" sejour=$sejour
                                onclick="Sejour.showDossierSoinsModal('`$sejour->_id`', 'Imeds');" float="none"}}
                            </div>
                        {{/if}}
                    </td>
                    <td style="text-align: center;">
                        {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
                            {{mb_include module=system template=inc_icon_alerts object=$prescription
                            callback="function() { refreshLineSejour('`$sejour->_id`')}"
                            nb_alerts=$prescription->_count_alertes}}
                        {{else}}
                            {{if $sejour->_ref_prescription_sejour->_count_fast_recent_modif}}
                                <img src="images/icons/ampoule.png"
                                     onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')"/>
                                {{mb_include module=system template=inc_vw_counter_tip
                                count=$sejour->_ref_prescription_sejour->_count_fast_recent_modif}}
                            {{/if}}
                        {{/if}}

                        {{if "mpm general confirmation"|gconf}}
                            {{assign var=really_show_confirmation value=true}}
                            {{if $prescription->_alert_confirmation === null}}
                                {{assign var=really_show_confirmation value=false}}
                            {{/if}}
                            <i id="confirmation_lines_{{$prescription->_id}}"
                              {{if $really_show_confirmation}}
                                class="fa fa-{{if $prescription->_alert_confirmation}}times{{else}}check{{/if}}-circle"
                                style="color: #{{if $prescription->_alert_confirmation}}800{{else}}080{{/if}};
                                  font-size: 1.2em;"
                                title="{{tr}}CPrescription-{{if $prescription->_alert_confirmation}}alert_{{/if}}lines_confirme{{/tr}}"
                              {{/if}}>
                            </i>
                        {{/if}}
                    </td>
                    <td style="text-align: center;">
                        {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
                            {{mb_include module=system template=inc_icon_alerts object=$prescription
                            nb_alerts=$prescription->_count_urgences level="high"}}
                        {{/if}}
                    </td>
                    <td style="text-align: center;">
                        {{if $sejour->_count_tasks}}
                            <img src="images/icons/phone_orange.png"
                                 onclick="Soins.showTasks(
                                   this,
                                   'tooltip-content-tasks-{{$sejour->_id}}', '{{$sejour->_id}}', 'soins');"
                                 onmouseover="this.style.cursor='pointer';"/>
                            {{mb_include module=system template=inc_vw_counter_tip count=$sejour->_count_tasks}}
                            <div id="tooltip-content-tasks-{{$sejour->_id}}"
                                 style="display: none; height: 400px; width: 400px:"></div>
                        {{/if}}

                        {{if $sejour->_count_tasks_not_created}}
                            <img src="images/icons/phone_red.png"
                                 onclick="Soins.showTasksNotCreated(
                                   this,
                                   'tooltip-content-tasks-not-created-{{$sejour->_id}}', '{{$sejour->_id}}');"
                                 onmouseover="this.style.cursor='pointer';"/>
                            {{mb_include module=system template=inc_vw_counter_tip
                            count=$sejour->_count_tasks_not_created}}
                            <div id="tooltip-content-tasks-not-created-{{$sejour->_id}}"
                                 style="display: none; height: 400px; width: 400px:"></div>
                        {{/if}}
                    </td>
                    <td>
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
                        {{if $sejour->sortie_reelle}}
                            <i class="fas fa-check" style="color: green;"></i>
                            {{mb_value object=$sejour field=sortie_reelle}}
                        {{else}}
                            {{mb_value object=$sejour field=sortie_prevue}}
                        {{/if}}
                        </span>
                    </td>
                    <td class="button">
                        <form name="addPMILink_{{$_naissance->_id}}" method="post"
                              onsubmit="return onSubmitFormAjax(this);">
                            {{mb_class object=$dossier_perinat}}
                            {{mb_key   object=$dossier_perinat}}
                            <input type="hidden" name="grossesse_id" value="{{$_naissance->grossesse_id}}"/>
                            {{mb_field object=$dossier_perinat typeEnum="checkbox" field=info_lien_pmi
                            onchange="this.form.onsubmit();"}}

                            {{if !$dossier_perinat->_id}}
                                <input type="hidden" name="date_premier_contact" value="now"/>
                                <input type="hidden" name="consultant_premier_contact_id" value="{{$app->user_id}}"/>
                            {{/if}}
                        </form>
                    </td>
                    {{if $etat == "consult_pediatre"}}
                        <td class="narrow">{{$_naissance->_consult_pediatre}}</td>
                    {{/if}}
                </tr>
            {{/foreach}}
            {{foreachelse}}
            <tr>
                <td class="empty" colspan="22">{{tr}}CNaissance.none{{/tr}}</td>
            </tr>
        {{/foreach}}
        </tbody>
        <thead>
        <tr>
            <th class="title" colspan="22">
                <button type="button" style="float: right;" class="me-no-display"
                        onclick="ObjectTooltip.createDOM(this, 'show_print_buttons', {duration:0, offsetLeft: false});">
                    <i class="fas fa-print fa-lg"></i> {{tr}}Print{{/tr}}
                </button>
                {{me_button label="CNaissance-action-Print list" icon=print mediboard_ext_only=true
                onclick="Tdb.printNaissances(getForm('tdbNaissances'));"}}
                {{me_button label="CNaissance-action-Transmission sheet" icon=print onclick="" mediboard_ext_only=true
                onclick="Tdb.printTransmissionSheet('$order_col', '$order_way');"}}
                {{me_button label="CNaissance-action-List for pediatric nurse|pl" icon=print onclick=""
                mediboard_ext_only=true onclick="Tdb.printPediatricNurse('$order_col', '$order_way');"}}
                {{me_button label="CNaissance-action-List of pediatric consultation|pl" icon=print onclick=""
                mediboard_ext_only=true onclick="Tdb.printPediatricConsult('$order_col', '$order_way');" }}
                {{me_dropdown_button button_label=Print button_icon=print button_class="me-tertiary"
                container_class="me-dropdown-button-right me-float-right"}}
                {{tr}}CNaissance-Dashboard of birth|pl{{/tr}} ({{$total}})
            </th>
        </tr>
        <tr>
            <th rowspan="2" class="narrow">
                {{mb_colonne class=CLit field=nom order_col=$order_col order_way=$order_way function="Tdb.sortBy"}}
            </th>
            <th rowspan="2" class="narrow text">{{mb_title class=CNaissance field=num_naissance}}</th>
            <th rowspan="2" class="narrow">{{mb_title class=CService field=neonatalogie}}</th>
            <th rowspan="2" class="narrow">
                {{mb_colonne class=CSejour field=patient_id order_col=$order_col order_way=$order_way
                function="Tdb.sortBy"}}
            </th>
            <th rowspan="2" class="narrow text">
                {{mb_colonne class=CPatient field=naissance order_col=$order_col order_way=$order_way
                function="Tdb.sortBy"}}
            </th>
            <th rowspan="2" class="narrow">{{mb_title class=CPatient field=sexe}}</th>
            <th rowspan="2" class="narrow">{{mb_title class=CConstantesMedicales field=poids}} (g)</th>
            <th rowspan="2" class="text">{{tr}}CNaissance-Observations{{/tr}}</th>
            <th rowspan="2" class="text">{{tr}}CNaissance-Type of breastfeeding{{/tr}}</th>
            <th rowspan="2" class="narrow">{{tr}}CNaissance-Term{{/tr}}</th>
            <th rowspan="2" class="narrow">{{tr}}CGrossesseAnt-mode_accouchement{{/tr}}</th>
            <th rowspan="2" class="narrow text">{{tr}}CNaissance-GUTHRIE realized{{/tr}}</th>
            <th rowspan="2" class="narrow text">{{tr}}CNaissance-GUTHRIE sent{{/tr}}</th>
            <th rowspan="2" class="narrow text">{{tr}}CNaissance-OEA realized{{/tr}}</th>
            <th rowspan="2" class="text">{{mb_title class=COperation field=labo_bacterio_id}}</th>
            <th colspan="3">{{tr}}CPrescriptionLine-Alert|pl{{/tr}}</th>
            <th rowspan="2" class="narrow">{{mb_title class=CSejour field=sortie}}</th>
            <th rowspan="2" class="narrow">{{mb_title class=CDossierPerinat field=info_lien_pmi}}</th>
            {{if $etat == "consult_pediatre"}}
                <th rowspan="2" class="narrow text">{{tr}}CNaissance-Consultation with pediatrician{{/tr}}</th>
            {{/if}}
        </tr>
        <tr>
            <th>
                <label
                  title="{{tr}}CNaissance-Prescription-desc{{/tr}}">{{tr}}CNaissance-Prescription-court{{/tr}}</label>
            </th>
            <th>
                <label title="{{tr}}CNaissance-urgence-desc{{/tr}}">{{tr}}CNaissance-urgence-court{{/tr}}</label>
            </th>
            <th>{{tr}}CSejour-attentes{{/tr}}</th>
        </tr>
        </thead>
    </table>
</div>

<div id="show_print_buttons" style="display: none;">
    <button type="button" onclick="Tdb.printTransmissionSheet('{{$order_col}}', '{{$order_way}}');">
        <i class="fas fa-print"></i> {{tr}}CNaissance-action-Transmission sheet{{/tr}}
    </button>
    <br/>
    <button type="button" onclick="Tdb.printPediatricNurse('{{$order_col}}', '{{$order_way}}');">
        <i class="fas fa-print"></i> {{tr}}CNaissance-action-List for pediatric nurse|pl{{/tr}}
    </button>
    <br/>
    <button type="button" onclick="Tdb.printPediatricConsult('{{$order_col}}', '{{$order_way}}');">
        <i class="fas fa-print"></i> {{tr}}CNaissance-action-List of pediatric consultation|pl{{/tr}}
    </button>
</div>
