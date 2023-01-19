{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=brisDeGlace}}

<script>
  Main.add(function() {
    var oform = getForm("list_bris");
    Calendar.regField(oform.date_start);
    Calendar.regField(oform.date_end);
    oform.onsubmit();

    new Url("system", "ajax_seek_autocomplete")
    .addParam("object_class", "CPatient")
    .addParam("field", "patient_id")
    .addParam("view_field", "_patient_view")
    .addParam("input_field", "_seek_patient")
    .autoComplete(oform.elements._seek_patient, null, {
      minChars:           3,
      method:             "get",
      select:             "view",
      dropdown:           false,
      width:              "300px",
      afterUpdateElement: function (field, selected) {
        var view = selected.down('.view');
        $V(oform.patient_id, selected.get('guid').split('-')[1]);
        $V(oform._patient_view, view.innerHTML);
        $V(oform._seek_patient, '');
      }
    });
  });
</script>

<form name="list_bris" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result_bris')">
  <input type="hidden" name="page" value="0"/>
  <input type="hidden" name="m" value="admin"/>
  <input type="hidden" name="a" value="ajax_search_bris_by_user" />

  <table class="me-w100">
    <tr>
      <td class="me-text-align-center">
        <select name="object_class" onchange="BrisDeGlace.changeContext(this.value, this.form)">
          <option value="">&mdash; {{tr}}Type|pl{{/tr}}</option>
          <option value="CSejour" selected="selected">{{tr}}CSejour|pl{{/tr}}</option>
        </select>

      <div id="patientAutocomplete" style="display: inline-block">
        <input type="hidden" name="patient_id" onchange="this.form.onsubmit()"/>
        <input type="text" name="_patient_view" style="width: 15em" value="{{$patient->_view}}"
               readonly="readonly"/>
        <input type="text" name="_seek_patient" style="width: 13em;" value=""
               placeholder="{{tr}}fast-search{{/tr}} {{tr}}CPatient{{/tr}}" autocomplete onblur="$V(this, '')"/>
        <button type="button" class="cancel notext me-tertiary me-dark"
                onclick="$V(this.form.patient_id, '');$V(this.form._patient_view, '')">
        </button>
      </div>

        <label>{{tr}}common-Start{{/tr}} :
          <input type="hidden" name="date_start" value="{{$date_start}}" onchange="this.form.onsubmit();"/>
        </label>
        <label>{{tr}}end{{/tr}} :
          <input type="hidden" name="date_end" value="{{$date_end}}" onchange="this.form.onsubmit();"/>
        </label>

        <button class="change notext">{{tr}}Refresh{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl">
  <thead>
    <tr>
      <th>{{mb_title object=$bris field=user_id}}</th>
      <th>{{mb_title object=$bris field=object_id}}</th>
      <th>{{mb_title object=$bris field=comment}}</th>
      <th class="narrow">{{mb_title object=$bris field=date}}</th>
    </tr>
  </thead>
  <tbody id="result_bris"></tbody>
</table>
