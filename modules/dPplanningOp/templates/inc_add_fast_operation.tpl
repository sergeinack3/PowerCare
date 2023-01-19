{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm("addFastOp");

    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("praticiens", '1')
      .addParam("input_field", "chir_id_view")
      .autoComplete(form.chir_id_view, null, {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          $V(form.chir_id_view, selected.down('.view').innerHTML);
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.chir_id, id);
        }
      }
    );

    var dates = {
      limit: {
        start: "{{$date_min}}",
        stop:  "{{$date_max}}"
      }
    };
    Calendar.regField(form.date, dates);
    Calendar.regField(form._time_urgence, null, {datePicker:false, timePicker:true});
  });

  completeButton = function(operation_id) {
    var button = $("fast_operation_{{$operation->sejour_id}}");
    if (button) {
      button.update("Modifier l'intervention hors plage");
      button.onclick = function () {
        addFastOperation(this, operation_id);
      };
    }
    Control.Modal.close();
  };
</script>

<form name="addFastOp" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_operation_aed" />
  {{mb_key object=$operation}}
  {{mb_field object=$operation field=sejour_id hidden=true}}
  <input type="hidden" name="callback" value="completeButton" />
  <table class="form">
    <tr>
      <th>
        {{mb_label object=$operation field=chir_id}}
      </th>
      <td>
        {{mb_field object=$operation field="chir_id" hidden=hidden}}
        <input type="text" name="chir_id_view" class="autocomplete" style="width:15em;" value="{{$operation->_ref_chir}}"/>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$operation field=libelle}}
      </th>
      <td>
        {{mb_field object=$operation field=libelle form=addFastOp
                   autocomplete="true,1,50,true,true" min_length=2 inputWidth="50%"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$operation field=cote}}
      </th>
      <td>
        {{mb_field object=$operation field=cote}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$operation field=_time_op class=notNull}}
      </th>
      <td>
        {{if !"dPplanningOp COperation only_admin_can_change_time_op"|gconf ||
              @$modules.dPplanningOp->_can->admin || $app->_ref_user->isAdmin()}}
          {{mb_field object=$operation field=_time_op form=addFastOp class=notNull}}
        {{else}}
          {{mb_value object=$operation field=_time_op}}
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$operation field=date}}
      </th>
      <td>
        <input type="hidden" name="date" value="{{$operation->date}}" class="date notNull" />

        à

        <input type="text" class="time" name="_time_urgence_da" readonly value="{{$operation->_time_urgence|date_format:"%H:%M"}}" />
        <input name="_time_urgence" class="notNull time" type="hidden" value="{{$operation->_time_urgence}}" />
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$operation field=urgence typeEnum=checkbox}}
      </th>
      <td>
        {{mb_field object=$operation field=urgence typeEnum=checkbox}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="{{if $operation->_id}}save{{else}}new{{/if}}" onclick="this.form.onsubmit();">
          {{tr}}{{if $operation->_id}}Save{{else}}Create{{/if}}{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
