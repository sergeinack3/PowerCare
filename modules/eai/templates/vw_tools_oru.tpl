{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function nextOru(){
    var form = getForm("tools-oru");

    if (!$V(form["continue"])) {
      return;
    }

    form.onsubmit();
  }
</script>

<form name="tools-oru" method="get" action="?"
      onsubmit="return onSubmitFormAjax(this, null, 'tools-oru')">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="a" value="ajax_tools_oru" />
  <input type="hidden" name="suppressHeaders" value="1" />

  <table class="main form">
    <tr>
      <th>{{mb_label class=CExchangeDataFormat field="_date_min"}}</th>
      <td>
        <input class="dateTime notNull" type="hidden" name="date_min" value="{{$date_min}}" /> <br />
        <script type="text/javascript">
          Main.add(function () {
            Calendar.regField(getForm('tools-oru').date_min);
          });
        </script>
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=CExchangeDataFormat field="_date_max"}}</th>
      <td>
        <input class="dateTime notNull" type="hidden" name="date_max" value="{{$date_max}}" /> <br />
        <script type="text/javascript">
          Main.add(function () {
            Calendar.regField(getForm('tools-oru').date_max);
          });
        </script>
      </td>
    </tr>
    <tr>
      <th>Connecteur</th>
      <td>
        <select name="sender_guid">
          {{foreach from=$senders item=_sender}}
            <option value="{{$_sender->_guid}}" >{{$_sender->nom}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>Nombre d'échanges à modifier</th>
      <td><input type="text" name="limit" value="30" size="3" title="Nombre d'échanges à modifier" /></td>
    </tr>
    <tr>
      <th>Rejouer les échanges qui ont été déjà rejoués moins de </th>
      <td><input type="number" name="reprocess" value="1" size="1" title="Rejouer les échanges qui ont été déjà rejoués moins de" /></td>
    </tr>
    <tr>
      <th>Appliquer le process sur un échange en particulier </th>
      <td><input type="number" name="message_id"  title="Appliquer le process sur un échange en particulier" /></td>
    </tr>
    <tr>
      <th></th>
      <td><label><input type="checkbox" checked name="blank" value="1" title="Essai à blanc" /> Essai à blanc</label></td>
    </tr>
    <tr>
      <th></th>
      <td><label><input type="checkbox" name="continue" value="1" title="Automatique" /> Automatique</label></td>
    </tr>
    <tr>
      <td colspan="2">
        <button type="submit" class="change">Lancer</button>
      </td>
    </tr>
  </table>
</form>


<div id="tools-oru"></div>
