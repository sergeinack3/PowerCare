{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=planning ajax=true}}
{{mb_script module=ssr script=planning ajax=true}}
<script>
  Main.add(function () {
    PlanningSejour.changeViewPlanningStay();
    Calendar.regField(getForm("changeDatePlaning").debut, null, {noView: true});
  });
</script>
<table class="main me-no-align">
  <tr>
    <th>
      <div id="patient_banner">
          {{mb_include module=soins template=inc_patient_banner object=$sejour patient=$sejour->_ref_patient}}
      </div>
    </th>
  </tr>
  <tr>
    <th>
      <form action="?" name="changeDatePlaning" method="get" onsubmit="return PlanningSejour.changeDate(this);">
        <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
        <button type="button" class="left notext" onclick="$V($(this).getSurroundingForm().debut, '{{$precedent}}')"></button>
          {{tr}}date.From{{/tr}} {{$debut|date_format:$conf.date}} {{tr}}date.to{{/tr}} {{$fin|date_format:$conf.date}}
        <input type="hidden" name="debut" class="date" value="{{$debut}}" onchange="return PlanningSejour.changeDate(this.form);" />
        <button type="button" class="right notext" onclick="$V($(this).getSurroundingForm().debut, '{{$suivant}}')"></button>
      </form>

      <select class="fa fa-calendar calendar-view"
              style="position: absolute; right: 0;"
              data-stay-id="{{$sejour->_id}}"
              data-view="week"
              data-guid="{{$sejour->_guid}}">
        <option value="week" {{if !$isMonth}}selected{{/if}}>{{tr}}CPlanningOp-view-weekly{{/tr}}</option>
        <option value="month" {{if $isMonth}}selected{{/if}}>{{tr}}CPlanningOp-view-monthly{{/tr}}</option>
      </select>
    </th>
  </tr>
  <tr>

  </tr>
  <tr>
    <td>
      <div id="planning-sejour">
          {{if $isMonth}}
              {{mb_include module=system template=calendars/vw_month print=true calendar=$planning}}
          {{else}}
              {{mb_include module=system template=calendars/vw_week print=true}}
          {{/if}}
      </div>
    </td>
  </tr>
</table>