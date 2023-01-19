{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=m value=system}}
{{assign var=class value=CMessage}}

<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this);">

  {{mb_configure module=$m}}

  <table class="form">
    {{mb_include module=system template=inc_config_str var=default_email_from}}
    {{mb_include module=system template=inc_config_str var=default_email_to}}

    <tr>
      <td class="button" colspan="6">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{mb_include module=system template=inc_config_exchange_source source=$message_smtp}}