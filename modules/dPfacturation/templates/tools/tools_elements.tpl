{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture ajax=true}}
{{mb_default var=js_function         value="showElements"}}
{{mb_default var=tool_lib            value="bills"}}
{{mb_default var=element_to_factures value=false}}
<script>
  Main.add(
    function() {
      Calendar.regField(getForm('tools_elements').date_min);
      Calendar.regField(getForm('tools_elements').date_max);
    }
  );
</script>
<form name="tools_elements" method="get"
      onsubmit="return FactuTools.{{$js_function}}(
          {
            date_min     : this.date_min.value,
            date_max     : this.date_max.value,
            praticien_id : this.praticien_id.value,
            autoClose    : true
          }
        );">
  <table class="form tbl">
    <tr>
      <th class="category" colspan="3">{{tr}}Filter{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <label>
          {{tr}}common-Start date{{/tr}}
          <input type="hidden" name="date_min" value="{{$date_min}}"/>
        </label>
      </td>
      <td>
        <label>
          {{tr}}common-End date{{/tr}}
          <input type="hidden" name="date_max" value="{{$date_max}}"/>
        </label>
      </td>
      <td>
        <label>
          {{tr}}common-Practitioner{{/tr}}
          <select name="praticien_id">
            <option value="-1">&mdash;{{tr}}All{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$praticiens selected=$praticien_id}}
          </select>
        </label>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="3">
        <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
        {{unique_id var=help_container}}
        <button type="button" class="help" style="float:right" onclick="Modal.open('help_{{$help_container}}')">
          {{tr}}Help{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
<table class="tbl">
  {{foreach from=$elements_list key=_class item=_elements}}
    <tr>
      <th class="title" colspan="3">{{tr}}{{$_class}}{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}Date{{/tr}}</th>
      <th>{{tr}}CPatient{{/tr}}</th>
      <th class="narrow"></th>
    </tr>
    <tbody id="tt_{{$_class}}_container">
      {{mb_include module=facturation template=tools/tools_elements_by_class
                   elements=$_elements page=0 element_class=$_class total=$totaux.$_class}}
    </tbody>
  {{/foreach}}
</table>
<div style="display: none; width: 200px;" id="help_{{$help_container}}">
  <table class="form">
    <tr>
      <th class="title">
        {{tr}}Help{{/tr}}
        <button type="button" class="cancel notext" style="float:right" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </th>
    </tr>
    <tr>
      <td>
        <p>{{tr}}Facturation-tools-error-{{$tool_lib}}-help{{/tr}}</p>
      </td>
    </tr>
  </table>
</div>