{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai    script=exchange_data_format ajax=true}}
{{mb_script module=system script=sender_fs            ajax=true}}

<table class="tbl">
  <tr>
    <th colspan="2" class="category">{{tr}}CSenderFileSystem-utilities{{/tr}}</th>
  </tr>  
  
  <tr>
    <td class="narrow">
      <button type="button" class="fas fa-exchange-alt" onclick="SenderFS.createExchanges('{{$actor->_guid}}');">
        {{tr}}CSenderFileSystem-utilities_create_exchanges{{/tr}}
      </button> 
    </td>
  </tr>
  
  <tr>
    <td class="narrow">
      <button type="button" class="fa fa-check" onclick="ExchangeDataFormat.treatmentExchanges('{{$actor->_guid}}');">
        {{tr}}CExchangeDataFormat-utilities_treatment_exchanges{{/tr}}
      </button> 
    </td>
  </tr>
   
  <tr>
    <td class="narrow">
      <button type="button" class="fa fas fa-sync" onclick="SenderFS.dispatch('{{$actor->_guid}}');">
        {{tr}}CSenderFileSystem-utilities_dispatch{{/tr}}
      </button> 
    </td>
  </tr>
</table>
