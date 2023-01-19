{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mod_hotellerie value="hotellerie"|module_active}}
{{if $mod_hotellerie}}
    {{mb_script module=hotellerie script=hotellerie ajax=1}}
{{/if}}

{{foreach from=$sejours item=_sejour}}
    {{assign var=patient value=$_sejour->_ref_patient}}
    {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
    {{assign var=affectation value=$_sejour->_ref_curr_affectation}}
    {{assign var=curr_affectation value=$_sejour->_ref_curr_affectation}}
    {{assign var=aff_next value=$_sejour->_ref_next_affectation}}
    {{assign var=prescription value=$_sejour->_ref_prescription_sejour}}
    <tr style="height: 38px;">
        <td class="">
            <script>
                PersonnelSejour.addSejourJson('{{$_sejour->_guid}}', '{{$service_id}}');
            </script>
            <input name="{{$_sejour->_guid}}" type="checkbox"
                   onchange="PersonnelSejour.showCheckSejours($V(this)); PersonnelSejour.changeSejourJson(this, '{{$_sejour->_guid}}', '{{$service_id}}');"/>
        </td>
        <td>
            {{if $affectation->_id && $affectation->lit_id}}
                {{$affectation->_ref_lit->_view}}
                {{if $mod_hotellerie}} - {{mb_include module=hotellerie template=inc_icon_cleanup sejour=$_sejour callback=false affectation=$affectation}}{{/if}}
            {{elseif $_sejour->_ref_next_affectation->_id && $_sejour->_ref_next_affectation->lit_id}}
                {{$_sejour->_ref_next_affectation->_ref_lit->_view}}
                {{if $mod_hotellerie}}
                    - {{mb_include module=hotellerie template=inc_icon_cleanup affectation=$_sejour->_ref_next_affectation sejour=$_sejour callback=false}}
                {{/if}}
            {{/if}}
        </td>
        <td>
      <span style="position: relative;">
        <button type="button" class="mediuser_black notext"
                onclick="Soins.paramUserSejour('{{$_sejour->_id}}', '{{$service_id}}', 'refresh_list_sejours', '{{$date}}');"
                style="{{if $_sejour->_ref_users_sejour|@count == 0}}opacity: 0.6;{{/if}}"></button>
        {{if $_sejour->_ref_users_sejour|@count}}
            <span class="countertip"
                  style="margin-top: -4px;margin-left: -9px;">{{$_sejour->_ref_users_sejour|@count}}</span>
        {{/if}}
      </span>
        </td>
        <td>
            {{if $_sejour->_ref_users_sejour|@count}}
                <ul>
                    {{foreach from=$_sejour->_ref_users_sejour item=_user_sejour}}
                        <li>
              <span class="mediuser" style="border-left-color: #{{$_user_sejour->_ref_user->_color}};"
                    onmouseover="ObjectTooltip.createDOM(this, 'horaires_user_sejour_{{$_user_sejour->_id}}');">
                {{$_user_sejour->_ref_user->_shortview}}
              </span>
                            <div style="display: none" id="horaires_user_sejour_{{$_user_sejour->_id}}">
                                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_sejour->_ref_user}}
                                :
                                <ul>
                                    {{foreach from=$_user_sejour->_affectations item=_affectation}}
                                        <li>
                                            Du {{$_affectation->debut|date_format:$conf.datetime}}
                                            au {{$_affectation->fin|date_format:$conf.datetime}}
                                        </li>
                                    {{/foreach}}
                                </ul>
                            </div>
                        </li>
                    {{/foreach}}
                </ul>
            {{/if}}
        </td>
        <td {{if $_sejour->sortie_reelle}}class="hatching"{{/if}}>
            {{mb_include module=patients template=inc_vw_photo_identite size=20 nodebug=true}}
        </td>
        <td {{if $_sejour->sortie_reelle}}class="hatching"{{/if}}>
            <a href="#{{$patient->_guid}}" style="display:inline-block"
               onclick="showDossierSoins('{{$_sejour->_id}}','{{$date}}');">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient->_view}}
        </span>
            </a>
            <button type="button" class="lookup notext me-tertiary" style="display:inline-block;float:right"
                    onclick="popEtatSejour('{{$_sejour->_id}}');">
                {{tr}}CSejour-Condition of stay{{/tr}}</button>
            <br/>
            {{mb_value object=$patient field=_age}} ({{mb_value object=$patient field=naissance}})
        </td>
        <td style="text-align: center;">
            {{mb_include module=hospi template=inc_vw_icones_sejour sejour=$_sejour demain=$date_after}}
        </td>
        <td class="text">
            {{mb_include module=soins template=inc_cell_motif_sejour sejour=$_sejour}}
        </td>
        <td style="text-align: center;">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
        <strong
          style="font-size: 1.1em;">{{mb_value object=$_sejour field=entree_reelle format=$conf.time}}</strong><br/>
        {{mb_value object=$_sejour field=entree format=$conf.date}}
      </span>

            <div style="position: relative">
                <div class="ecap-sejour-bar"
                     title="arrivée il y a {{$_sejour->_entree_relative}}j et départ prévu dans {{$_sejour->_sortie_relative}}j ({{mb_value
                     object=$_sejour field=sortie}})">
                    {{assign var=progress_bar_width value=0}}
                    {{if $_sejour->_duree}}
                        {{math assign=progress_bar_width equation='100*(-entree / (duree))'
                        entree=$_sejour->_entree_relative duree=$_sejour->_duree format='%.2f'}}
                    {{/if}}
                    <div
                      style="width: {{if $_sejour->_duree && $progress_bar_width <= 100}}{{$progress_bar_width}}{{else}}100{{/if}}%;"></div>
                </div>
            </div>
        </td>
        <td>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien initials=border}}
            {{if $prescription->_id}}
                {{foreach from=$prescription->_jour_op item=_info_jour_op}}
                    {{assign var=anesth value=$_info_jour_op.operation->_ref_anesth}}
                    {{if $anesth->_id}}
                        <br/>
                        (
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$anesth->_guid}}')">
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$anesth initials=border}}
            </span>
                        )
                    {{/if}}
                {{/foreach}}
            {{/if}}
        </td>
        <td style="text-align: center;">
            {{if $dossier_medical && $dossier_medical->_count_antecedents}}
                {{if $_sejour->_ref_dossier_medical && $_sejour->_ref_dossier_medical->_id}}
                    {{assign var=dossier_medical value=$_sejour->_ref_dossier_medical}}
                {{/if}}
                <span>
          {{mb_include module=patients template=vw_antecedents_allergies sejour_id=$_sejour->_id}}
        </span>
            {{/if}}
        </td>
        {{if $isImedsInstalled}}
            <td>
                <div class="Imeds_button" onclick="showDossierSoins('{{$_sejour->_id}}', '{{$date}}', 'Imeds');">
                    {{mb_include module=Imeds template=inc_sejour_labo link="#" sejour=$_sejour}}
                </div>
            </td>
        {{/if}}
        <td style="text-align: center;">
            {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
                {{mb_include module=system template=inc_icon_alerts
                object=$prescription
                callback="function() { PersonnelSejour.refreshListeSoignant()}"
                nb_alerts=$prescription->_count_alertes}}
            {{else}}
                {{if $_sejour->_ref_prescription_sejour->_count_fast_recent_modif}}
                    <img src="images/icons/ampoule.png"
                         onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')"/>
                    {{mb_include module=system template=inc_vw_counter_tip count=$_sejour->_ref_prescription_sejour->_count_fast_recent_modif}}
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
                callback="function() { PersonnelSejour.refreshListeSoignant()}"
                nb_alerts=$prescription->_count_urgences
                level="high"}}
            {{/if}}
        </td>
        {{if "soins Observations manual_alerts"|gconf}}
            <td style="text-align: center;">
                {{mb_include module=system template=inc_icon_alerts object=$_sejour tag=observation show_span=1 event=onmouseover img_ampoule="ampoule_rose"}}
            </td>
        {{/if}}
        <td style="text-align: center;">
            {{if $_sejour->_count_tasks}}
                <img src="images/icons/phone_orange.png"
                     onclick="Soins.showTasks(this, 'tooltip-content-tasks-{{$_sejour->_id}}', '{{$_sejour->_id}}', 'responsable', 1);"
                     onmouseover="this.style.cursor='pointer';"/>
                {{mb_include module=system template=inc_vw_counter_tip count=$_sejour->_count_tasks}}
                <div id="tooltip-content-tasks-{{$_sejour->_id}}"
                     style="display: none; height: 400px; width: 400px:"></div>
            {{/if}}

            {{if $_sejour->_count_tasks_not_created}}
                <img src="images/icons/phone_red.png"
                     onclick="Soins.showTasksNotCreated(this, 'tooltip-content-tasks-not-created-{{$_sejour->_id}}', '{{$_sejour->_id}}');"
                     onmouseover="this.style.cursor='pointer';"/>
                {{mb_include module=system template=inc_vw_counter_tip count=$_sejour->_count_tasks_not_created}}
                <div id="tooltip-content-tasks-not-created-{{$_sejour->_id}}"
                     style="display: none; height: 400px; width: 400px:"></div>
            {{/if}}
        </td>
        <td>
            <button type="button" class="search" onclick="PersonnelSejour.showMacrocibles('{{$_sejour->_guid}}');"
                    onmouseover="ObjectTooltip.createEx(this,'{{$_sejour->_guid}}', 'macroSejour')">
                {{$_sejour->_ref_macrocibles|@count}} {{tr}}CCategoryPrescription-cible_importante|pl{{/tr}}
            </button>
        </td>

        {{if "soins UserSejour elts_colonne_regime"|gconf || "soins UserSejour elts_colonne_jeun"|gconf}}
            {{if $prescription->_ref_lines_jeun || $prescription->_ref_lines_regime}}
                <td>
                    {{if $prescription->_ref_lines_regime}}
                        <script>
                            Main.add(
                              function () {
                                  new Url('soins', 'vw_elts_regime_sejour')
                                    .addParam('object_guid', '{{$prescription->_guid}}')
                                    .requestUpdate("regime-{{$prescription->_guid}}");
                              }
                            )
                        </script>
                        <div id="regime-{{$prescription->_guid}}" style="display:none"></div>
                    {{strip}}
                        <i style="color:grey; font-size:20px" class="fa fa-utensils"
                           onmouseover="ObjectTooltip.createDOM(this, 'regime-{{$prescription->_guid}}')">
                        </i>
                    {{/strip}}
                    {{/if}}
                    {{if $prescription->_ref_lines_jeun}}
                        <script>
                            Main.add(
                              function () {
                                  new Url('soins', 'vw_elts_regime_sejour')
                                    .addParam('object_guid', '{{$prescription->_guid}}')
                                    .addParam('a_jeun', 1)
                                    .requestUpdate("jeun-{{$prescription->_guid}}");
                              }
                            )
                        </script>
                        <div id="jeun-{{$prescription->_guid}}" style="display:none"></div>
                    {{strip}}
                        <i style="color:red; font-size:20px" class="fas fa-ban"
                           onmouseover="ObjectTooltip.createDOM(this, 'jeun-{{$prescription->_guid}}')">
                        </i>
                    {{/strip}}
                    {{/if}}
                </td>
            {{else}}
                <td class="empty">
                    {{tr}}CPrescription.regime.sejour.none{{/tr}}
                </td>
            {{/if}}
            </td>
        {{/if}}
        <td style="text-align: center;">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
        <strong style="font-size: 1.1em;">{{mb_value object=$_sejour field=sortie format=$conf.time}}</strong><br/>
        {{mb_value object=$_sejour field=sortie format=$conf.date}}
      </span>
        </td>
    </tr>
    <tr>
        <td colspan="19" style="outline: 1px solid gray;outline-offset: -1px;padding:0;"></td>
    </tr>
    {{foreachelse}}
    <tr>
        <td colspan="19" class="empty">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
{{/foreach}}
