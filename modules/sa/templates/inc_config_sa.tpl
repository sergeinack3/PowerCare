{{*
 * @package Mediboard\Sa
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigSA" action="?m=sa&a=configure" method="post" onsubmit="return checkForm(this)">
  {{mb_configure module=$m}}
  <table class="form">
    {{assign var="mod" value="sa"}}
    
    {{mb_include module=system template=configure_handler class_handler=CSaObjectHandler}}
    {{mb_include module=system template=configure_handler class_handler=CSaEventObjectHandler}}
    
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