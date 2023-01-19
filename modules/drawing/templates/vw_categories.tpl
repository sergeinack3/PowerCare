{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=drawing script=DrawingCategory}}
{{mb_script module=files script=file}}

<style>
  .drawing_file_list img{
    max-width: 120px;
    max-height: 120px;
  }
</style>

<script>
  Main.add(function() {
    refreshList();
  });

  refreshList = function() {
    var oform = getForm('filter_ressources');
    oform.onsubmit();
  };
</script>

<fieldset class="me-align-auto">
  <legend>{{tr}}CDrawingCategory-legend-Filter resource|pl{{/tr}}</legend>
  <form method="get" name="filter_ressources" onsubmit="return onSubmitFormAjax(this, {}, 'result_ressouces')">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="a" value="ajax_list_ressources"/>
    <table class="form me-no-box-shadow">
      <tr>
        <th>{{tr}}User{{/tr}}</th>
        <td>
          <select name="user_id" onchange="$V(this.form.function_id, '', false); this.form.onsubmit();">
            <option value="">&mdash; {{tr}}Select{{/tr}}</option>
            {{foreach from=$users item=_user}}
              <option value="{{$_user->_id}}">{{$_user}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <th>{{tr}}Function{{/tr}}</th>
        <td>
          <select name="function_id" onchange="$V(this.form.user_id, '', false); this.form.onsubmit();">
            <option value="">&mdash; {{tr}}Select{{/tr}}</option>
            {{foreach from=$functions item=_function}}
              <option value="{{$_function->_id}}">{{$_function}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    </table>
  </form>
</fieldset>
<div id="result_ressouces" class="me-padding-8"></div>
