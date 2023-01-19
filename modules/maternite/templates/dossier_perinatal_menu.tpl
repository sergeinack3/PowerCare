{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{if $dossier->_listChapitres|is_array}}
    <table class="main layout">
        <tr>
            <td class="halfPane">
                <fieldset>
                    <legend
                      class="dossier-perinatal-font-size-legend">{{tr}}CDossierPerinat-debut_grossesse{{/tr}}</legend>
                    <ul>
                        {{foreach from=$dossier->_listChapitres.debut_grossesse item=chapitre key=nom_chapitre}}
                            <li
                              {{if $chapitre.dev != 'ko'}}onclick="DossierMater.openPage({{$grossesse->_id}}, '{{$nom_chapitre}}', '{{$operation_id}}');"{{/if}}>
                <span class="circled clickable"
                      style="background-color: {{$chapitre.color}}">
                  {{tr}}CDossierPerinat-debut_grossesse-{{$nom_chapitre}}{{/tr}}
                    {{if $chapitre.count != "NA"}}({{$chapitre.count}}){{/if}}
                </span>
                                {{if $chapitre.dev != "ok"}}
                                    <span class="circled opacity-30"
                                          style="background-color: {{if $chapitre.dev == "ok"}}lightgreen{{elseif $chapitre.dev == "ko"}}red{{else}}orange{{/if}};
                                            float: right;">
                  Dev : {{$chapitre.dev}}
                </span>
                                {{/if}}
                            </li>
                        {{/foreach}}
                    </ul>
                </fieldset>
            </td>
            <td>
                <fieldset>
                    <legend
                      class="dossier-perinatal-font-size-legend">{{tr}}CDossierPerinat-suivi_grossesse{{/tr}}</legend>
                    <ul>
                        {{foreach from=$dossier->_listChapitres.suivi_grossesse item=chapitre key=nom_chapitre}}
                            <li
                              {{if $chapitre.dev != 'ko'}}onclick="DossierMater.openPage({{$grossesse->_id}}, '{{$nom_chapitre}}', '{{$operation_id}}');"{{/if}}>
                <span class="circled {{if $chapitre.dev == 'ok'}}clickable{{/if}}"
                      style="background-color: {{$chapitre.color}}">
                  {{tr}}CDossierPerinat-suivi_grossesse-{{$nom_chapitre}}{{/tr}}
                    {{if $chapitre.count != "NA"}}({{$chapitre.count}}){{/if}}
                </span>
                                {{if $chapitre.dev != "ok"}}
                                    <span class="circled opacity-30"
                                          style="background-color: {{if $chapitre.dev == "ok"}}lightgreen{{elseif $chapitre.dev == "ko"}}red{{else}}orange{{/if}};
                                            float: right;">
                  Dev : {{$chapitre.dev}}
                </span>
                                {{/if}}
                            </li>
                        {{/foreach}}
                    </ul>
                </fieldset>
            </td>
        </tr>
        <tr>
            {{if array_key_exists('accouchement', $dossier->_listChapitres)}}
                <td>
                    <fieldset>
                        <legend
                          class="dossier-perinatal-font-size-legend">{{tr}}CDossierPerinat-accouchement{{/tr}}</legend>
                        {{if $dossier->niveau_alerte_cesar}}
                            <div
                              {{if $dossier->niveau_alerte_cesar == 1}}
                                  class="small-info" style="background-color: lightgreen"
                              {{elseif $dossier->niveau_alerte_cesar == 2}}
                                  class="small-warning"
                              {{elseif $dossier->niveau_alerte_cesar == 3}}
                                  class="small-error"
                              {{/if}}
                            >
                                {{if $dossier->conduite_a_tenir_acc}}
                                    {{mb_value object=$dossier field=conduite_a_tenir_acc}}
                                    <br/>
                                {{/if}}
                                {{mb_value object=$dossier field=niveau_alerte_cesar}}
                            </div>
                        {{/if}}
                        <ul>
                            {{foreach from=$dossier->_listChapitres.accouchement item=chapitre key=nom_chapitre}}
                                <li
                                  {{if $chapitre.dev != 'ko'}}onclick="DossierMater.openPage({{$grossesse->_id}}, '{{$nom_chapitre}}', '{{$operation_id}}');"{{/if}}>
                <span class="circled {{if $chapitre.dev != 'ko'}}clickable{{/if}}"
                      style="background-color: {{$chapitre.color}}">
                  {{tr}}CDossierPerinat-accouchement-{{$nom_chapitre}}{{/tr}}
                    {{if $chapitre.count != "NA"}}({{$chapitre.count}}){{/if}}
                </span>
                                    {{if $chapitre.dev != "ok"}}
                                        <span class="circled opacity-30"
                                              style="background-color: {{if $chapitre.dev == "ok"}}lightgreen{{elseif $chapitre.dev == "ko"}}red{{else}}orange{{/if}};
                                                float: right;">
                  Dev : {{$chapitre.dev}}
                </span>
                                    {{/if}}
                                </li>
                            {{/foreach}}
                        </ul>
                    </fieldset>
                </td>
            {{/if}}
            <td>
                <fieldset>
                    <legend
                      class="dossier-perinatal-font-size-legend">{{tr}}CDossierPerinat-suivi_accouchement{{/tr}}</legend>
                    <ul>
                        {{foreach from=$dossier->_listChapitres.suivi_accouchement item=chapitre key=nom_chapitre}}
                            <li
                              {{if $chapitre.dev != 'ko'}}onclick="DossierMater.openPage({{$grossesse->_id}}, '{{$nom_chapitre}}', '{{$operation_id}}');"{{/if}}>
                <span class="circled {{if $chapitre.dev != 'ko'}}clickable{{/if}}"
                      style="background-color: {{$chapitre.color}}">
                  {{tr}}CDossierPerinat-suivi_accouchement-{{$nom_chapitre}}{{/tr}}
                    {{if $chapitre.count != "NA"}}({{$chapitre.count}}){{/if}}
                </span>
                                {{if $chapitre.dev != "ok"}}
                                    <span class="circled opacity-30"
                                          style="background-color: {{if $chapitre.dev == "ok"}}lightgreen{{elseif $chapitre.dev == "ko"}}red{{else}}orange{{/if}};
                                            float: right;">
                  Dev : {{$chapitre.dev}}
                </span>
                                {{/if}}
                            </li>
                        {{/foreach}}
                    </ul>
                </fieldset>
            </td>
        </tr>
    </table>
{{else}}
    <div class="big-warning">Le dossier de périnatalité est désactivé</div>
{{/if}}
