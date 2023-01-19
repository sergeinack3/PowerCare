{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=tab_to_update value="tab-hospitalisation"}}
{{mb_default var=getSourceLabo value=false}}

<script>
    Main.add(function () {
        if ($("{{$tab_to_update}}")) {
            Control.Tabs.setTabCount("{{$tab_to_update}}", {{$sejours|@count}});
        }
    });
</script>

{{mb_default var=first value=true}}
{{mb_default var=board value=false}}
{{mb_default var=allow_edit_cleanup value=1}}

<table class="main tbl me-no-align me-small">
    <tbody>
    {{if $print}}
        <tr class="not-printable">
            <td style="vertical-align: middle;" class="button">
                <form name="addInputArea" method="get" style="clear:both;float: left;">
                    <label><input type="checkbox" name="show_textarea"
                                  onclick="$$('tbody.input_area').invoke('toggle')"/></label>
                    <strong>Afficher zone de saisie </strong>
                </form>
            </td>
            <td>
                <div style="clear:both;">
                    <a class="button print" onclick="window.print();">{{tr}}Print{{/tr}}</a>
                </div>
            </td>
        </tr>
    {{/if}}

    {{foreach from=$sejours item=sejour}}
        {{if $print}}
            {{mb_include module=soins template=inc_vw_print_sejour}}

        {{else}}
            <tr id="line_sejour_{{$sejour->_id}}"
                {{if $sejour->_ref_curr_affectation->_in_permission}}class="opacity-50"{{/if}}>
                {{mb_include module=soins template=inc_vw_sejour}}
            </tr>
        {{/if}}

        {{foreachelse}}
        <tr>
            <td colspan="16" class="empty">
                {{tr}}CSejour.none{{/tr}}
            </td>
        </tr>
    {{/foreach}}
    </tbody>

    <thead>
    <tr>
        <th class="title" colspan="16" {{if $print}}onclick="window.print();"{{/if}}>
            {{if !$board && !$print}}
                {{assign var=buttons_list value=""}}
                {{if $service_id != "NP"}}
                    {{me_button label="CSejour-action-Leaf transmission|pl" icon=print onclick="Soins.feuilleTransmissions('`$service_id`')"}}
                {{/if}}

                {{if $first}}
                    {{me_button label=Print icon=print old_class=notext onclick="printSejours(\$V(getForm('changeDate').date));"}}
                {{/if}}
                {{me_dropdown_button button_icon=print button_label=Print
                container_class="me-dropdown-button-right" button_class="notext"
                container_style="float: right;" button_class="me-tertiary notext"}}
            {{/if}}
            {{if $board}}
                <button type="button" class="print notext" style="float: right;"
                        onclick="Soins.printDisplayedSejours(
                          '{{$date}}',
                          '{{$print_content_class}}',
                          '{{$print_content_id}}',
                          {{if $show_affectation}}1{{else}}0{{/if}},
                        {{if $only_non_checked}}1{{else}}0{{/if}}
                          )">
                    {{tr}}Print{{/tr}}
                </button>
                {{tr}}CSejour|pl{{/tr}}
            {{else}}
                {{if $service->_id && $service_id != "NP"}}
                    {{tr}}CService-Service stays{{/tr}} {{$service}}
                {{elseif $function->_id}}
                    {{tr}}CFunctions-Office stays{{/tr}} {{$function}}
                {{elseif $discipline->_id}}
                    {{tr}}CDiscipline-Speciality stays{{/tr}} {{$discipline}}
                {{elseif $praticien->_id}}
                    {{tr}}CMediusers-Stays of the practitioner{{/tr}} {{$praticien}}
                {{elseif !$service->_id && $service_id != "NP"}}
                    {{tr}}CMediusers-Stays of the patients{{/tr}}
                {{else}}
                    {{tr}}CPatient-Patient not placed|pl{{/tr}}
                {{/if}}
                ({{$sejours|@count}})
                le {{$date|date_format:$conf.longdate}}
                <form name="changeDate" method="get" onsubmit="return false;">
                    <input type="hidden" name="date" class="date" value="{{$date}}"
                           onchange="$V(getForm('TypeHospi').date, $V(getForm('changeDate').date));"/>
                </form>
            {{/if}}

            {{if $print}}
                <span style="font-weight: normal;"> - {{$dtnow|date_format:$conf.datetime}}</span>
            {{/if}}
            {{if !"soins Sejour select_services_ids"|gconf && $service_id && $service_id != "NP" && (@$modules.dPplanningOp->_can->admin || ("soins UserSejour can_edit_user_sejour"|gconf && @$modules.dPplanningOp->_can->edit))}}
                {{assign var=responsable_jour value='Ox\Mediboard\Hospi\CAffectationUserService::loadResponsableJour'|static_call:$service_id:$date}}
                <button type="button" class="mediuser_black notext"
                        onclick="Soins.reponsableJour('{{$date}}', '{{$service_id}}', 'TypeHospi');"
                        style="margin-left: 10px;{{if !$responsable_jour->_id}}opacity: 0.6;{{/if}}"
                        onmouseover="ObjectTooltip.createDOM(this, 'responsable_jour-{{$date}}-{{$service_id}}');"></button>
                {{if $responsable_jour->_id}}
                    <span class="countertip" style="margin-top:1px;"><span>1</span></span>
                {{/if}}
                <div style="display: none" id="responsable_jour-{{$date}}-{{$service_id}}"
                     class="{{if !$responsable_jour->_id}}empty{{/if}}">
                    {{if $responsable_jour->_id}}
                        {{tr}}CAffectationUserService.day{{/tr}}: {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$responsable_jour->_ref_user}}
                    {{else}}
                        {{tr}}CUserSejour.none_responsable{{/tr}}
                    {{/if}}
                </div>
            {{/if}}
        </th>
    </tr>

    {{if !$print}}
        <tr>
            {{if $service->_id || $function->_id || $praticien->_id || $discipline->_id || $show_affectation}}
                <th rowspan="2" style="width: 7%;">{{mb_title class=CLit field=nom}}</th>
            {{/if}}

            {{if "hotellerie"|module_active && $allow_edit_cleanup}}
                <th rowspan="2" class="narrow">{{tr}}CBedCleanup-Cleaning{{/tr}}</th>
            {{/if}}
            <th colspan="2" rowspan="2" style="width: 20%">
                <input type="text" size="3" onkeyup="Soins.filterFullName(this);" id="filter-patient-name"
                       style="float: right;"/>
                {{mb_title class=CPatient field=nom}}
                <br/>
                ({{mb_title class=CPatient field=nom_jeune_fille}})
            </th>
            {{if "dPImeds"|module_active}}
                <th rowspan="2">Labo</th>
            {{/if}}
            {{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
              <th rowspan="2">{{tr}}Ox Labo{{/tr}}</th>
            {{/if}}
            <th colspan="{{if $board}}2{{elseif "dPprescription"|module_active}}5{{else}}3{{/if}}" class="narrow">
                Alertes
            </th>
            <th rowspan="2" class="narrow">
                {{mb_title class=CSejour field=entree}}
                <br/>
                {{mb_title class=CSejour field=sortie}}
            </th>
            <th rowspan="2" style="width: 25%">{{mb_title class=CSejour field=libelle}}</th>
            <th rowspan="2" class="narrow">{{tr}}CPrescription-praticien{{/tr}}</th>
            {{if !$board}}
                <th rowspan="2">{{tr}}CSejour-msg-Care Project Special requests{{/tr}}</th>
            {{/if}}
        </tr>
        <tr>
            {{if !$board}}
                {{if "dPprescription"|module_active}}
                    <th><label title="Modification de prescriptions">Presc.</label></th>
                    <th><label
                          title="Prescriptions urgentes">{{tr}}CPoseDispositifVasculaire-urgence-court{{/tr}}</label>
                    </th>
                {{/if}}
                <th>{{tr}}CSejour-attentes{{/tr}}</th>
            {{/if}}
            <th>{{tr}}CAntecedent-Allergie|pl{{/tr}}</th>
            <th><label title="{{tr}}CAntecedent.more{{/tr}}">{{tr}}CAntecedent-court{{/tr}}</label></th>
        </tr>
    {{/if}}
    </thead>
</table>
