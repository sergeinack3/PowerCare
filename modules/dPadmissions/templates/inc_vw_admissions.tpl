{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=print_global value=0}}

{{if "dmp"|module_active}}
    {{mb_script module="dmp" script="cdmp" ajax="true"}}
{{/if}}

<script>
    Main.add(function () {
        {{if !$print_global}}
        Prestations.callback = Admissions.reloadAdmissionLine;
        Calendar.regField(getForm("changeDateAdmissions").date, null, {noView: true});
        Admissions.restoreSelection();
        {{/if}}
    });
</script>

{{mb_include module=admissions template=inc_refresh_page_message}}

{{if $period}}
    <div class="small-info">
        Vue partielle limitée au <strong>{{$period}}</strong>. Veuillez changer le filtre pour afficher toute la
        journée.
    </div>
{{/if}}

<table class="tbl me-no-align me-small-tbl" id="admissions"
       {{if $print_global}}style="border-collapse: separate;"{{/if}}>
    <tbody>
    {{foreach from=$sejours item=_sejour}}
        <tr
          class="sejour sejour-type-default sejour-type-{{$_sejour->type}} {{if !$_sejour->facturable}} non-facturable {{/if}}"
          id="{{$_sejour->_guid}}">
            {{mb_include module=admissions template="inc_vw_admission_line" nodebug=true}}
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="12" class="empty">{{tr}}CAdmission.none{{/tr}}</td>
        </tr>
    {{/foreach}}
    </tbody>
    <thead>
    {{if $print_global}}
        <tr class="clear">
            <th colspan="14">
                <h1>
                    <a href="#" onclick="window.print()">
                        {{tr var1=$date|date_format:$conf.longdate var2=$total}}admissions-Admission of %s (%s admission)|pl{{/tr}}
                    </a>
                </h1>
            </th>
        </tr>
    {{else}}
        <tr>
            <th class="title" colspan="18">
                <div style="margin-bottom: 5px;">
                    <a href="#1" style="display: inline"
                       onclick="$V(getForm('selType').date, '{{$hier}}'); reloadFullAdmissions()">&lt;&lt;&lt;</a>
                    {{$date|date_format:$conf.longdate}}
                    <form name="changeDateAdmissions" action="?" method="get">
                        <input type="hidden" name="date" class="date" value="{{$date}}"
                               onchange="$V(getForm('selType').date, this.value); reloadFullAdmissions()"/>
                    </form>
                    <a href="#1" style="display: inline"
                       onclick="$V(getForm('selType').date, '{{$demain}}'); reloadFullAdmissions()">&gt;&gt;&gt;</a>
                </div>
                <div>
                    <em style="float: left; font-weight: normal;">
                        {{$total}}
                        {{if $selAdmis == "n"}}admissions non effectuées
                        {{elseif $selSaisis == "n"}}dossiers non préparés
                        {{else}}admissions ce jour
                        {{/if}}
                    </em>

                    <select style="float: right" name="filterFunction" style="width: 16em;"
                            onchange="$V(getForm('selType').filterFunction, this.value); reloadAdmission();">
                        <option value=""> &mdash; Toutes les fonctions</option>
                        {{mb_include module=mediusers template=inc_options_function list=$functions selected=$filterFunction}}
                    </select>
                    <br>
                    <button id="printSelectionButton" type="button" onclick="Admissions.choosePrintForSelection()"
                            disabled class="button print me-primary disabled">
                        {{tr}}CCompteRendu-print_for_select{{/tr}}
                    </button>
                    <div class="me-inline-block">
                        {{mb_include module=hospi template=inc_send_prestations type='admissions'}}
                    </div>
                </div>

            </th>
            {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
                <th class="title"></th>
            {{/if}}
        </tr>
      <tr>
        <td colspan="18">
          {{mb_include module=system template=inc_pagination total=$total current=$page
          change_page=changePage step=$step}}
        </td>
      </tr>
    {{/if}}
    <tr>
        <th class="narrow" {{if $print_global}}style="display: none;"{{/if}}>
            <input type="checkbox" style="float: left;"
                   onclick="Admissions.togglePrint(this.checked); Admissions.updatePrintSelectionButtonDisplay();"/>
        </th>
        <th>
            {{mb_colonne class="CSejour" field="patient_id" order_col=$order_col order_way=$order_way function=sortBy}}
        </th>
        <th {{if $print_global}}style="display: none;"{{/if}}>
            <input type="text" onkeyup="Admissions.filter(this, 'admissions')" id="filter-patient-name"/>
        </th>
        {{* Icones contextuelles (appels contextuels, notifications, prestations,... *}}
        {{if $canPlanningOp->read && $flag_contextual_icons}}
            <th class="narrow" {{if $print_global}}style="display: none;"{{/if}}></th>
        {{/if}}

        <th>{{tr}}CSejour-admit{{/tr}}</th>
        {{if $canPlanningOp->read && $flag_dmp}}
            <th class="narrow" {{if $print_global}}style="display: none;"{{/if}}>{{tr}}CSubmissionLot.type.DMP{{/tr}}</th>
        {{/if}}
        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
            <th class="narrow me-ws-wrap me-text-align-center"
                {{if $print_global}}style="display: none;"{{/if}}>{{tr}}CAppFine{{/tr}}
                <button style="margin-top: 1%;" type="submit"
                        onclick="appFineClient.relaunchPatientsAdmission('listAdmissions', 'print_doc')">
                    <i class="fas fa-share fa-lg"
                       title="{{tr}}CAppFineClient-msg-Relaunch patient dashboard task{{/tr}}"></i>
                    {{tr}}CAppFineClient-relaunch{{/tr}}
                </button>
            </th>
        {{/if}}
        {{if "dPplanningOp CSejour use_phone"|gconf}}
            <th class="narrow">{{tr}}CSejour-appel{{/tr}}</th>
        {{/if}}
        <th>
            {{mb_colonne class="CSejour" field="praticien_id" order_col=$order_col order_way=$order_way function=sortBy}}
        </th>
        {{*  DHE  *}}
        {{if $canPlanningOp->read}}
            <th {{if $print_global}}style="display: none"{{/if}}>
                {{tr}}COperation-event-dhe{{/tr}}
            </th>
        {{/if}}
        <th>
            {{mb_colonne class="CSejour" field="_passage_bloc" order_col=$order_col order_way=$order_way function=sortBy}}
        </th>
        <th>
            {{mb_colonne class="CSejour" field="entree_prevue" order_col=$order_col order_way=$order_way function=sortBy}}
        </th>
        <th class="narrow">Chambre</th>
        <th class="narrow" {{if $print_global}}style="display: none;"{{/if}}>{{tr}}CCompteRendu|pl{{/tr}}</th>
        <th class="narrow me-text-align-center" {{if $print_global}}style="display: none;"{{/if}}>
            {{if $canAdmissions->edit && $sejours|@count}}
                <form name="Multiple-CSejour" action="?" method="post" onsubmit="return submitMultiple(this);">
                    <input type="hidden" name="m" value="planningOp"/>
                    <input type="hidden" name="dosql" value="do_sejour_aed"/>
                    {{assign var=sejours_ids value=$sejours|@array_keys}}
                    <input type="hidden" name="sejour_ids" value="{{"-"|implode:$sejours_ids}}"/>
                    <input type="hidden" name="entree_preparee" value="1"/>
                    <button class="tick oneclick" style="margin-right: 12px" type="submit">
                        {{tr}}CSejour-entree_preparee-all{{/tr}}
                    </button>
                </form>
            {{else}}
                {{tr}}CSejour-entree_preparee-all{{/tr}}
            {{/if}}
        </th>
        <th> Anesth.</th>
        <th class="narrow">Couv.</th>
        {{if $app->user_prefs.show_dh_admissions}}
            <th class="narrow" colspan="2">DH</th>
        {{/if}}
    </tr>
    </thead>
</table>

{{mb_include module=forms template=inc_widget_ex_class_register_multiple_end event_name=preparation_entree object_class="CSejour"}}
