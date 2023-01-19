{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=hour_quantum value="dPhospi General nb_hours_trans"|gconf}}
{{assign var="object" value=$observation}}

<script>
  Main.add(function () {
    var form = getForm("editObs");

    var options = null;

    {{if "soins Transmissions blocking_hour"|gconf}}
    options = {
      minHours: "{{$hour-$hour_quantum}}",
      maxHours: "{{$hour+$hour_quantum}}"
    };
    {{/if}}

    var dates = {};
    dates.limit = {
      start: "{{$dnow}}",
      stop:  "{{$dnow}}"
    };
    Calendar.regField(form.date, dates, options);
    {{if "planSoins general use_observation_duree"|gconf}}
      changeTypeObservation(form);
    {{/if}}
  });

  changeTypeObservation = function(form) {
    switch ($V(form.type)) {
      case '':
        var duree_default = '{{"planSoins general duree_observation_normal"|gconf}}';
        break;
      case 'synthese':
        var duree_default = '{{"planSoins general duree_observation_synthese"|gconf}}';
        break;
      case 'communication':
        var duree_default = '{{"planSoins general duree_observation_communication"|gconf}}';
        break;
      default:
        var duree_default = '';
        break;
    }
    $V(form.duree, duree_default);
  }

  checkObservationClose = function (form) {
    var text = $V(form.elements.text).trim();

    if (!text) {
      Control.Modal.close();
      return;
    }

    Modal.confirm($T("common-msg-Do you want to close this window"), {onOK: Control.Modal.close});
  }
</script>

<form name="editObs" method="post">
  {{mb_class object=$observation}}
  {{mb_key   object=$observation}}
  {{mb_field object=$observation field=sejour_id hidden=true}}
  {{mb_field object=$observation field=user_id   hidden=true}}

  <table class="form">
    <tr>
      {{if $observation->_id}}
        <th class="title modify" colspan="10">
          {{mb_include module=system template=inc_object_history}}
          {{tr}}{{$observation->_class}}-title-modify{{/tr}} - <span style="font-weight: bold">{{mb_value object=$patient field=_view}}</span>
        </th>
      {{else}}
        <th class="title me-th-new" colspan="10">
          {{tr}}{{$observation->_class}}-title-create{{/tr}} - <span style="font-weight: bold">{{mb_value object=$patient field=_view}}</span>
        </th>
      {{/if}}
    </tr>

    <tr>
      <th>{{mb_label object=$observation field=date}}</th>
      <td>{{mb_field object=$observation field=date}}</td>
      <th>{{mb_label object=$observation field=degre}}</th>
      <td>{{mb_field object=$observation field=degre typeEnum="radio"}}</td>
      <th>{{mb_label object=$observation field=type}}</th>
      <td>
        <select name="type" {{if "planSoins general use_observation_duree"|gconf}}onchange="changeTypeObservation(this.form);"{{/if}}>
          <option value="" {{if !$observation->type}}selected{{/if}}>{{tr}}Normal{{/tr}}</option>
          <option value="synthese"
                  {{if $observation->type == "synthese"}}selected{{/if}}>{{tr}}CObservationMedicale.type.synthese{{/tr}}</option>
          <option value="communication"
                  {{if $observation->type == "communication"}}selected{{/if}}>{{tr}}CObservationMedicale.type.communication{{/tr}}</option>
        </select>
      </td>
      {{if "planSoins general use_observation_duree"|gconf}}
        <th>{{mb_label object=$observation field=duree}}</th>
        <td>{{mb_field object=$observation field=duree}}</td>
      {{/if}}
      
      <th>{{mb_label object=$observation field=etiquette}}</th>
      <td>{{mb_field object=$observation field=etiquette emptyLabel="Choose"}}</td>
    </tr>

    <tr>
      <td style="padding-bottom:20px;"></td>
    </tr>
    <tr>
      <td colspan="10">
        {{mb_field object=$observation field="text" style="height: 390px;" form="editObs" aidesaisie="validateOnBlur: 0, strict: 0"}}
      </td>
    </tr>

    <tr>
      <td colspan="10" class="button">
        <button type="button" class="{{$observation->_id|ternary:save:add}} singleclick" onclick="submitSuivi(this.form);">
          {{tr}}{{$observation->_id|ternary:Save:Add}}{{/tr}}
        </button>

        <button type="button" class="cancel" onclick="checkObservationClose(this.form);">
          {{tr}}Cancel{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
