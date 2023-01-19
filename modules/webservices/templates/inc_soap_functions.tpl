{{*
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  getEventName = function(button, func) {
	  var method = func.split(/[ \(]/i)[1];
	  $V(getForm('{{$form_name}}').evenement_name, method);
	}

  sendEvent = function(func, exchange_source_guid) {
		var url = new Url("webservices", "ajax_test_send_event");
	  url.addParam("function", func);
	  url.addParam("exchange_source_guid", exchange_source_guid);
	  url.requestModal(800, 350);
	}
</script>

<table class="main tbl">
  <tr>
    <th colspan="2">
      Liste des fonctions disponibles
      <a href="{{$exchange_source->host}}" title="Accès direct">
        (accéder directement au serveur)
      </a>
    </th>
  </tr>
  {{foreach from=$functions item=_function}}
  <tr>
  	<td class="narrow">
  		<button class="compact add notext" onclick="getEventName($(this), '{{$_function}}')"></button>
  		<button class="compact tick notext" onclick="sendEvent('{{$_function}}', '{{$exchange_source->_guid}}')"></button>
	  </td>
    <td class="text">
      {{$_function}}
    </td>
  </tr>
  {{/foreach}}
  {{foreach from=$types item=_type}}
  <tr>
    <td class="text" colspan="2">
      {{$_type}}
    </td>
  </tr>
  {{/foreach}}
</table>