{{*
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="xds" script="xds_document" ajax=true}}

<div>
  <fieldset class="me-margin-bottom-8">
    <legend>{{tr}}XDS-Search_choose_patient{{/tr}}</legend>

    <form name="choosePatient" action="?m={{$m}}" method="get">
      <input type="hidden" name="patient_id" value="" />

      <table class="layout form me-no-box-shadow">
        <tr>
          <td style="width: 20%">
            <input type="text" name="_patient_view" style="width: 200px" value="{{$patient->_view}}" readonly="readonly" />
          </td>
          <td>
            <input type="text" name="_seek_patient" style="width: 13em;" placeholder="{{tr}}fast-search{{/tr}}" "autocomplete"
            onblur="$V(this, '')"/>

            <script>
              Main.add(function () {
                var form = getForm("choosePatient");
                var url = new Url("system", "ajax_seek_autocomplete");
                url.addParam("object_class", "CPatient");
                url.addParam("field", "patient_id");
                url.addParam("view_field", "_patient_view");
                url.addParam("input_field", "_seek_patient");
                url.autoComplete(form.elements._seek_patient, null, {
                  minChars:           3,
                  method:             "get",
                  select:             "view",
                  dropdown:           false,
                  width:              "300px",
                  afterUpdateElement: function (field, selected) {
                    var patient_id = selected.getAttribute("id").split("-")[2];
                    $V(field.form.patient_id, patient_id);
                    $V(getForm('filter_search_documents').patient_id, patient_id);
                    $V(field.form.elements._patient_view, selected.down('.view').innerHTML);
                    $V(field.form.elements._seek_patient, "");
                  }
                });
              });

              var patient_id = $V(getForm('choosePatient').patient_id);
            </script>
          </td>
        </tr>
      </table>
    </form>
  </fieldset>
</div>

<div>
  <form method="post" name="filter_search_documents" onsubmit="return XDSDocument.searchDocument(this)">
    <input type="hidden" name="patient_id" value="">

    <fieldset class="me-margin-bottom-8">
      <legend>{{tr}}XDS-Search_choose_receiver{{/tr}}</legend>

      <table class="layout form me-no-box-shadow">
        <tr>
          <th style="width: 20%">{{tr}}CReceiverHL7v3{{/tr}}</th>
          <td>
            <select name="receiver_hl7v3_id">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$receivers_hl7v3 item=_receiver_hl7v3}}
                <option value="{{$_receiver_hl7v3->_id}}">{{$_receiver_hl7v3->_view}}</option>
              {{/foreach}}
            </select>
          </td>
        </tr>
      </table>
    </fieldset>

    <fieldset class="me-margin-bottom-8">
      <legend>{{tr}}XDS-Search_by_uuid{{/tr}}</legend>

      <table class="layout form me-no-box-shadow">
        <tr>
          <th style="width: 20%">UUID</th>
          <td>
            <input type="text" name="uuid" style="width: 200px" />
          </td>
        </tr>
      </table>
    </fieldset>

    <fieldset class="me-margin-bottom-8">
      <legend>{{tr}}Advanced-Search{{/tr}}</legend>
      <table class="layout form me-no-box-shadow">
        <tr>
          <th style="width: 20%">{{mb_label class="CDMPFile" field="_date_min_submit"}}</th>
          <td>
            {{mb_field class="CXDSFile" field="_date_min_submit" register=true form="filter_search_documents"
            onchange="if(this.value != ''){\$('form_document').hide()}else{\$('form_document').show()}"}}
            {{mb_label class="CXDSFile" field="_date_max_submit"}}
            {{mb_field class="CXDSFile" field="_date_max_submit" register=true form="filter_search_documents"}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label class="CXDSFile" field="_date_min_event"}}</th>
          <td>
            {{mb_field class="CXDSFile" field="_date_min_event" register=true form="filter_search_documents"
            onchange="if(this.value != ''){\$('form_submission').hide()}else{\$('form_submission').show()}"}}
            {{mb_label class="CXDSFile" field="_date_max_event"}}
            {{mb_field class="CXDSFile" field="_date_max_event" register=true form="filter_search_documents"}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label class="CXDSFile" field="type_document"}}</th>
          <td>
            <select multiple name="_type_doc[]">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$types_code key=_code item=_name}}
                <option value="{{$_code}}">{{$_name}} ({{$_code}})</option>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <td class="button" colspan="2">
            <button class="search" type="submit">{{tr}}Search{{/tr}}</button>
          </td>
        </tr>
      </table>
    </fieldset>
  </form>
</div>

<div id="result_search_documents"></div>