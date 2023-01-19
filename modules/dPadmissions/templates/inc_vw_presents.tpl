{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=prestations ajax=1}}

<script>
  Main.add(function() {
    Admissions.restoreSelection("listPresents");
    Calendar.regField(getForm("changeDatePresents").date, null, {noView: true});
    Calendar.regField(getForm("changeHeurePresents").heure, null);
    Prestations.callback = PatientsPresents.reloadPresent;
  });
</script>

{{mb_include module=admissions template=inc_refresh_page_message}}

<table class="tbl me-no-align" id="admissions" data-date="{{$date}}">
  <tbody>
    {{foreach from=$sejours item=_sejour}}
      <tr class="sejour sejour-type-default sejour-type-{{$_sejour->type}}
        {{if !$_sejour->facturable}} non-facturable {{/if}}" id="{{$_sejour->_guid}}">
        {{mb_include module=admissions template="inc_vw_present_line" nodebug=true}}
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}None{{/tr}}</td>
      </tr>
    {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th class="title" colspan="10">
      <button type="button" class="print notext me-tertiary" style="float: right;" onclick="this.up('table').print();"></button>
      <button type="button" class="hslip notext compact me-tertiary me-dark" style="vertical-align: bottom; float: left;margin-right:-20px;"
              onclick="Admissions.toggleListPresent();" title="Afficher/cacher la colonne de gauche"></button>
      <a href="?m=admissions&tab=vw_idx_present&date={{$hier}}" style="display: inline">&lt;&lt;&lt;</a>
      {{$date|date_format:$conf.longdate}}
      <form name="changeDatePresents" action="?" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="vw_idx_present" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>

      <form name="changeHeurePresents" action="?" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="vw_idx_present" />
        <input type="hidden" name="heure" class="time" value="{{$heure}}" onchange="this.form.submit()" />
      </form>
      <a href="?m=admissions&tab=vw_idx_present&date={{$demain}}" style="display: inline">&gt;&gt;&gt;</a>
      <br />
      <em style="float: left; font-weight: normal;">
        {{$total}} {{if $heure}} / {{$total_sejours}}{{/if}} présents ce jour
        {{if $heure}} à {{$heure|date_format:$conf.time}}{{/if}}
      </em>

      <form name="filterFunctionForm" method="get">
        <select style="float: right" name="filterFunction" style="width: 16em;" onchange="PatientsPresents.reloadPresent();">
          <option value=""> &mdash; Toutes les fonctions</option>
          {{mb_include module=mediusers template=inc_options_function list=$functions selected=$filterFunction}}
        </select>
      </form>
    </th>
  </tr>

  <tr>
    <td colspan="10">
        {{mb_include module=system template=inc_pagination total=$total current=$page
        change_page=PatientsPresents.changePageSejours step=$step}}
    </td>
  </tr>

  {{assign var=url value="?m=$m&tab=vw_idx_present"}}
  <tr>
    <th>
      <input type="checkbox" style="float: left;" onclick="Admissions.togglePrint(this.checked)"/>
      {{mb_colonne class="CSejour" field="patient_id" order_col=$order_col order_way=$order_way url=$url}}
    </th>
    <th class="narrow">
      <input type="text" size="3" onkeyup="Admissions.filter(this, 'admissions')" id="filter-patient-name" />
    </th>

    <th>
      <input type="text" size="3" onkeyup="Admissions.filter(this, 'admissions', 'CMediusers-view')"
             style="float: right;margin-left: -45px;" id="filter-praticien-name" />
      {{mb_colonne class="CSejour" field="praticien_id" order_col=$order_col order_way=$order_way url=$url}}
    </th>

    <th class="narrow">
      {{mb_colonne class="CSejour" field="entree" order_col=$order_col order_way=$order_way url=$url}}
    </th>

    <th class="narrow">
      {{mb_colonne class="CSejour" field="sortie" order_col=$order_col order_way=$order_way url=$url}}
    </th>

    <th>
      <input type="text" size="3" onkeyup="Admissions.filter(this, 'admissions', 'CChambre-view')"
             style="float: right;margin-left: -45px;" id="filter-chambre-name" />
      {{mb_colonne class="CAffectation" field="_chambre" order_col=$order_col order_way=$order_way url=$url}}
    </th>

    <th>
      {{if $canAdmissions->edit && $sejours|@count}}
        <form name="Multiple-CSejour" action="?" method="post" onsubmit="return PatientsPresents.submitMultiple(this);">
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_sejour_aed" />
          {{assign var=sejours_ids value=$sejours|@array_keys}}
          <input type="hidden" name="sejour_ids" value="{{"-"|implode:$sejours_ids}}" />
          <input type="hidden" name="entree_preparee" value="1" />
          <button class="tick oneclick" type="submit">
            {{tr}}CSejour-entree_preparee-all{{/tr}}
          </button>
        </form>
      {{else}}
        {{tr}}CSejour-entree_preparee-all{{/tr}}
      {{/if}}
    </th>
  </tr>
  </thead>
</table>
