{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=multi_label value="dPplanningOp COperation multiple_label"|gconf}}
{{mb_script module=planningOp script=operation ajax=true}}

<form name="infosInterv" action="?" method="post" onsubmit="onSubmitFormAjax(this, Control.Modal.close)">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$operation}}

  <table class="form">
    <tr>
      <th>{{mb_label object=$operation field="libelle"}}</th>
      <td>
        {{mb_value object=$operation field="libelle" form="infosInterv" readonly=true}}
    </tr>
    <tr>
      <th>{{tr}}CProtocole.libelle_comp{{/tr}}</th>
      <td>
        <input type="text" name="_libelle_comp" placeholder="{{tr}}CProtocole.libelle_comp{{/tr}}">
      </td>
    </tr>
   <tr>
     <th>{{mb_label object=$operation field="cote"}}</th>
     <td>{{mb_field object=$operation field="cote"}}</td>
   </tr>
   <tr>
     <td colspan="2" class="button">
       <button type="button" class="save" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
     </td>
   </tr>
  </table>
</form>
{{if $multi_label}}
  <script>
    Main.add(function () {
      Libelle.refreshlistLibelle('{{$operation->_id}}');
    });
  </script>
  <div id="libelles">
  </div>
{{/if}}
