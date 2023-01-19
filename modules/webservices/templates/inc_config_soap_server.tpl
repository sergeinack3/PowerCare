{{*
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=webservices script=soap}}

<table class="form">
  <tr>
    <th class="title">
      {{tr}}config-soap-server{{/tr}}
    </th>
  </tr>

  <tr>
    <td>
      <form name="editConfig-webservices" action="?" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_configure module=$m}}
        <table class="form">

          {{mb_include module=system template=inc_config_str var=wsdl_root_url}}
          {{mb_include module=system template=inc_config_enum var=soap_server_encoding values=UTF-8|ISO-8859-1}}

          <tr>
            <td class="button" colspan="10">
              <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>

  <tr>
    <th class="title">
      {{tr}}config-exchange-source{{/tr}}
    </th>
  </tr>
  <tr>
    <td> {{mb_include module=system template=inc_config_exchange_source source=$mb_soap_server}} </td>
  </tr>
</table>