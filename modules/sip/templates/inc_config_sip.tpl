{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigSip" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{assign var="mod" value="sip"}}
    <tr>
      <th class="title" colspan="2">{{tr}}config-{{$mod}}{{/tr}}</th>
    </tr>

    {{mb_include module=system template=configure_handler class_handler=CSipObjectHandler}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>