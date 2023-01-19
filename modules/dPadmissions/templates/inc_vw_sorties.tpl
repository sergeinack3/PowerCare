{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=print_global value=0}}

<script>
  Main.add(function() {
    {{if !$print_global}}
      Admissions.restoreSelection();
      Calendar.regField(getForm("changeDateSorties").date, null, {noView: true});
      Prestations.callback = reloadSorties;
    {{/if}}
  });
</script>

{{mb_include module=admissions template=inc_refresh_page_message}}

{{if $period}}
  <div class="small-info">
    Vue partielle limitée au <strong>{{$period}}</strong>. Veuillez changer le filtre pour afficher toute la journée.
  </div>
{{/if}}

<table class="tbl me-small-tbl" id="sortie" {{if $print_global}}style="border-collapse: separate;"{{/if}}>
  <tbody>
    {{foreach from=$sejours item=_sejour}}
      <tr class="sejour-type-default sejour-type-{{$_sejour->type}} {{if !$_sejour->facturable}} non-facturable {{/if}}" id="{{$_sejour->_guid}}">
        {{mb_include module="admissions" template="inc_vw_sortie_line" nodebug=true}}
      </tr>
    {{/foreach}}
  </tbody>

  <thead>
  {{if $print_global}}
    <tr class="clear">
      <th colspan="15">
        <h1>
          <a href="#" onclick="window.print()">
            {{tr var1=$date|date_format:$conf.longdate var2=$total}}admissions-Exit of %s (%s exit)|pl{{/tr}}
          </a>
        </h1>
      </th>
    </tr>
  {{else}}
    <tr>
      <th class="title" colspan="15">
        <div style="margin-bottom: 5px;">
          <a href="#1" onclick="$V(getForm('selType').date, '{{$hier}}'); reloadFullSorties()" style="display: inline">&lt;&lt;&lt;</a>
          {{$date|date_format:$conf.longdate}}
          <form name="changeDateSorties" action="?" method="get">
            <input type="hidden" name="date" class="date" value="{{$date}}" onchange="$V(getForm('selType').date, this.value); reloadFullSorties()" />
          </form>
          <a href="#1" onclick="$V(getForm('selType').date, '{{$demain}}'); reloadFullSorties()"  style="display: inline">&gt;&gt;&gt;</a>
        </div>

        <em style="float: left; font-weight: normal;">
          {{$total}}
          {{if $selSortis == "n"}}sorties non effectuées
          {{else}}sorties ce jour
          {{/if}}
        </em>

        <select style="float: right" name="filterFunction" style="width: 16em;" onchange="$V(getForm('selType').filterFunction, this.value); reloadSorties();">
          <option value=""> &mdash; Toutes les fonctions</option>
          {{mb_include module="mediusers" template="inc_options_function" list=$functions selected=$filterFunction}}
        </select>
        <br>
        <button id="printSelectionButton" type="button" onclick="Admissions.choosePrintForSelection()"
                disabled class="button me-margin-top-4 print me-primary disabled">
          {{tr}}CCompteRendu-print_for_select{{/tr}}
        </button>
        <div class="me-inline-block me-margin-top-2">
          {{mb_include module=hospi template=inc_send_prestations type='sortie'}}
        </div>
        {{if $type == "ambu" || $type == "exte" }}
          <button class="print" type="button" onclick="printAmbu('{{$type}}')">{{tr}}Print{{/tr}} {{tr}}CSejour.type.{{$type}}{{/tr}}</button>
        {{/if}}
      </th>
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
      <th  {{if $print_global}}style="display: none;"{{/if}}>
        <input type="text" onkeyup="Admissions.filter(this, 'sortie')" id="filter-patient-name" />
      </th>
      {{* Icones contextuelles (appels contextuels, notifications, prestations,... *}}
      {{if $canPlanningOp->read && $flag_contextual_icons}}
        <th class="narrow" {{if $print_global}}style="display: none;"{{/if}}></th>
      {{/if}}

      <th class="narrow">Effectuer la sortie</th>

      {{if "dPplanningOp CSejour use_phone"|gconf}}
        <th class="narrow">{{tr}}CSejour-appel{{/tr}}</th>
      {{/if}}

      {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
        <th class="narrow me-ws-wrap me-text-align-center" {{if $print_global}}style="display: none;"{{/if}}>{{tr}}CAppFine{{/tr}}
          <button style="margin-top: 1%;" type="submit"
                  onclick="appFineClient.relaunchPatientsAdmission('listSorties', 'print_doc')">
            <i class="fas fa-share fa-lg" title="{{tr}}CAppFineClient-msg-Relaunch patient dashboard task{{/tr}}"></i>
            {{tr}}CAppFineClient-relaunch{{/tr}}
          </button>
        </th>
      {{/if}}

      <th>
        {{mb_colonne class="CSejour" field="praticien_id" order_col=$order_col order_way=$order_way function=sortBy}}
      </th>

      {{* DHE *}}
      {{if $canPlanningOp->read}}
        <th {{if $print_global}}style="display: none;"{{/if}} class="narrow">{{tr}}COperation-event-dhe{{/tr}}</th>
      {{/if}}

      <th>
        {{mb_colonne class="CSejour" field="sortie_prevue" order_col=$order_col order_way=$order_way function=sortBy}}
      </th>
      <th>Chambre</th>
      <th class="narrow" {{if $print_global}}style="display: none;"{{/if}}>{{tr}}CCompteRendu|pl{{/tr}}</th>
      <th class="narrow" {{if $print_global}}style="display: none;"{{/if}}>
        {{if $canAdmissions->edit && $sejours|@count}}
          <form name="Multiple-CSejour-sortie_preparee" action="?" method="post" onsubmit="return submitMultiple(this);">
            <input type="hidden" name="m" value="planningOp" />
            <input type="hidden" name="dosql" value="do_sejour_aed" />
            {{assign var=sejours_ids value=$sejours|@array_keys}}
            <input type="hidden" name="sejour_ids" value="{{"-"|implode:$sejours_ids}}" />
            <input type="hidden" name="sortie_preparee" value="1" />
            <button class="tick oneclick" type="submit">
              {{tr}}CSejour-sortie_preparee-all{{/tr}}
            </button>
          </form>
        {{else}}
          {{tr}}CSejour-sortie_preparee-all{{/tr}}
        {{/if}}
      </th>
      {{if $app->user_prefs.show_dh_admissions}}
        <th colspan="2">DH</th>
      {{/if}}
    </tr>
  </thead>
</table>
