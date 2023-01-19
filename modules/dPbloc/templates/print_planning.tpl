{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPplanningOp" script="ccam_selector"}}
{{mb_script module=bloc           script=edit_planning ajax=1}}

<script>
    function checkFormPrint(form, compact) {
        if (!checkForm(form)) {
            return false;
        }

        if (!compact) {
            compact = 0;
        }
        popPlanning(form, compact);
    }

    function popPlanning(form, compact) {
        var url = new Url("bloc", "view_planning");
        url.addFormData(form);

        url.addParam("_bloc_id[]", $V(form.elements["_bloc_id[]"]), true);
        url.addParam('_salle_id[]', $V(form.elements['_salle_id[]']), true);
        url.addParam('_compact', compact);

        if (form.planning_perso.checked) { // pour l'affichage du planning perso d'un anesthesiste
            url.addParam("planning_perso", true);
        }

        url.popup(1000, 600, 'Planning');
    }

    function printPlanningPersonnel(form) {
        var url = new Url("bloc", "print_planning_personnel");
        url.addElement(form._datetime_min);
        url.addElement(form._datetime_max);
        url.addParam("_bloc_id[]", $V(form.elements["_bloc_id[]"]), true);
        url.addParam("_salle_id[]", $V(form.elements["_salle_id[]"]), true);
        url.addElement(form._prat_id);
        url.addElement(form._specialite);
        url.popup(900, 500, $T('mod-dPbloc-tab-print_planning_personnel'));
    }

    function printFullPlanning(form) {
        var url = new Url("bloc", "print_full_planning");
        url.addElement(form._datetime_min);
        url.addElement(form._datetime_max);
        url.addRadio(form._print_annulees);
        url.addRadio(form._ranking);
        url.addParam("_bloc_id[]", $V(form.elements["_bloc_id[]"]), true);
        url.popup(900, 550, 'Planning');
    }

    function togglePrintFull(status) {
        var print_button = $("print_button");
        print_button.setAttribute("onclick", status ? "printFullPlanning(this.form)" : "checkFormPrint(this.form)");
        $$(".not-full").invoke(status ? "addClassName" : "removeClassName", "opacity-30");
    }

    function changeDate(sDebut, sFin) {
        var oForm = getForm("paramFrm");
        oForm._datetime_min.value = sDebut;
        oForm._datetime_max.value = sFin;
        oForm._datetime_min_da.value = Date.fromDATETIME(sDebut).toLocaleDateTime();
        oForm._datetime_max_da.value = Date.fromDATETIME(sFin).toLocaleDateTime();
    }

    function changeDateCal(minChanged) {
        var oForm = getForm("paramFrm");
        oForm.select_days[0].checked = false;
        oForm.select_days[1].checked = false;
        oForm.select_days[2].checked = false;
        oForm.select_days[3].checked = false;
        oForm.select_days[4].checked = false;
        oForm.select_days[5].checked = false;
        oForm.select_days[6].checked = false;
        oForm.select_days[7].checked = false;

        var minElement = oForm._datetime_min,
          maxElement = oForm._datetime_max,
          minView = oForm._datetime_min_da,
          maxView = oForm._datetime_max_da;

        if (minElement.value > maxElement.value) {
            if (minChanged) {
                $V(maxElement, minElement.value);
                $V(maxView, Date.fromDATE(maxElement.value).toLocaleDate());
            } else {
                $V(minElement, maxElement.value);
                $V(minView, Date.fromDATE(minElement.value).toLocaleDate());
            }
        }
    }

    //affiche ou cache le checkbox relatif à un anesthésiste
    function showCheckboxAnesth(element) {
        var form = getForm("paramFrm");
        $('perso').hide();
        if ($(element.options[element.selectedIndex]).hasClassName('anesth')) {
            $('perso').show();
            form.planning_perso.checked = "";
        }
    }
</script>

<form name="paramFrm" action="?m=bloc" method="post" onsubmit="return checkFormPrint(this)">
    <input type="hidden" name="_class" value="COperation"/>
    <input type="hidden" name="_chir" value="{{$chir}}"/>
    <table class="main me-align-auto">
        <tr class="me-row-valign">
            <td class="halfPane">
                <table class="form me-margin-top-0">
                    <tr>
                        <th class="category" colspan="4">{{tr}}common-period-choice{{/tr}}</th>
                    </tr>
                    <tr>
                        <th>{{mb_label object=$filter field="_datetime_min"}}</th>
                        <td>{{mb_field object=$filter field="_datetime_min" form="paramFrm" canNull="false" onchange="changeDateCal(true)" register=true}} </td>
                        <td rowspan="2">
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$now}} 00:00:00','{{$now}} 23:59:59');" value="day"
                                       checked/>
                                {{tr}}CConsultation-current-day{{/tr}}
                            </label>
                            <br/>
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$tomorrow}} 00:00:00','{{$tomorrow}} 23:59:59');"
                                       value="tomorrow"/>
                                {{tr}}Next-day{{/tr}}
                            </label>
                            <br/>
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$week_deb}} 00:00:00','{{$week_fin}} 23:59:59');"
                                       value="week"/>
                                {{tr}}CConsultation-current-week{{/tr}}
                            </label>
                            <br/>
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$month_deb}} 00:00:00','{{$month_fin}} 23:59:59');"
                                       value="month"/>
                                {{tr}}CConsultation-current-month{{/tr}}
                            </label>
                        </td>
                        <td rowspan="2">
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$j2}} 00:00:00','{{$j2}} 23:59:59');" value="j+2"/>
                                J+2
                            </label>
                            <br/>
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$j3}} 00:00:00','{{$j3}} 23:59:59');" value="j+3"/>
                                J+3
                            </label>
                            <br/>
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$next_week_deb}} 00:00:00','{{$next_week_fin}} 23:59:59');"
                                       value="next_week"/>
                                {{tr}}Last-week{{/tr}}
                            </label>
                            <br/>
                            <label>
                                <input type="radio" name="select_days"
                                       onclick="changeDate('{{$next_month_deb}} 00:00:00','{{$next_month_fin}} 23:59:59');"
                                       value="next_month"/>
                                {{tr}}Next-month{{/tr}}
                            </label>
                            <br/>
                        </td>
                    </tr>
                    <tr>
                        <th>{{mb_label object=$filter field="_datetime_max"}}</th>
                        <td>{{mb_field object=$filter field="_datetime_max" form="paramFrm" canNull="false" onchange="changeDateCal(false)" register=true}} </td>
                    </tr>
                    <tr>
                        <th class="category" colspan="4">{{tr}}COperation.type|pl{{/tr}}</th>
                    </tr>
                    <tr class="not-full">
                        <th>{{mb_label object=$filter field=_ranking}}</th>
                        <td colspan="3">{{mb_field object=$filter field=_ranking emptyLabel=All typeEnum=radio}}</td>
                    </tr>
                    <tr class="not-full">
                        <th>{{mb_label object=$filterSejour field="type"}}</th>
                        <td colspan="3">
                            {{mb_field object=$filterSejour field="type" canNull=true style="width: 15em;" emptyLabel="CSejour.type.all"}}
                        </td>
                    </tr>
                    <tr class="not-full">
                        <th>
                            <label for="paramFrm__intervention_emergency"
                                   title="{{tr}}COperation-Intervention in emergency|pl{{/tr}}">
                                {{tr}}COperation-Intervention in emergency|pl{{/tr}}
                            </label>
                        </th>
                        <td colspan="3">
                            <label>
                                <input type="radio" name="_intervention_emergency" value="" checked/> {{tr}}All{{/tr}}
                            </label>
                            <label>
                                <input type="radio" name="_intervention_emergency" value="1"/> {{tr}}Yes{{/tr}}
                            </label>
                            <label>
                                <input type="radio" name="_intervention_emergency" value="0"/> {{tr}}No{{/tr}}
                            </label>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="halfPane">
                <table class="form me-margin-top-0">
                    <tr>
                        <th class="category" colspan="3">{{tr}}Filter-other|pl{{/tr}}</th>
                    </tr>
                    <tr class="not-full">
                        <th>{{mb_label object=$filter field="_prat_id"}}</th>
                        <td>
                            <select name="_prat_id" style="width: 15em;"
                                    onchange="showCheckboxAnesth(this); this.form._specialite.value = '0';">
                                <option value="0">&mdash; {{tr}}CMediusers.praticiens.all{{/tr}}</option>
                                {{foreach from=$listPrat item=curr_prat}}
                                    <option class="{{if $curr_prat->isAnesth()}}mediuser anesth{{else}}mediuser{{/if}}"
                                            style="border-color: #{{$curr_prat->_ref_function->color}};"
                                            value="{{$curr_prat->user_id}}">
                                        {{$curr_prat->_view}}
                                    </option>
                                {{/foreach}}
                            </select>
                            <span id="perso" {{if !$praticien->isAnesth()}} style="display:none;"{{/if}}>
                {{tr}}Planning-perso{{/tr}} <input type="checkbox" name="planning_perso"/>
              </span>
                        </td>
                    </tr>
                    <tr class="not-full">
                        <th>{{mb_label object=$filter field="_specialite"}}</th>
                        <td>
                            <select name="_specialite" style="width: 15em;" onchange="this.form._prat_id.value = '0';">
                                <option value="0">&mdash; {{tr}}CDiscipline.all{{/tr}}</option>
                                {{foreach from=$listSpec item=curr_spec}}
                                    <option value="{{$curr_spec->function_id}}" class="mediuser"
                                            style="border-color: #{{$curr_spec->color}};">
                                        {{$curr_spec->text}}
                                    </option>
                                {{/foreach}}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>{{mb_label object=$filter field="_bloc_id"}}</th>
                        <td valign="top">
                            <select name="_bloc_id[]" style="width: 15em;"
                                    onchange="this.form.elements['_salle_id[]'].selectedIndex=0">
                                <option value="0">&mdash; {{tr}}CBlocOperatoire.all{{/tr}}</option>
                                {{foreach from=$listBlocs item=curr_bloc}}
                                    <option value="{{$curr_bloc->_id}}">
                                        {{$curr_bloc->_view}}
                                    </option>
                                {{/foreach}}
                            </select>
                            <input type="checkbox"
                                   onclick="this.form.elements['_bloc_id[]'].writeAttribute('multiple', this.checked ? 'multiple' : null)"/>
                        </td>
                    </tr>
                    <tr class="not-full">
                        <th>{{mb_label object=$filter field="_salle_id"}}</th>
                        <td>
                            <select name="_salle_id[]" style="width: 15em;"
                                    onchange="this.form.elements['_bloc_id[]'].selectedIndex=0">
                                <option value="0">&mdash; {{tr}}CSalle.all{{/tr}}</option>
                                {{foreach from=$listBlocs item=curr_bloc}}
                                    <optgroup label="{{$curr_bloc->_view}}">
                                        {{foreach from=$curr_bloc->_ref_salles item=curr_salle}}
                                            <option value="{{$curr_salle->_id}}"
                                                    {{if $curr_salle->_id == $filter->salle_id}}selected{{/if}}>
                                                {{$curr_salle->nom}}
                                            </option>
                                        {{/foreach}}
                                    </optgroup>
                                {{/foreach}}
                            </select>
                            <input type="checkbox"
                                   onclick="this.form.elements['_salle_id[]'].writeAttribute('multiple', this.checked ? 'multiple' : null)"/>
                        </td>
                    </tr>
                    <tr class="not-full">
                        <th>{{mb_label object=$filter field="_codes_ccam"}}</th>
                        <td><input type="text" name="_codes_ccam" style="width: 12em;" value=""/>
                            <button type="button" class="search notext"
                                    onclick="CCAMSelector.init()">{{tr}}mod-dPccam-tab-vw_find_code{{/tr}}</button>
                            <script>
                                CCAMSelector.init = function () {
                                    this.sForm = "paramFrm";
                                    this.sClass = "_class";
                                    this.sChir = "_chir";
                                    this.sView = "_codes_ccam";
                                    this.pop();
                                };
                                var oForm = getForm('paramFrm');
                                Main.add(function () {
                                    var url = new Url("dPccam", "autocompleteCcamCodes");
                                    url.autoComplete(oForm._codes_ccam, '', {
                                        minChars:      1,
                                        dropdown:      true,
                                        width:         "250px",
                                        updateElement: function (selected) {
                                            $V(oForm._codes_ccam, selected.down("strong").innerHTML);
                                        }
                                    });
                                });
                            </script>
                        </td>
                    </tr>
                    <tr class="not-full">
                        <th>{{mb_label object=$filter field="exam_extempo"}}</th>
                        <td>{{mb_field object=$filter field="exam_extempo" typeEnum=checkbox}}</td>
                    </tr>
                    <tr class="not-full">
                        <th>{{tr}}COperation-back-dossiers_anesthesie.none{{/tr}}</th>
                        <td><input type="checkbox" name="no_consult_anesth"
                                   onclick="this.form.no_consult_anesth.value =  this.checked ? 1 : 0;" value="0"/></td>
                    </tr>
                </table>

            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{assign var="class" value="CPlageOp"}}
                <table class="form me-no-align me-no-box-shadow">
                    <tr>
                        <th class="category" colspan="2">{{tr}}common-Display settings{{/tr}}</th>
                    </tr>
                    <tr class="me-row-valign">
                        <td class="halfPane me-padding-0 me-padding-bottom-8">
                            <fieldset>
                                <legend>{{tr}}CPatient.data{{/tr}}</legend>
                                <table class="form me-no-align me-no-box-shadow me-margin-top-0">
                                    <tr>
                                        <th style="width: 50%">
                                            <label for="_print_ipp_1"
                                                   title="{{tr}}COperation-_print_ipp-desc{{/tr}}">{{tr}}COperation-_print_ipp{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_print_ipp" value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_print_ipp" value="0"
                                                                       checked/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="_coordonnees_1"
                                                   title="{{tr}}COperation-_coordonnees-desc{{/tr}}">{{tr}}COperation-_coordonnees{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_coordonnees" value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_coordonnees" value="0"
                                                                       checked/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label
                                              title="{{tr}}COperation-_show_identity-desc{{/tr}}">{{tr}}COperation-_show_identity{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_show_identity" value="1"
                                                                        checked/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_show_identity" value="0"/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label title="{{tr}}COperation-_display_main_doctor{{/tr}}"
                                                   for="_display_main_doctor">{{tr}}COperation-_display_main_doctor{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_display_main_doctor"
                                                                        value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_display_main_doctor"
                                                                       value="0" checked/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label title="{{tr}}COperation-_display_allergy{{/tr}}"
                                                   for="_display_allergy">{{tr}}COperation-_display_allergy{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_display_allergy" value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_display_allergy" value="0"
                                                                       checked/>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                        <td class="halfPane me-padding-0 me-padding-bottom-8">
                            <fieldset>
                                <legend>{{tr}}CSejour.data{{/tr}}</legend>
                                <table class="form me-no-align me-no-box-shadow me-margin-top-0">
                                    <tr>
                                        <th style="width: 50%">
                                            <label for="_print_numdoss_1"
                                                   title="{{tr}}COperation-_print_numdoss-desc{{/tr}}">{{tr}}COperation-_print_numdoss{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_print_numdoss" value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_print_numdoss" value="0"
                                                                       checked/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label
                                              title="{{tr}}COperation-_show_comment_sejour-desc{{/tr}}">{{tr}}COperation-_show_comment_sejour{{/tr}}</label>
                                        </th>
                                        <td>
                                            {{assign var="var" value="show_comment_sejour"}}
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_show_comment_sejour"
                                                                        value="1"
                                                                        {{if "dPbloc printing show_comment_sejour"|gconf == 1}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_show_comment_sejour"
                                                                       value="0"
                                                                       {{if "dPbloc printing show_comment_sejour"|gconf == 0}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr class="me-row-valign">
                        <td class="halfPane me-padding-0 me-padding-bottom-8">
                            <fieldset>
                                <legend>{{tr}}COperation.data{{/tr}}</legend>
                                <table class="form me-no-align me-no-box-shadow">
                                    <tr>
                                        <th style="width: 50%">{{mb_label object=$filter field="_ccam_libelle"}}</th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_ccam_libelle" value="1"
                                                                        {{if "dPbloc printing libelle_ccam"|gconf == "1"}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_ccam_libelle" value="0"
                                                                       {{if "dPbloc printing libelle_ccam"|gconf == "0"}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="_materiel_1"
                                                   title="{{tr}}COperation-_materiel-desc{{/tr}}">{{tr}}COperation-_materiel{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_materiel" value="1"
                                                                        {{if "dPbloc printing view_materiel"|gconf == "1"}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_materiel" value="0"
                                                                       {{if "dPbloc printing view_materiel"|gconf == "0"}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="_missing_materiel_1"
                                                   title="{{tr}}COperation-_missing_materiel-desc{{/tr}}">{{tr}}COperation-_missing_materiel{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_missing_materiel" value="1"
                                                                        {{if "dPbloc printing view_missing_materiel"|gconf == "1"}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_missing_materiel" value="0"
                                                                       {{if "dPbloc printing view_missing_materiel"|gconf == "0"}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="_extra_1"
                                                   title="{{tr}}COperation-_extra-desc{{/tr}}">{{tr}}COperation-_extra{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_extra" value="1"
                                                                        {{if "dPbloc printing view_extra"|gconf == "1"}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_extra" value="0"
                                                                       {{if "dPbloc printing view_extra"|gconf == "0"}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="_duree_1"
                                                   title="{{tr}}COperation-_duree-desc{{/tr}}">{{tr}}COperation-_duree{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_duree" value="1"
                                                                        {{if "dPbloc printing view_duree"|gconf == "1"}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_duree" value="0"
                                                                       {{if "dPbloc printing view_duree"|gconf == "0"}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="_examens_perop_1"
                                                   title="{{tr}}COperation-show-examens-perop-desc{{/tr}}">
                                                {{tr}}COperation-show-examens-perop{{/tr}}
                                            </label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_examens_perop" value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_examens_perop" value="0"
                                                                       checked/>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                        <td class="halfPane me-padding-0 me-padding-bottom-8">
                            <fieldset>
                                <legend>{{tr}}Display-mode{{/tr}}</legend>
                                <table class="form me-no-align me-no-box-shadow">
                                    <tr>
                                        <th style="width: 50%">{{mb_label object=$filter field="_plage"}}</th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_plage" value="1"
                                                                        {{if "dPbloc printing plage_vide"|gconf == "1"}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_plage" value="0"
                                                                       {{if "dPbloc printing plage_vide"|gconf == "0"}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="print_annulees_1"
                                                   title="{{tr}}COperation-_print_annulees-desc{{/tr}}">{{tr}}COperation-_print_annulees{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_print_annulees" value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_print_annulees" value="0"
                                                                       checked/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="_hors_plage_1"
                                                   title="{{tr}}COperation-_hors_plage{{/tr}}">{{tr}}COperation-_hors_plage{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_hors_plage" value="1"
                                                                        {{if "dPbloc printing view_hors_plage"|gconf == "1"}}checked{{/if}}/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_hors_plage" value="0"
                                                                       {{if "dPbloc printing view_hors_plage"|gconf == "0"}}checked{{/if}}/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label
                                              title="{{tr}}COperation-_by_prat{{/tr}}">{{tr}}COperation-_by_prat{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_by_prat" value="1"
                                                                        onclick="this.form._by_bloc[1].checked=true;"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_by_prat" value="0" checked/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label
                                              title="{{tr}}COperation-_by_bloc{{/tr}}">{{tr}}COperation-_by_bloc{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_by_bloc" value="1"
                                                                        onclick="this.form._by_prat[1].checked=true;"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_by_bloc" value="0" checked/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="print_full"
                                                   title="{{tr}}COperation-_print_full{{/tr}}">{{tr}}COperation-_print_full{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_print_full" value="1"
                                                                        onclick="togglePrintFull(true)"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_print_full" value="0" checked
                                                                       onclick="togglePrintFull(false)"/>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="_page_break"
                                                   title="{{tr}}COperation-_page_break{{/tr}}">{{tr}}COperation-_page_break{{/tr}}</label>
                                        </th>
                                        <td>
                                            <label>
                                                {{tr}}Yes{{/tr}} <input type="radio" name="_page_break" value="1"/>
                                            </label>
                                            <label>
                                                {{tr}}No{{/tr}} <input type="radio" name="_page_break" value="0"
                                                                       checked/>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="button">
                            <button class="print" id="print_button" type="button"
                                    onclick="checkFormPrint(this.form)">{{tr}}Display{{/tr}}</button>
                            <button class="print" id="print_compact_button" type="button"
                                    onclick="checkFormPrint(this.form, 1)">{{tr}}Print.compact{{/tr}}</button>
                            {{if $can->edit}}
                                <button class="print" type="button"
                                        onclick="printPlanningPersonnel(this.form)">{{tr}}mod-dPbloc-tab-print_planning_personnel{{/tr}}</button>
                            {{/if}}
                            <button class="print" type="button"
                                    title="{{tr}}CPlageOp-action-Print of free vacations and canceled vacations-desc{{/tr}}"
                                    onclick="EditPlanning.printVacationStatus(this.form);">
                                {{tr}}CPlageOp-action-Status of empty operating ranges{{/tr}}
                            </button>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
