{{*
 * @package Mediboard\Sms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigSMS" action="?" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  <table class="form">
    {{assign var="mod" value="sms"}}
    <tr>
      <th class="title" colspan="10">{{tr}}config-{{$mod}}{{/tr}}</th>
    </tr>
    
    {{mb_include module=system template=configure_handler class_handler=CSmsObjectHandler}}
    
    <tr>
      <th class="category" colspan="10">{{tr}}config-traitement-{{$mod}}{{/tr}}</th>
    </tr>
        
    {{mb_include module=system template=inc_config_bool var=server}}
    
    <tr>
      <td class="button" colspan="10">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>