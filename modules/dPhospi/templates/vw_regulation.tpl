{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions script=admissions ajax=1}}
{{mb_script module=hospi script=regulation ajax=1}}

<script>
  Main.add(function () {
    var form = getForm('searchSejourRegulation');
    Calendar.regField(form.date_regulation, null, {datePicker: true, timePicker: true});
    Regulation.searchSejours(form);
  });
</script>


<form name="searchSejourRegulation" method="get" action="?">
  <table class="main form">
    <tr>
      <th>
        <label title="{{tr}}Regulation-date_ref-desc{{/tr}}">
          {{tr}}Regulation-date_ref{{/tr}}
        </label>
      </th>
      <td><input type="hidden" name="date_regulation" class="notNull dateTime" value="{{$filter->_date_min}}" /></td>
      <th rowspan="4">{{mb_title object=$filter field="type"}}</th>
      <td rowspan="4">
        <select name="type" size="5" multiple>
          <option value="" {{if !$types|@count}}selected="selected"{{/if}}>&mdash; {{tr}}All{{/tr}}</option>
          {{foreach from='Ox\Mediboard\PlanningOp\CSejour'|static:"types" item=_type}}
            <option value="{{$_type}}" {{if in_array($_type, $types)}}selected="selected"{{/if}}>
              {{tr}}CSejour.type.{{$_type}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th rowspan="4">{{tr}}CService|pl{{/tr}}</th>
      <td rowspan="4">
        <select name="services_id" size="5" multiple>
          <option value="" {{if !$services_id|@count}}selected="selected"{{/if}}>&mdash; {{tr}}CService.all{{/tr}}</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if in_array($_service->_id, $services_id)}}selected="selected"{{/if}}>
              {{$_service->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{tr}}CUserLog-type-desc{{/tr}}</th>
      <td>
        <select name="type_log">
          <option value="" {{if !$type_log}}selected="selected"{{/if}}>&mdash; {{tr}}common-all|f|pl{{/tr}}</option>
          <option value="create" {{if $type_log == "create"}}selected="selected"{{/if}}>{{tr}}CUserLog.type.create{{/tr}}</option>
          <option value="store" {{if $type_log == "store"}}selected="selected"{{/if}}>{{tr}}CUserLog.type.store{{/tr}}</option>
        </select>
      </td>
    </tr>
    <tr>
      <th>{{tr}}common-Practitioner{{/tr}}</th>
      <td>
        <select name="praticien_id" onchange="this.form.function_id.value = '';" style="width: 15em !important">
          <option value="">&mdash; {{tr}}common-Choice a practitioner{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser selected=$filter->praticien_id list=$praticiens}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{tr}}CPrescriptionGraph-function_id{{/tr}}</th>
      <td>
        <select name="function_id" onchange="this.form.praticien_id.value = '';" style="width: 15em !important">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function selected=$function_id list=$functions}}
        </select>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button type="button" class="search me-primary" onclick="Regulation.searchSejours(this.form);">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="list_sejours_regulation"></div>