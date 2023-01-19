{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=relance}}
{{mb_script module=facturation script=comptabilite}}

<script>
    function changeDate(sDebut, sFin) {
        var form = document.printFrm;
        form._date_min.value = sDebut;
        form._date_max.value = sFin;
        form._date_min_da.value = Date.fromDATE(sDebut).toLocaleDate();
        form._date_max_da.value = Date.fromDATE(sFin).toLocaleDate();
    }

    Main.add(function () {
        Control.Tabs.create('tabs-configure', true);

        Comptabilite.prepareUserField(getForm('printFrm'), 'chir_view', '1');
        Comptabilite.prepareUserField(getForm('printFrm'), 'all_chir_view', '2');

        {{if count($listPrat) == 1}}
        {{foreach from=$listPrat item=prat_info}}
        $V(form.chir_view, '{{$prat_info->_view}}');
        $V(form.all_chir_view, '{{$prat_info->_view}}');
        $V(form.chir, '{{$prat_info->user_id}}');
        {{/foreach}}
        {{/if}}
    });
</script>

{{if count($listPrat)}}
    <form name="printFrm" action="?" method="get" onSubmit="return checkRapport()" target="">
        <input type="hidden" name="m" value="facturation"/>
        <input type="hidden" name="a" value=""/>
        <input type="hidden" name="suppressHeaders" value="0"/>
        <input type="hidden" name="export_csv" value="">
        <input type="hidden" name="all_group_money" value="1">
        <input type="hidden" name="all_group_compta" value="1">
        <input type="hidden" name="cs" value="">
        <input type="hidden" name="function_compta" value="">
        <input type="hidden" name="type_view" value="">
        <input type="hidden" name="_type_affichage" value="">
        <input type="hidden" name="mode" value="">
        <input type="hidden" name="bloc_id" value="">
        <input type="hidden" name="order" value="">
        <input type="hidden" name="typeVue" value="">
        <input type="hidden" name="_etat_reglement_patient" value="">
        <input type="hidden" name="_etat_reglement_tiers" value="">
        <input type="hidden" name="_etat_accident_travail" value="">
        <table class="form main">
            <tr>
                <th class="category" colspan="3">{{tr}}CConsultation-title-choose-period{{/tr}}</th>
                <th class="category">{{mb_label object=$filter field="_prat_id"}}</th>
            </tr>
            <tr>
                <th>{{mb_label object=$filter field="_date_min"}}</th>
                <td>{{mb_field object=$filter field="_date_min" form="printFrm" canNull="false" register=true}}</td>
                <td rowspan="2">
                    <table>
                        <tr>
                            <td>
                                <input type="radio" name="select_days" onclick="changeDate('{{$now}}','{{$now}}');"
                                       value="day" checked="checked"/>
                                <label for="select_days_day">{{tr}}CConsultation-current-day{{/tr}}</label>
                                <br/>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$yesterday}}','{{$yesterday}}');" value="yesterday"/>
                                <label for="select_days_yesterday">{{tr}}CConsultation-yesterday{{/tr}}</label>
                                <br/>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$week_deb}}','{{$week_fin}}');" value="week"/>
                                <label for="select_days_week">{{tr}}CConsultation-current-week{{/tr}}</label>
                                <br/>
                            </td>
                            <td>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$month_deb}}','{{$month_fin}}');" value="month"/>
                                <label for="select_days_month">{{tr}}CConsultation-current-month{{/tr}}</label>
                                <br/>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$three_month_deb}}','{{$month_fin}}');"
                                       value="three_month"/>
                                <label for="select_days_three_month">{{tr}}CConsultation-three-last-month{{/tr}}</label>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="button" rowspan="2">
                    <input type="hidden" name="chir" value="{{$prat->_id}}"/>
                    <div>
                        <div id="chir_view_container">
                            <input type="text" name="chir_view" class="autocomplete"
                                   value="{{if $prat && $prat->_id}}{{$prat->_view}}{{else}}&mdash; {{tr}}common-all|pl{{/tr}}{{/if}}"
                                   style="text-align: left;"
                                   onmousedown="$V(this, '');"
                                   onblur="if (!$V(this)){$V(this, '&mdash; {{tr}}common-all|pl{{/tr}}');$V(this.form.chir, '');}"
                                   placeholder="&mdash; {{tr}}CMediusers-select-praticien{{/tr}}"/>

                            <button type="button" class="cancel notext me-tertiary me-dark"
                                    onclick="$V(this.form.chir, ''); $V(this.form.chir_view, '');">{{tr}}Erase{{/tr}}</button>
                        </div>
                        <div id="all_chir_view_container" style="display:none">
                            <input type="text" name="all_chir_view" class="autocomplete"
                                   value="&mdash; {{tr}}common-all|pl{{/tr}}" style="text-align: left;"
                                   onmousedown="$V(this, '');"
                                   onblur="if (!$V(this)){$V(this, '&mdash; {{tr}}common-all|pl{{/tr}}');$V(this.form.chir, '');}"
                                   placeholder="&mdash; {{tr}}CMediusers-select-praticien{{/tr}}"/>
                        </div>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox"
                                   onchange="Comptabilite.toggleUserSelector(this.form)"/>
                            {{tr}}common-User disabled{{/tr}}
                        </label>
                    </div>
                </td>
            </tr>

            <tr>
                <th>{{mb_label object=$filter field="_date_max"}}</th>
                <td>{{mb_field object=$filter field="_date_max" form="printFrm" canNull="false" register=true}} </td>
            </tr>
        </table>
    </form>
    {{if "dPfacturation CRelance use_relances"|gconf || "dPfacturation CJournalBill use_journaux"|gconf}}
        <ul id="tabs-configure" class="control_tabs">
            <li><a href="#compta">{{tr}}General{{/tr}}</a></li>
            {{if "dPfacturation CRelance use_relances"|gconf}}
                <li><a href="#relances">{{tr}}CRelance|pl{{/tr}}</a></li>
            {{/if}}
            {{if "dPfacturation CJournalBill use_journaux"|gconf}}
                <li><a href="#journaux">{{tr}}CJournalBill|pl{{/tr}}</a></li>
            {{/if}}
        </ul>
        <div id="compta" style="display: none;" class="me-padding-0">
            {{mb_include module=facturation template=vw_gestion}}
        </div>
        {{if "dPfacturation CRelance use_relances"|gconf}}
            <div id="relances" style="display: none;" class="me-padding-0">
                {{mb_include module=facturation template=vw_relances}}
            </div>
        {{/if}}

        {{if "dPfacturation CJournalBill use_journaux"|gconf}}
            <div id="journaux" style="display: none;">
                {{mb_include module=facturation template=vw_journaux}}
            </div>
        {{/if}}
    {{else}}
        {{mb_include module=facturation template=vw_gestion}}
    {{/if}}
{{else}}
    <div class="big-info">{{tr}}Compta.no_acces{{/tr}}</div>
{{/if}}
