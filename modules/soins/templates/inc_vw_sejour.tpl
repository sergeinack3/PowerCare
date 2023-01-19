{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=lite_view value=false}}
{{mb_default var=show_full_affectation value=false}}
{{mb_default var=default_tab value=""}}
{{mb_default var=board value=""}}
{{mb_default var=count_validation_pharma value=false}}
{{mb_default var=getSourceLabo value=false}}

{{assign var=mod_hotellerie value="hotellerie"|module_active}}

{{if $mod_hotellerie}}
    {{mb_script module=hotellerie script=hotellerie ajax=1}}
{{/if}}

{{if "OxLaboClient"|module_active && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
    {{mb_script module=oxLaboClient script=oxlaboalert ajax=true}}
    {{mb_script module=oxLaboClient script=oxlaboclient ajax=true}}
{{/if}}

{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
{{assign var=_sejour_id value=$sejour->_id}}
{{assign var=affectation value=$sejour->_ref_curr_affectation}}
{{mb_default var=prescription value=$sejour->_ref_prescription_sejour}}
{{mb_default var=allow_edit_cleanup value=1}}

{{if $board}}
    {{assign var=style_span value="position: relative !important; padding: 1px; top: -10px;"}}
{{/if}}

<script>
    Main.add(function () {
        {{if isset($sejour_id|smarty:nodefaults) && $sejour_id && "dPImeds"|module_active}}
        ImedsResultsWatcher.loadResults();
        {{/if}}
    });
</script>

{{if ($service_id && $service_id != "NP") || $show_affectation || $function->_id || $discipline->_id || $praticien->_id}}
    <td class="text {{if $sejour->isolement}}isolement{{/if}} {{if !$affectation->_id}}compact{{/if}}">
        {{if !$board && (@$modules.dPplanningOp->_can->admin || ("soins UserSejour can_edit_user_sejour"|gconf && @$modules.dPplanningOp->_can->edit))}}
            <button class="mediuser_black notext"
                    onclick="Soins.paramUserSejour('{{$affectation->sejour_id}}', '{{if $service_id != "NP" }}{{$service_id}}{{/if}}', null, '{{$date}}');"
                    onmouseover="ObjectTooltip.createDOM(this, 'affectation_{{$sejour->_guid}}');"
                    {{if $sejour->_ref_users_sejour|@count == 0}}style="opacity: 0.6;" {{/if}}></button>
            {{if $sejour->_ref_users_sejour|@count}}
                <span class="countertip">{{$sejour->_ref_users_sejour|@count}}</span>
            {{/if}}
            {{mb_include module=planningOp template=vw_user_sejour_table}}
        {{/if}}

        {{if $sejour->_ref_curr_brancardage && $sejour->_ref_curr_brancardage->_id && "brancardage General see_demande_ecap"|gconf}}
            <span id="brancard_{{$sejour->_id}}">
        <a href="#" style="display: inline"
           onclick="Brancardage.jumelles('{{$sejour->_ref_curr_brancardage->context_class}}-{{$sejour->_ref_curr_brancardage->context_id}}');">
          <img src="images/icons/brancard_cligno.gif" title="{{tr}}CBrancardage.brancard_cligno{{/tr}}"/>
        </a>
      </span>
        {{/if}}

        {{if $affectation->_id && $affectation->lit_id}}
            {{if $show_full_affectation}}
                {{$affectation->_ref_lit->_view}}
            {{else}}
                {{mb_value object=$affectation->_ref_lit field=nom}}
            {{/if}}
        {{elseif $sejour->_ref_next_affectation->_id && $sejour->_ref_next_affectation->lit_id}}
            {{if $show_full_affectation}}
                {{$sejour->_ref_next_affectation->_ref_lit->_view}}
            {{else}}
                {{mb_value object=$sejour->_ref_next_affectation->_ref_lit field=nom}}
            {{/if}}
        {{/if}}

        {{if "soins Sejour see_initiale_infirmiere"|gconf && array_key_exists('infirmiere',$sejour->_ref_users_by_type)}}
            <br/>
            {{foreach from=$sejour->_ref_group_users_by_type.infirmiere item=_inf}}
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_inf->_ref_user initials=border}}
            {{/foreach}}
        {{/if}}
    </td>
{{/if}}

{{if $mod_hotellerie && $allow_edit_cleanup}}
    <td style="text-align: center">
        {{if $affectation->_id && $affectation->lit_id}}
            {{mb_include module=hotellerie template=inc_icon_cleanup affectation=$affectation}}
        {{elseif $sejour->_ref_next_affectation->_id && $sejour->_ref_next_affectation->lit_id}}
            {{mb_include module=hotellerie template=inc_icon_cleanup affectation=$sejour->_ref_next_affectation}}
        {{/if}}
    </td>
{{/if}}

<td class="narrow">
    {{mb_include module=patients template=inc_vw_photo_identite size=32 nodebug=true sejour_conf=$sejour->presence_confidentielle}}
</td>

<td class="text">
    {{if $sejour->presence_confidentielle}}
        {{mb_include module=planningOp template=inc_badge_sejour_conf}}
    {{/if}}
    <button class="lookup notext me-tertiary me-btn-small me-dark" style="float:right;"
            onclick="Soins.popEtatSejour('{{$sejour->_id}}');">
        {{tr}}CSejour-_etat{{/tr}}
    </button>
    {{if "syntheseMed"|module_active && !$lite_view}}
        {{mb_include module=syntheseMed template=inc_button_synthese type_acces=ecap float="right"}}
    {{/if}}
    {{if $lite_view && "dPprescription"|module_active && "mpm Analyse_cat see_risque_pop"|gconf}}
        <span class="compact" style="float:right">
      {{if $patient->naissance}}
          {{if $patient->_annees <= "mpm Risque_pop age_min"|gconf}}
              < {{"mpm Risque_pop age_min"|gconf}} ans
          {{elseif $patient->_annees >= "mpm Risque_pop age_max"|gconf}}
              > {{"mpm Risque_pop age_max"|gconf}} ans
          {{/if}}
      {{/if}}
            {{assign var=grossesse value=$patient->_ref_last_grossesse}}
            {{if $grossesse && $grossesse->_id && $grossesse->active}}
                <img onmouseover="ObjectTooltip.createEx(this, '{{$patient->_ref_last_grossesse->_guid}}')"
                     src="style/mediboard_ext/images/icons/grossesse.png"
                     style="background-color: rgb(255, 215, 247);"/>
            {{/if}}

            {{assign var=score_asa value="mpm Risque_pop score_asa"|gconf}}
            {{if $score_asa}}
                {{foreach from=$sejour->_ref_operations item=_interv}}
                    {{if $_interv->ASA >= $score_asa}}
                        ASA &ge; {{$score_asa}}
                    {{/if}}
                {{/foreach}}
            {{/if}}
    </span>
    {{/if}}

    {{if $affectation->_in_permission}}
        {{mb_include module=soins template=inc_button_permission affectation=$affectation float="right"}}
        <span class="texticon" style="float: right;">PERM.</span>
    {{/if}}

    {{if $affectation->_id}}
        {{mb_include module=planningOp template=inc_icon_autorisation_permission affectation=$affectation float="right"}}
    {{/if}}

    {{if $sejour->_ref_prescription_sejour}}
        {{assign var=prescription value=$sejour->_ref_prescription_sejour}}
        <div class="text" style="font-size: 12pt; float: right;">
            {{mb_include module=prescription template=vw_line_important lines=$prescription->_ref_lines_important}}
        </div>
    {{/if}}
    {{assign var=sejour_nda value=$sejour->_NDA}}
    {{assign var=sejour_id value=$sejour->_id}}
    {{assign var=onclick value="showDossierSoins('$sejour_id','$date', '$default_tab');"|nl2br}}
    {{if "oxLaboClient"|module_active && "oxLaboClient alert_result_critical modal_alert_result_critical"|gconf && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
      {{assign var=onclick value="showDossierSoins('$sejour_id','$date', '$default_tab');OxLaboClient.showModaleCriticalResult(`$sejour_id`);"}}
    {{/if}}
    {{mb_include module=ssr template=inc_view_patient onclick=$onclick|smarty:nodefaults _sejour=$sejour bebe_indicator=true}}

    {{if ($service_id && $service_id != "NP")}}
        <div style="float: right;">
            {{assign var=next_date value='Ox\Core\CMbDT::date'|static_call:"+1 day":$date}}
            {{mb_include module=hospi template=inc_vw_icones_sejour sejour=$sejour curr_affectation=$affectation aff_next=$sejour->_ref_next_affectation demain=$next_date}}
        </div>
    {{/if}}
    {{if "nouveal"|module_active && "nouveal general active_prm"|gconf}}
        <div style="float: right;">
            {{mb_include module=nouveal template=inc_vw_etat_patient}}
        </div>
    {{/if}}

    {{if $count_validation_pharma}}
        {{assign var=prescription_id value=$sejour->_ref_prescription_sejour->_id}}
        {{if $count_validation_pharma.$prescription_id}}
            {{assign var=cnt_prescription value=$count_validation_pharma.$prescription_id}}
            <div class="prescription_indicateur
        {{if $cnt_prescription.valide == 0}}
          prescription_indicateur_gris
        {{elseif $cnt_prescription.valide == $cnt_prescription.count}}
          prescription_indicateur_vert
        {{else}}
          prescription_indicateur_orange
        {{/if}}
        "
                 title="{{tr var1=$cnt_prescription.valide
                 var2=$cnt_prescription.count}}CPrescription-Number of validated prescriptions{{/tr}}">
                <span>{{$cnt_prescription.valide}} / {{$cnt_prescription.count}}</span>
            </div>
        {{/if}}
    {{/if}}
</td>

{{if "dPImeds"|module_active}}
    <td>
    <span onclick="showDossierSoins('{{$sejour->_id}}','{{$date}}','Imeds');">
    {{mb_include module=Imeds template=inc_sejour_labo link="#"}}
    </span>
    </td>
{{/if}}

{{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
  <td>
    {{assign var=sejour_nda value=$sejour->_NDA}}
    {{if array_key_exists($sejour_nda, $labo_alert_by_nda)}}
      <span id="OxLaboAlert_{{$sejour_nda}}">
        {{mb_include module=oxLaboClient template=vw_alerts object=$sejour object_id=$sejour->_id object_class=$sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$labo_alert_by_nda.$sejour_nda.total alerts=$labo_alert_by_nda.$sejour_nda}}
      </span>
    {{/if}}
    {{if array_key_exists($sejour_nda, $new_labo_alert_by_nda)}}
      <span id="OxLaboNewAlert_{{$sejour_nda}}">
        {{mb_include module=oxLaboClient template=vw_alerts object=$sejour object_id=$sejour->_id object_class=$sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$new_labo_alert_by_nda.$sejour_nda|@count alerts=$new_labo_alert_by_nda.$sejour_nda alert_new_result=true}}
      </span>
    {{/if}}
  </td>
{{/if}}

{{if $count_validation_pharma}}
    <td style="text-align: center;">
        {{assign var=alert_pharma value=$sejour->_ref_prescription_sejour->_refs_alerts_not_handled}}
        {{if $alert_pharma && $alert_pharma|@count > 0}}
            {{mb_include module=system template=inc_icon_alerts object=$sejour->_ref_prescription_sejour
            callback="refreshLineSejour.curry('`$sejour->_id`')" tag="prescription_pharma_modification"
            nb_alerts=$alert_pharma|@count img_ampoule="ampoule_violet" keep_img=true}}
        {{/if}}
    </td>
{{/if}}
{{if !$lite_view}}
    {{if "dPprescription"|module_active}}
        <td style="text-align: center;">
            {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
                {{mb_include module=system template=inc_icon_alerts
                object=$prescription
                callback="function() { refreshLineSejour('`$sejour->_id`')}"
                nb_alerts=$prescription->_count_alertes}}
            {{else}}
                {{if $sejour->_ref_prescription_sejour->_count_fast_recent_modif}}
                    {{mb_include module=system template=inc_bulb img_ampoule="ampoule" event_trigger="onmouseover"
                    event_function="ObjectTooltip.createEx(this, '`$prescription->_guid`')"
                    alert_nb=$sejour->_ref_prescription_sejour->_count_fast_recent_modif}}
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
                    style="color: #{{if $prescription->_alert_confirmation}}800{{else}}080{{/if}}; font-size: 1.2em;"
                    title="{{tr}}CPrescription-{{if $prescription->_alert_confirmation}}alert_{{/if}}lines_confirme{{/tr}}"
                  {{/if}}>
                </i>
            {{/if}}
        </td>
        <td style="text-align: center;">
            {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
                {{mb_include module=system template=inc_icon_alerts
                object=$prescription
                callback="function() { refreshLineSejour('`$sejour->_id`')}"
                nb_alerts=$prescription->_count_urgences
                level="high"}}
            {{else}}
                {{if $sejour->_ref_prescription_sejour->_count_urgences}}
                    {{mb_include module=system template=inc_bulb img_ampoule="ampoule_urgence" event_trigger="onmouseover"
                    event_function="ObjectTooltip.createEx(this, '`$prescription->_guid`')"
                    alert_nb=$sejour->_ref_prescription_sejour->_count_urgences}}
                {{/if}}
            {{/if}}
        </td>
    {{/if}}
    <td style="text-align: center;">
        {{if $sejour->_count_tasks}}
            <img src="images/icons/phone_orange.png"
                 onclick="Soins.showTasks(this, 'tooltip-content-tasks-{{$sejour->_id}}', '{{$sejour->_id}}', 'soins');"
                 onmouseover="this.style.cursor='pointer';"/>
            {{mb_include module=system template=inc_vw_counter_tip count=$sejour->_count_tasks}}
            <div id="tooltip-content-tasks-{{$sejour->_id}}" style="display: none; height: 400px; width: 400px:"></div>
        {{/if}}

        {{if $sejour->_count_tasks_not_created}}
            <img src="images/icons/phone_red.png"
                 onclick="Soins.showTasksNotCreated(this, 'tooltip-content-tasks-not-created-{{$sejour->_id}}', '{{$sejour->_id}}');"
                 onmouseover="this.style.cursor='pointer';"/>
            {{mb_include module=system template=inc_vw_counter_tip count=$sejour->_count_tasks_not_created}}
            <div id="tooltip-content-tasks-not-created-{{$sejour->_id}}"
                 style="display: none; height: 400px; width: 400px:"></div>
        {{/if}}
    </td>
{{/if}}

<td style="text-align: center;">
    {{if $dossier_medical && $dossier_medical->_id && $dossier_medical->_count_allergies}}
        {{me_img src="warning.png" icon="warning" class="me-warning" onmouseover="ObjectTooltip.createEx(this, '`$sejour->_ref_patient->_guid`', 'allergies');"}}
        {{mb_include module=system template=inc_vw_counter_tip count=$dossier_medical->_count_allergies}}
    {{elseif $dossier_medical && $dossier_medical->_ref_allergies|is_countable && $dossier_medical->_ref_allergies|@count}}
        <span class="texticon texticon-allergies-ok"
              title="{{tr}}CAntecedent-No known allergy-desc{{/tr}}">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>
    {{/if}}
</td>
<td style="text-align: center;">
    {{if $dossier_medical && $dossier_medical->_count_antecedents}}
        {{if $sejour->_ref_dossier_medical && $sejour->_ref_dossier_medical->_id}}
            {{assign var=dossier_medical value=$sejour->_ref_dossier_medical}}
        {{/if}}
        <span class="texticon texticon-atcd"
              onmouseover="ObjectTooltip.createEx(this, '{{$dossier_medical->_guid}}', 'antecedents');">Atcd</span>
    {{/if}}
</td>
<td>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');"
    {{if $sejour->entree|date_format:$conf.date == $date|date_format:$conf.date && $board}}
    class="circled" style="background-color: #dfd"
    {{/if}}>
    {{mb_value object=$sejour field=entree format=$conf.date}}
  </span>
    {{if $sejour->_duree && $board}}
        <br/>
        <span {{if $sejour->sortie|date_format:$conf.date == $date|date_format:$conf.date}}
    class="compact circled" style="background-color: #dfd"
    {{else}}
    class="compact"
    {{/if}}>
    &rarr; {{mb_value object=$sejour field=sortie format="%d/%m"}} ({{mb_value object=$sejour field=_duree}}j)
    {{if $sejour->confirme}}
        <span title="Sortie autorisée">
        {{me_img src="tick.png" alt_tr="CSejour-confirme" icon="tick" class="me-success"}}
      </span>
    {{/if}}
  </span>
    {{/if}}

    {{if !$board}}
        <div style="position: relative">
            <div class="ecap-sejour-bar"
                 title="arrivée il y a {{$sejour->_entree_relative}}j et départ prévu dans {{$sejour->_sortie_relative}}j ({{mb_value object=$sejour field=sortie}})">
                {{assign var=progress_bar_width value=0}}
                {{if $sejour->_duree}}
                    {{math assign=progress_bar_width equation='100*(-entree / (duree))' entree=$sejour->_entree_relative duree=$sejour->_duree format='%.2f'}}
                {{/if}}

                <div
                  style="width: {{if $sejour->_duree && $progress_bar_width <= 100}}{{$progress_bar_width}}{{else}}100{{/if}}%;"></div>
            </div>
        </div>
    {{/if}}
</td>
<td class="text">
    {{mb_include module=soins template=inc_cell_motif_sejour}}
</td>
<td>
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien initials=border}}
</td>
{{if !$lite_view}}
    <td class="text compact">
        {{if "soins Observations manual_alerts"|gconf}}
            {{mb_include module=system template=inc_icon_alerts object=$sejour tag=observation callback="function() { refreshLineSejour('`$sejour->_id`')}" show_empty=1 show_span=1 event=onmouseover img_ampoule="ampoule_rose"}}
        {{/if}}
        {{foreach from=$sejour->_ref_transmissions item=_transmission}}
            <div onmouseover="ObjectTooltip.createEx(this, '{{$_transmission->_guid}}')">
                <strong>{{$_transmission->type|substr:0:1|upper}}</strong>:{{$_transmission->text}}
            </div>
        {{/foreach}}
        {{if "transport"|module_active}}
            <div>
                {{mb_include module=transport template=inc_buttons_transport object=$sejour}}
            </div>
        {{/if}}
    </td>
{{/if}}
