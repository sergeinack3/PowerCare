{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=soins script=soins ajax=true}}

<script type="text/javascript">
Calendar.regField(getForm("changeDatePermissions").date, null, {noView: true});
</script>

{{mb_include module=admissions template=inc_refresh_page_message}}

<table class="tbl me-no-align" id="admissions">
  <tbody>
    {{foreach from=$affectations item=_aff}}
      {{assign var=_sejour value=$_aff->_ref_sejour}}
      <tr class="sejour-type-default sejour-type-{{$_sejour->type}} {{if !$_sejour->facturable}} non-facturable {{/if}}" id="permission{{$_aff->_id}}">
        {{mb_include module=admissions template="inc_vw_permission_line" _sejour=$_aff->_ref_sejour nodebug=true}}
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
      <a href="?m=dPadmissions&tab=vw_idx_permissions&date={{$hier}}" style="display: inline"><<<</a>
      {{$date|date_format:$conf.longdate}}
      <form name="changeDatePermissions" action="?" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="vw_idx_permissions" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
      <a href="?m=dPadmissions&tab=vw_idx_permissions&date={{$demain}}" style="display: inline">>>></a>
      <br />

      <em style="float: left; font-weight: normal;">
        {{$total}}
        {{if $type_externe == "depart"}}
          Départ(s)
        {{else}}
          Retour(s)
        {{/if}}
      </em>

      <select style="float: right" name="filterFunction" style="width: 16em;" onchange="changeFunction(this.value);">
        <option value=""> &mdash; Toutes les fonctions</option>
        {{foreach from=$functions item=_function}}
          <option value="{{$_function->_id}}" {{if $_function->_id == $filterFunction}}selected="selected"{{/if}} class="mediuser" style="border-color: #{{$_function->color}};">{{$_function}}</option>
        {{/foreach}}
      </select>
    </th>
  </tr>

  <tr>
    <td colspan="10">
        {{mb_include module=system template=inc_pagination total=$total current=$page
        change_page=changePage step=$step}}
    </td>
  </tr>

  {{assign var=url value="?m=$m&tab=vw_idx_permissions&type_externe=$type_externe"}}
  <tr>
    <th class="narrow">Valider</th>
    <th>
      Patient
    </th>
    <th class="narrow">
      <input type="text" size="3" onkeyup="Admissions.filter(this, 'admissions')" id="filter-patient-name" />
    </th>
    <th>Praticien</th>
    <th>Heure</th>
    {{if $type_externe == "depart"}}
      <th>Chambre</th>
      <th>Destination</th>
    {{else}}
      <th>Provenance</th>
      <th>Chambre</th>
    {{/if}}
    <th>Durée</th>
  </tr>
  </thead>
</table>
