{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Calendar.regField(getForm("changeDateAdmissions").date, null, {noView: true});
</script>

{{mb_include module=admissions template=inc_refresh_page_message}}

<table class="tbl me-no-align" id="admissions">
  <tr>
    <th class="title" colspan="10">
      <a href="?m={{$current_m}}&tab=vw_sejours_validation&date={{$hier}}" style="display: inline"><<<</a>
      {{$date|date_format:$conf.longdate}}
      <form name="changeDateAdmissions" action="?" method="get">
        <input type="hidden" name="m" value="{{$current_m}}" />
        <input type="hidden" name="tab" value="vw_sejours_validation" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
      <a href="?m={{$current_m}}&tab=vw_sejours_validation&date={{$demain}}" style="display: inline">>>></a>
      <br />
      
      <em style="float: left; font-weight: normal;">
      {{$sejours|@count}}
      {{if $recuse == -1}}
        séjour(s) en attente de validation
      {{elseif $recuse == 0}}
        séjour(s) validé(s)
      {{else}}
        séjour(s) récusé(s)
      {{/if}}
      </em>
  
      <select style="float: right" name="filterFunction" style="width: 16em;" onchange="reloadAdmission(this.value);">
        <option value=""> &mdash; Toutes les fonctions</option>
        {{foreach from=$functions item=_function}}
          <option value="{{$_function->_id}}" {{if $_function->_id == $filterFunction}}selected="selected"{{/if}} class="mediuser" style="border-color: #{{$_function->color}};">{{$_function}}</option>
        {{/foreach}}
      </select>
    </th>
  </tr>
  
  {{assign var=url value="?m=$current_m&tab=vw_sejours_validation&recuse=$recuse"}}
  <tr>
    <th class="narrow">Validation</th>
    <th>{{mb_colonne class="CSejour" field="patient_id" order_col=$order_col order_way=$order_way url=$url}}</th>
    <th class="narrow"><input type="text" size="3" onkeyup="Admissions.filter(this, 'admissions')" id="filter-patient-name" /></th>
    <th>{{mb_colonne class="CSejour" field="praticien_id" order_col=$order_col order_way=$order_way url=$url}}</th>
    <th>{{mb_colonne class="CSejour" field="entree_prevue" order_col=$order_col order_way=$order_way url=$url}}</th>
    <th class="narrow">Chambre</th>
    <th>Couv.</th>
  </tr>

  {{foreach from=$sejours item=_sejour}}
  <tr class="sejour-type-default sejour-type-{{$_sejour->type}} {{if !$_sejour->facturable}} non-facturable {{/if}}" id="admission{{$_sejour->sejour_id}}">
    {{mb_include module=dPadmissions template="inc_vw_sejour_line" nodebug=true}}
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">{{tr}}None{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>