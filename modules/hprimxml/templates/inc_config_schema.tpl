{{*
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-schema" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <th class="category" colspan="2">Global</th>
    </tr>
    {{mb_include module=system template=inc_config_bool var=concatenate_xsd}}
    {{mb_include module=system template=inc_config_str  var=tag_default}}

    <tr>
      <th class="category" colspan="2">Patients</th>
    </tr>
    {{mb_include module=system template=inc_config_bool var=mvtComplet}}

    <tr>
      <th class="category" colspan="2">PMSI</th>
    </tr>

    {{mb_include module=system template=inc_config_enum var=send_diagnostic values=evt_pmsi|evt_serveuretatspatient}}
    {{mb_include module=system template=inc_config_str  var=actes_ngap_excludes}}
    {{mb_include module=system template=inc_config_bool var=send_only_das_diags}}
    {{mb_include module=system template=inc_config_bool var=use_recueil}}

    <tr>
      <td class="button" colspan="10">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>