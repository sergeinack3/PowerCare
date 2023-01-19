{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr class="me-lit {{if !"dPhospi CLit show_in_tableau"|gconf}}lit{{/if}}" id="lit-{{$curr_lit->_id}}">
    <td>

        {{if $curr_lit->_overbooking}}
            {{me_img src="warning.png" alt=warning title="Over-booking: `$curr_lit->_overbooking` collisions" icon="warning" class="me-warning"}}
        {{/if}}

        {{if "hotellerie"|module_active && $curr_lit->_ref_last_cleanup->_id}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_lit->_ref_last_cleanup->_guid}}')"
                  style="float: right;">
        <i class="fa fa-bed fa-lg" style="color: {{$curr_lit->_ref_last_cleanup->_color_status}};"></i>
      </span>
        {{else}}
            <span style="float: right;" onmouseover="ObjectTooltip.createEx(this, '{{$curr_lit->_guid}}')">
        <i class="fa fa-bed fa-lg" style="color: {{if $curr_lit->_ref_affectations}}black{{else}}#909090{{/if}};"></i>
      </span>
        {{/if}}
        <span
          onmouseover="ObjectTooltip.createDOM(this, $('dispo_lit_{{$curr_lit->_guid}}').clone(true));">{{$curr_lit->_shortview}}</span>

    </td>
    <td class="action">
        {{if $can->edit}}
            <input name="choixLit" type="radio" id="lit{{$curr_lit->_id}}" onclick="selectLit({{$curr_lit->_id}})"/>
        {{/if}}
    </td>
</tr>
<tr class="me-lit-infos">
    <td colspan="2" style="padding: 0;" class="me-padding-0">
        {{foreach name=affectations from=$curr_lit->_ref_affectations item=curr_affectation}}
            {{assign var="sejour" value=$curr_affectation->_ref_sejour}}
            {{assign var="patient" value=$sejour->_ref_patient}}
            {{assign var="aff_prev" value=$curr_affectation->_ref_prev}}
            {{assign var="aff_next" value=$curr_affectation->_ref_next}}
            {{assign var=in_permission value=$curr_affectation->_in_permission}}
            <form name="addAffectationaffectation_{{$curr_affectation->_id}}" action="?m={{$m}}" method="post"
                  class="prepared">
                <input type="hidden" name="m" value="dPhospi"/>
                <input type="hidden" name="dosql" value="do_affectation_aed"/>
                <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
                <input type="hidden" name="lit_id" value=""/>
                <input type="hidden" name="sejour_id" value="{{$sejour->_id}}"/>
                <input type="hidden" name="entree" value="{{$curr_affectation->entree}}"/>
                <input type="hidden" name="sortie" value="{{$curr_affectation->sortie}}"/>
            </form>
            <table id="affectation_{{$curr_affectation->_id}}"
                   class="tbl me-no-border-radius me-no-align me-no-hover-discret
             me-table-hover-on-dark {{if $in_permission}}opacity-50{{/if}}
             {{if $smarty.foreach.affectations.last}}me-no-box-shadow{{/if}}">
                <tr class="patient">
                    {{if $curr_affectation->sejour_id}}
                        <td class="text button" style="width: 1%;">
                            {{if $can->edit}}
                                <script>new Draggable("affectation_{{$curr_affectation->_id}}", {revert: true});</script>
                            {{/if}}
                            <!--
            <a href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
              <img src="images/icons/planning.png" title="Modifier le séjour">
            </a>
            -->
                            {{if $sejour->_couvert_c2s}}
                                <div><strong>C2S</strong></div>
                            {{/if}}
                            {{if $sejour->_couvert_ald}}
                                <div><strong {{if $sejour->ald}}style="color: red;"{{/if}}>ALD</strong></div>
                            {{/if}}

                            {{mb_include module=hospi template=inc_vw_icones_sejour}}
                        </td>
                        {{if $sejour->confirme}}
                            <td class="text"
                                style="background-image:url(images/icons/ray.gif); background-repeat:repeat;">
                                {{else}}
                            <td class="text">
                        {{/if}}
                    {{if !$sejour->entree_reelle || ($aff_prev->_id && $aff_prev->effectue == 0)}}
                        <span class="patient-not-arrived">
                    {{elseif $sejour->septique}}
                        <span class="septique">
                    {{else}}
                        <span>
                    {{/if}}
                        {{if "dPImeds"|module_active && "dPhospi vue_tableau show_labo_results"|gconf}}
                            {{mb_include module=Imeds template=inc_sejour_labo link="#1"}}
                            <script>
              ImedsResultsWatcher.addSejour('{{$sejour->_id}}', '{{$sejour->_NDA}}');
            </script>
                        {{/if}}
                        <span style="float: right;">
            {{if $prestation_id && $sejour->_liaisons_for_prestation|@count}}
                {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$sejour->_liaisons_for_prestation}}
            {{/if}}
                            {{if $prestation_id && $sejour->_liaisons_for_prestation_ponct|@count}}
                                {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$sejour->_liaisons_for_prestation_ponct}}
                            {{/if}}
                            {{mb_include module=patients template=inc_vw_antecedents type=deficience readonly=1}}
          </span>
                        <span
                          {{if $app->touch_device}}onclick{{else}}onmouseover{{/if}}="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')"
                          {{if $sejour->recuse == "-1"}}class="opacity-70"{{/if}}>
            <strong {{if $sejour->type == "ambu"}}style="font-style: italic;"{{/if}}>
              {{if $sejour->recuse == "-1"}}[Att] {{/if}}
                {{if $in_permission}}
                    <span class="texticon">PERM.</span>
                    {{mb_include module=soins template=inc_button_permission affectation=$curr_affectation from_placement=1}}
                {{/if}}
                {{$patient}}
            </strong>
          </span>
                        {{if $sejour->circuit_ambu}}
                            <span class="texticon"
                                  title="{{tr}}CSejour-circuit_ambu{{/tr}} : {{tr}}CSejour.circuit_ambu.{{$sejour->circuit_ambu}}{{/tr}}">
              {{mb_value object=$sejour field=circuit_ambu}}
            </span>
                        {{/if}}

                        {{if $sejour->prestation_id}}
                            {{mb_value object=$sejour field=prestation_id}}
                        {{/if}}

                        {{mb_include module=patients template=inc_icon_bmr_bhre}}

                        {{if (!$sejour->entree_reelle) || ($aff_prev->_id && $aff_prev->effectue == 0)}}
                            {{$curr_affectation->entree|date_format:"%d/%m %Hh%M"}}
                        {{else}}
                            {{$sejour->entree_reelle|date_format:"%d/%m %Hh%M"}}
                        {{/if}}
                        </span>
                        </td>
                        <td class="action" style="background:#{{$sejour->_ref_praticien->_ref_function->color}}"
                            onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_ref_praticien->_guid}}');">
                            {{$sejour->_ref_praticien->_shortview}}
                        </td>
                    {{else}}
                        {{if $curr_affectation->sortie > $dtnow || $mode == 1}}
                            <td colspan="2">
                                <strong><em>[LIT BLOQUE]</em></strong>
                            </td>
                        {{/if}}
                    {{/if}}
                </tr>
                {{if !$curr_affectation->sejour_id}}
                    {{if $curr_affectation->sortie < $dtnow && $mode == 0}}
                        <tr class="litdispo">
                            <td
                              colspan="2">{{tr}}Fin-blocage{{/tr}} {{$curr_affectation->sortie|date_format:"%A %d %B %Hh%M"}}</td>
                        </tr>
                    {{/if}}
                    <tr class="dates">
                        <td class="text" colspan="2">
                            {{if $can->edit}}
                                <form name="entreeAffectation{{$curr_affectation->_id}}" action="?m={{$m}}"
                                      method="post" style="float: right;"
                                      class="prepared">
                                    {{mb_class object=$curr_affectation}}
                                    {{mb_key   object=$curr_affectation}}
                                    <input type="hidden" name="entree" class="dateTime notNull"
                                           value="{{$curr_affectation->entree}}"
                                           onchange="this.form.submit()"/>
                                </form>
                            {{/if}}
                            <strong>Du</strong>:
                            {{$curr_affectation->entree|date_format:"%a %d %b %Hh%M"}}
                            ({{$curr_affectation->_entree_relative}}j)
                        </td>
                        <td class="action">
                            {{if $can->edit}}
                                <form name="rmvAffectation{{$curr_affectation->_id}}" action="?m={{$m}}" method="post"
                                      class="prepared">
                                    <input type="hidden" name="m" value="dPhospi"/>
                                    <input type="hidden" name="dosql" value="do_affectation_aed"/>
                                    <input type="hidden" name="del" value="1"/>
                                    <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
                                </form>
                                <a href="#"
                                   onclick="confirmDeletion(document.rmvAffectation{{$curr_affectation->_id}},{typeName:'l\'affectation',objName:'{{$patient->_view|smarty:nodefaults|JSAttribute}}'})">
                                    {{me_img src="trash.png" alt_tr="trash" title="Supprimer l'affectation" icon="trash" class="me-primary"}}
                                </a>
                            {{/if}}
                        </td>
                    </tr>
                    <tr class="dates">
                        <td class="text" colspan="2">
                            {{if $can->edit && (!$sejour->sortie_reelle || $aff_next->_id)}}
                                <form name="sortieAffectation{{$curr_affectation->_id}}" action="?m={{$m}}"
                                      method="post" style="float: right;"
                                      class="prepared">
                                    <input type="hidden" name="m" value="dPhospi"/>
                                    <input type="hidden" name="dosql" value="do_affectation_aed"/>
                                    <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
                                    <input type="hidden" name="sortie" class="dateTime notNull"
                                           value="{{$curr_affectation->sortie}}"
                                           onchange="this.form.submit()"/>
                                </form>
                            {{/if}}
                            <strong>Au</strong>:
                            {{$curr_affectation->sortie|date_format:"%a %d %b %Hh%M"}}
                            ({{$curr_affectation->_sortie_relative}}j)
                        </td>
                        <td class="action">
                        </td>
                    </tr>
                    {{if $curr_affectation->rques}}
                        <tr class="dates">
                            <td class="text highlight" colspan="3">
                                <strong>Remarques:</strong> {{$curr_affectation->rques|nl2br}}
                            </td>
                        </tr>
                    {{/if}}
                {{else}}
                    <tr class="dates">
                        <td class="text" colspan="2">
                            {{if $can->edit && (!$sejour->entree_reelle || $aff_prev->_id)}}
                                <form name="entreeAffectation{{$curr_affectation->_id}}" action="?m={{$m}}"
                                      method="post" style="float: right;"
                                      class="prepared">
                                    <input type="hidden" name="m" value="dPhospi"/>
                                    <input type="hidden" name="dosql" value="do_affectation_aed"/>
                                    <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
                                    <input type="hidden" name="entree" class="dateTime notNull"
                                           value="{{$curr_affectation->entree}}"
                                           onchange="return onSubmitFormAjax(this.form, {onComplete: reloadTableau});"/>
                                </form>
                            {{/if}}

                            {{if $curr_service->externe}}
                                <strong>Départ</strong>
                                {{if $aff_prev->_id}}
                                    ({{$aff_prev->_ref_lit->_ref_chambre->_shortview}})
                                {{/if}}
                            {{elseif $aff_prev->_id}}
                                <strong>Déplacé</strong>
                                ({{if $aff_prev->lit_id}}{{$aff_prev->_ref_lit->_ref_chambre->_shortview}}{{else}}{{$aff_prev->_ref_service}}{{/if}})
                            {{else}}
                                <strong>Entrée</strong>
                            {{/if}}
                            :
                            {{$curr_affectation->entree|date_format:"%a %d %b %Hh%M"}}
                            ({{$curr_affectation->_entree_relative}}j)
                        </td>
                        <td class="action">
                            {{if $can->edit}}
                                <form name="rmvAffectation{{$curr_affectation->_id}}" action="?m={{$m}}" method="post">
                                    <input type="hidden" name="m" value="dPhospi"/>
                                    <input type="hidden" name="dosql" value="do_affectation_aed"/>
                                    <input type="hidden" name="del" value="1"/>
                                    <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
                                </form>
                                <a href="#"
                                   onclick="confirmDeletion(document.rmvAffectation{{$curr_affectation->_id}},{typeName:'l\'affectation',objName:'{{$patient->_view|smarty:nodefaults|JSAttribute}}'})">
                                    {{me_img src="trash.png" alt_tr="trash" title="Supprimer l'affectation" icon="trash" class="me-primary"}}
                                </a>
                            {{/if}}
                        </td>
                    </tr>
                    <tr class="dates">
                        <td class="text" colspan="2">
                            {{if $can->edit && (!$sejour->sortie_reelle || $aff_next->_id)}}
                                <form name="sortieAffectation{{$curr_affectation->_id}}" action="?m={{$m}}"
                                      method="post" style="float: right;"
                                      class="prepared">
                                    <input type="hidden" name="m" value="dPhospi"/>
                                    <input type="hidden" name="dosql" value="do_affectation_aed"/>
                                    <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
                                    <input type="hidden" name="sortie" class="dateTime notNull"
                                           value="{{$curr_affectation->sortie}}"
                                           onchange="return onSubmitFormAjax(this.form, {onComplete: reloadTableau});"/>
                                </form>
                            {{/if}}

                            {{if $curr_service->externe}}
                                <strong>Retour</strong>
                                {{if $aff_next->_id}}
                                    ({{$aff_next->_ref_lit->_ref_chambre->_shortview}})
                                {{/if}}
                            {{elseif $aff_next->_id}}
                                <strong>Déplacé</strong>
                                ({{if $aff_next->lit_id}}{{$aff_next->_ref_lit->_ref_chambre->_shortview}}{{else}}{{$aff_next->_ref_service}}{{/if}})
                            {{else}}
                                <strong>Sortie</strong>
                            {{/if}}
                            :
                            {{$curr_affectation->sortie|date_format:"%a %d %b %Hh%M"}}
                            ({{$curr_affectation->_sortie_relative}}j)
                        </td>
                        <td class="action">
                            {{if $can->edit && !$aff_next->_id}}
                                <form name="splitAffectation{{$curr_affectation->_id}}" action="?m={{$m}}" method="post"
                                      class="prepared">
                                    <input type="hidden" name="m" value="dPhospi"/>
                                    <input type="hidden" name="dosql" value="do_affectation_split"/>
                                    <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
                                    <input type="hidden" name="sejour_id" value="{{$curr_affectation->sejour_id}}"/>
                                    <input type="hidden" name="entree" value="{{$curr_affectation->entree}}"/>
                                    <input type="hidden" name="sortie" value="{{$curr_affectation->sortie}}"/>
                                    <input type="hidden" name="no_synchro" value="1"/>
                                    <input type="hidden" name="_new_lit_id" value=""/>
                                    <span style="float: right;" class="me-float-none">
            <input type="hidden" name="_date_split" class="dateTime notNull" value="{{$curr_affectation->sortie}}"
                   onchange="submitAffectationSplit(this.form)"/>
          </span>
                                </form>
                            {{/if}}
                        </td>
                    </tr>
                    <tr class="dates">
                        <td colspan="3"><strong>Age</strong>: {{$patient->_age}}
                            ({{mb_value object=$patient field=naissance}})
                            {{if "dPhospi General show_uf"|gconf}}
                                <a style="float: right;" href="#1"
                                   onclick="AffectationUf.affecter('{{$curr_affectation->_id}}','{{$curr_lit->_id}}')">
                                    <span class="texticon texticon-uf" title="Affecter les UF">UF</span>
                                </a>
                            {{/if}}
                        </td>
                    </tr>
                    {{if $sejour->prestation_id}}
                        <tr class="dates">
                            <td colspan="3">
                                <strong>Prestation:</strong> {{$sejour->_ref_prestation->_view}}
                            </td>
                        </tr>
                    {{/if}}
                    <tr class="dates">
                        <td class="text" colspan="3">
                            <strong>
                                {{if $curr_affectation->praticien_id}}
                                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_affectation->_ref_praticien}}
                                    {{if $sejour->praticien_id != $curr_affectation->praticien_id}}
                                        ({{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien initials="border"}})
                                    {{/if}}
                                {{else}}
                                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
                                {{/if}}
                            </strong>
                        </td>
                    </tr>
                    {{if $sejour->libelle}}
                        <tr class="dates">
                            <td class="text" colspan="3">
                                {{$sejour->libelle}}
                            </td>
                        </tr>
                    {{/if}}
                    <tr class="dates">
                        <td class="text" colspan="3">
                            {{foreach from=$sejour->_ref_operations item=_operation}}
                                {{mb_include module=planningOp template=inc_vw_operation operation=$_operation}}
                            {{/foreach}}
                        </td>
                    </tr>
                    <tr class="dates">
                        <td class="text" colspan="3">
                            <form name="SeptieSejour{{$sejour->_id}}" method="post" class="prepared">
                                <input type="hidden" name="m" value="planningOp"/>
                                <input type="hidden" name="dosql" value="do_sejour_aed"/>
                                <input type="hidden" name="postRedirect" value="m=hospi"/>
                                {{mb_key object=$sejour}}

                                <strong>Pathologie</strong>:
                                {{$sejour->pathologie}}
                                -
                                {{if $can->edit}}
                                    <label title="Séjour propre" class="me-small-fields">
                                        <input type="radio" name="septique" value="0"
                                               {{if $sejour->septique == 0}}checked{{/if}}
                                               onclick="this.form.submit()"/>
                                        Propre
                                    </label>
                                    <label title="Séjour septique" class="me-small-fields">
                                        <input type="radio" name="septique" value="1"
                                               {{if $sejour->septique == 1}}checked{{/if}}
                                               onclick="this.form.submit()"/>
                                        Septique
                                    </label>
                                {{else}}
                                    {{if $sejour->septique == 0}}
                                        Propre
                                    {{else}}
                                        Septique
                                    {{/if}}
                                {{/if}}
                            </form>
                        </td>
                    </tr>
                    {{if $sejour->rques != ""}}
                        <tr class="dates">
                            <td class="text highlight" colspan="3">
                                <strong>Séjour</strong>: {{$sejour->rques|nl2br}}
                            </td>
                        </tr>
                    {{/if}}
                    {{foreach from=$sejour->_ref_operations item=curr_operation}}
                        {{if $curr_operation->rques != ""}}
                            <tr class="dates">
                                <td class="text highlight" colspan="3">
                                    <strong>Intervention</strong>: {{$curr_operation->rques|nl2br}}
                                </td>
                            </tr>
                        {{/if}}
                    {{/foreach}}
                    {{if $patient->rques != ""}}
                        <tr class="dates">
                            <td class="text highlight" colspan="3">
                                <strong>Patient</strong>: {{$patient->rques|nl2br}}
                            </td>
                        </tr>
                    {{/if}}
                    <tr class="dates">
                        <td class="text" colspan="3">
                            {{mb_include module=admissions template=inc_form_prestations edit=$can->edit}}
                        </td>
                    </tr>
                {{/if}}
            </table>
            {{foreachelse}}
            <div id="dispo_lit_{{$curr_lit->_guid}}">
                {{if $curr_service->externe}}
                    <table class="tbl me-no-box-shadow me-no-align">
                        <tr class="litdispo">
                            <td colspan="2">Aucun patient</td>
                        </tr>
                    </table>
                {{else}}
                    <table class="tbl me-no-box-shadow me-no-align">
                        <tr class="litdispo">
                            <td colspan="2">Lit disponible</td>
                        </tr>
                        <tr class="litdispo">
                            <td class="text" colspan="2">
                                depuis:
                                {{if $curr_lit->_ref_last_dispo && $curr_lit->_ref_last_dispo->_id}}
                                    {{$curr_lit->_ref_last_dispo->sortie|date_format:"%A %d %B %Hh%M"}}
                                    ({{$curr_lit->_ref_last_dispo->_sortie_relative}} jours)
                                {{else}}
                                    Toujours
                                {{/if}}
                            </td>
                        </tr>
                        <tr class="litdispo">
                            <td class="text" colspan="2">
                                jusque:
                                {{if $curr_lit->_ref_next_dispo && $curr_lit->_ref_next_dispo->_id}}
                                    {{$curr_lit->_ref_next_dispo->entree|date_format:"%A %d %B %Hh%M"}}
                                    ({{$curr_lit->_ref_next_dispo->_entree_relative}} jours)
                                {{else}}
                                    Toujours
                                {{/if}}
                            </td>
                        </tr>
                    </table>
                {{/if}}
            </div>
        {{/foreach}}
    </td>
</tr>
