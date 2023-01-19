{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=pmsi script=PMSI register=true}}
{{mb_script module=files script=file ajax=true}}

<!-- Onglet dossiers en cours volet Séjour -->
{{mb_include module=system template=inc_pagination total=$count current=$page change_page="PMSI.changePageHospi" step=$step}}
<table class="tbl">
    <tr>
        <th class="title" colspan="11">
            Liste des {{$count}} personne(s) hospitalisée(s) du {{$date_min|date_format:$conf.longdate}}
            au {{$date_max|date_format:$conf.longdate}}
        </th>
    </tr>
    <tr>
        {{if "atih"|module_active}}
            <th>{{mb_title class=CTraitementDossier field=traitement}}</th>
            <th>{{mb_title class=CTraitementDossier field=validate}}</th>
        {{/if}}
        <th>{{mb_title class=CSejour field=facture}}</th>
        <th>{{mb_title class=CSejour field=_NDA}}</th>
        <th>{{mb_label class=CSejour field=praticien_id}}</th>
        <th>{{mb_label class=CSejour field=patient_id}}</th>
        <th>
            {{mb_title class=CSejour field=entree}}
            {{mb_title class=CSejour field=sortie}}
        </th>
        <th>DP</th>
        <th>Actes</th>
        {{if "atih"|module_active && "atih CGroupage use_fg"|gconf}}
            <th class="narrow">
                <label title="{{tr}}CGroupage-Launch the grouping for all stay|pl-desc{{/tr}}">
                    <input type="checkbox" onclick="PMSI.selectAll_groupage(this.checked);"/>
                    {{tr}}CGroupage-Grouping function{{/tr}}
                </label>
            </th>
        {{/if}}
        <th class="narrow">{{tr}}common-Uread{{/tr}}</th>
    </tr>
    {{foreach from=$listSejours item=_sejour}}
        <tr>
            {{if "atih"|module_active}}
                <td class="text {{if $_sejour->_count_actes < 1}}empty{{/if}}">
                    {{if $_sejour->_ref_traitement_dossier->traitement}}
                        {{me_img src="tick.png" icon="tick" class="me-success" alt="Dossier traité par le PMSI"}}
                    {{else}}
                        {{me_img src="cross.png" icon="cancel" class="me-error" alt="Dossier non traité par le PMSI"}}
                    {{/if}}
                </td>
                <td class="text {{if $_sejour->_count_actes < 1}}empty{{/if}}">
                    {{if $_sejour->_ref_traitement_dossier->validate}}
                        <span>
              {{me_img src="tick.png" icon="tick" class="me-success" alt="Dossier validé par le PMSI"}}
            </span>
                    {{else}}
                        <span>
            {{me_img src="cross.png" icon="cancel" class="me-error" alt="Dossier non validé par le PMSI"}}
          </span>
                    {{/if}}
                </td>
            {{/if}}

            <td {{if !$_sejour->facture}}class="empty"{{/if}}>
                {{if $_sejour->facture}}
                    {{me_img src="tick.png" icon="tick" class="me-success" alt_tr="Ok"}}
                {{else}}
                    {{me_img src="cross.png" icon="cancel" class="me-error" alt="alerte"}}
                {{/if}}
            </td>
            <td class="text">
                <strong onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
                    {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour}}
                </strong>
            </td>

            <td class="text">
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
            </td>

            <td class="text">
                {{assign var=patient value=$_sejour->_ref_patient}}
                <a href="?m=pmsi&tab=vw_dossier_pmsi&patient_id={{$patient->_id}}&sejour_id={{$_sejour->_id}}">
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
                    {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
                      {{$patient}}
                  </span>
                </a>
            </td>

            <td>
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
                    {{mb_include module=system template=inc_interval_datetime from=$_sejour->entree to=$_sejour->sortie}}
                  </span>
                {{if "dmp"|module_active && $_sejour->_ref_patient->_ref_last_id400->id400 == 4}}
                    <span style="float: right"
                          class="dmp-exist">DMP {{if $_sejour->_count_dmp_docs}}({{$_sejour->_count_dmp_docs}}/{{$_sejour->_nb_files_docs}}){{/if}}</span>
                {{/if}}
            </td>

            <td class="text {{if !$_sejour->DP}}empty{{/if}}">
                {{if !$_sejour->DP}}
                    {{me_img src="cross.png" icon="cancel" class="me-error" alt="alerte"}}
                    Aucun DP
                {{else}}
                    {{me_img src="tick.png" icon="tick" class="me-success" alt_tr="Ok"}}
                    {{$_sejour->DP}}
                {{/if}}
            </td>

            <td class="text {{if $_sejour->_count_actes < 1}}empty{{/if}}">
                {{if $_sejour->_count_actes > 0}}
                    {{me_img src="tick.png" icon="tick" class="me-success" alt="`$_sejour->_count_actes` actes sur le séjour"}}
                    {{$_sejour->_count_actes}} actes
                {{else}}
                    {{me_img src="cross.png" icon="cancel" class="me-error" alt="Aucun acte sur le séjour"}}
                    Aucun acte
                {{/if}}
            </td>

            {{if "atih"|module_active && "atih CGroupage use_fg"|gconf}}
                {{if !in_array($_sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$_sejour->praticien_id)}}
                  {{assign var=rss_id             value=$_sejour->_ref_rss->_id}}
                  {{assign var=traitement_dossier value=$_sejour->_ref_traitement_dossier}}
                  {{assign var=sejour_id          value=$_sejour->_id}}

                  {{if $traitement_dossier && $traitement_dossier->_id}}
                      <td>
                        <span onmouseover="ObjectTooltip.createDOM(this, 'tooltip-infos-groupage-ok-{{$rss_id}}');">
                          {{me_img src="tick.png" icon="tick" class="me-success" alt_tr="CGroupage-Valid grouping"}} {{tr}}CGroupage-Grouping validated{{/tr}}
                        </span>

                          <div id="tooltip-infos-groupage-ok-{{$rss_id}}" style="display: none; min-width: 600px;">
                              <table class="main">
                                  <tr>
                                      <td>
                                          {{mb_include module=atih template=inc_result_groupage  groupage=$groupages.$sejour_id}}
                                          {{mb_include module=atih template=inc_erreurs_groupage groupage=$groupages.$sejour_id}}
                                      </td>
                                  </tr>
                                  <tr>
                                      <td class="tag" colspan="2">
                                          {{if $traitement_dossier->validate}}
                                              {{tr}}CGroupage-Grouping validated{{/tr}} :
                                              <span>{{tr var1=$traitement_dossier->validate|date_format:$conf.datetime}}common-the %s{{/tr}}</span>
                                              <span>{{tr}}common-by{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$traitement_dossier->_ref_dim}}</span>
                                          {{/if}}
                                      </td>
                                  </tr>
                              </table>
                          </div>
                      </td>
                  {{else}}
                      <td id="groupage_{{$_sejour->_guid}}">
                          <label
                            title="{{if !$rss_id}}{{tr}}CGroupage-Grouping impossible without RSS file{{/tr}}{{else}}{{tr}}CGroupage-action-Launch the grouping{{/tr}}{{/if}}">
                              <input type="checkbox" name="FG" value="{{$_sejour->_id}}"
                                     onchange="PMSI.loadGroupageCurrentDossiers(this.value, 1);"/>
                              {{tr}}CGroupage-action-Launch the grouping{{/tr}}
                          </label>
                      </td>
                  {{/if}}
                {{else}}
                    <td>
                        <div class="small-warning">
                            {{tr}}CRSS-msg-Creation of the RSS file impossible because you are on urgence stay{{/tr}}
                        </div>
                    </td>
                {{/if}}
            {{/if}}
            <td>
                {{mb_include module=pmsi template=inc_counter_not_read context=$_sejour}}
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td class="empty" style="text-align: center;" colspan="11">
                Aucun patient hospitalisé
            </td>
        </tr>
    {{/foreach}}
</table>
