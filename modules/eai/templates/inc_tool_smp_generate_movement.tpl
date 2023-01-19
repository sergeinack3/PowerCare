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

    Calendar.regField(form.elements.date_minimum);
    Calendar.regField(form.elements.date_maximum);
  });
</script>

<form name="tools-{{$_tool_class}}-{{$_tool}}" method="get" action="?"
      onsubmit="return onSubmitFormAjax(this, null, 'tools-{{$_tool_class}}-{{$_tool}}')">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="a" value="ajax_create_mouvement" />
  <input type="hidden" name="action" value="" />

  <table class="main form">
    <tr>
      <th>{{mb_label class=CExchangeDataFormat field="_date_min"}}</th>
      <td>
        <input class="dateTime notNull" type="hidden" name="date_minimum" value="{{$date_min}}" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=CExchangeDataFormat field="_date_max"}}</th>
      <td>
        <input class="dateTime notNull" type="hidden" name="date_maximum" value="{{$date_max}}" />
      </td>
    </tr>
    <tr>
      <th>{{tr}}CInteropReceiver{{/tr}}</th>
      <td>
        <select name="receiver_guid">
          {{foreach from=$receivers item=_receivers key=_class}}
            <optgroup label="{{tr}}{{$_class}}{{/tr}}">
              {{foreach from=$_receivers item=_receiver}}
                <option value="{{$_receiver->_guid}}">{{$_receiver}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>Mouvement</th>
      <td>
        <label for="movement_">Défaut</label>
        <input type="radio" name="movement" value="" checked />
        <label for="movement_A05">A05</label>
        <input type="radio" name="movement" value="A05" />
        <label for="movement_A01">A01/A04</label>
        <input type="radio" name="movement" value="A01" />
        <label for="movement_A03">A03</label>
        <input type="radio" name="movement" value="A03" />
      </td>
    </tr>
    <tr>
      <th>Nombre</th>
      <td><input type="text" name="count" value="30" size="3" title="Nombre d'échanges à traiter" /></td>
    </tr>
    <tr>
      <th>Automatique</th>
      <td><input type="checkbox" name="continue" value="1" title="Automatique" /></td>
    </tr>
    <tr>
      <td colspan="2">
        <button type="button" class="new" onclick="$V(this.form.action, 'generate'); this.form.onsubmit()">
          {{tr}}CEAI-tools-{{$_tool_class}}-generate{{/tr}}
          </button>
      </td>
    </tr>
  </table>
</form>