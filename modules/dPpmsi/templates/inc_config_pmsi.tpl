{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigPmsi" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}
  <table class="form"> 
    {{mb_include module=system template=inc_config_enum var=systeme_facturation values=siemens|}}

    {{mb_include module=system template=inc_config_bool var=server}}

    {{mb_include module=system template=inc_config_enum var=transmission_actes values=pmsi|signature}}

    {{mb_include module=system template=inc_config_enum var=passage_facture values=envoi|reception}}
    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>