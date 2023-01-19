{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=import script=import_mapping ajax=true}}

{{mb_default var=by_patient value=false}}
{{mb_default var=import_type value=null}}
{{mb_default var=import_action value='do_import_fw'}}

<script>
  submitImport = function (form) {
    $V(form.import_campaign_id, $V($('import-campaign-select')));

    form.onsubmit();
  }
</script>


<form name="import-{{$type}}" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-import-{{$type}}')">
  <input type="hidden" name="m" value="{{$module}}"/>
  <input type="hidden" name="dosql" value="{{$import_action}}"/>
  <input type="hidden" name="import_type" value="{{$import_type}}"/>
  <input type="hidden" name="type" value="{{$type}}"/>
  <input type="hidden" name="import_campaign_id" value=""/>

  <table class="main form">
    <tr>
      <th>{{tr}}Start{{/tr}}</th>
      <td><input type="number" name="start" value="{{$last_id}}"> / <span id="{{$type}}-count">{{$total}}</span></td>
    </tr>

    <tr>
      <th>{{tr}}Step{{/tr}}</th>
      <td><input type="number" name="step" value="100"/></td>
    </tr>

    {{if $by_patient}}
      <tr>
        <th>{{tr}}CPatient{{/tr}}</th>
        <td>
          <input type="text" name="patient_id" value=""/>
          <button class="change notext" type="button" onclick="ImportMapping.refreshCount('{{$type}}', '{{$module}}');"
        </td>
      </tr>
    {{/if}}

    <tr>
      <th>{{tr}}Update{{/tr}}</th>
      <td><input type="checkbox" name="update" value="1"/></td>
    </tr>

    <tr>
      <th>{{tr}}Auto{{/tr}}</th>
      <td><input type="checkbox" name="continue" value="1"/></td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="import" onclick="submitImport(this.form)">{{tr}}Import{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-import-{{$type}}"></div>
