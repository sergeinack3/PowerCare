{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    var form = getForm("tools-{{$_tool_class}}-{{$_tool}}");

    form.count.addSpinner({min: 1});
  });
</script>

<form name="tools-{{$_tool_class}}-{{$_tool}}" method="get" action="?"
      onsubmit="return onSubmitFormAjax(this, null, 'tools-{{$_tool_class}}-{{$_tool}}')">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="a" value="do_define_idex_missing" />
  <input type="hidden" name="suppressHeaders" value="1" />

  <table class="main form">
    <tr>
      <th>{{tr}}CExchangeDataFormat-msg-Type{{/tr}}</th>
      <td>
        <select name="exchange_class">
          {{foreach from=$exchanges_classes key=sub_classes item=_child_classes}}
            <optgroup label="{{tr}}{{$sub_classes}}{{/tr}}">
              {{foreach from=$_child_classes item=_class}}
                <option value="{{$_class->_class}}">{{tr}}{{$_class->_class}}{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th></th>
      <td><input type="text" name="count" value="30" size="3" title="Nombre d'échanges à traiter" /></td>
    </tr>

    <tr>
      <td colspan="2">
        <button type="submit" class="change">{{tr}}CEAI-tools-{{$_tool_class}}-{{$_tool}}-button{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>