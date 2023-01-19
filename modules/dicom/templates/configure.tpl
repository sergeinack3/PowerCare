{{*
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="EditConfig-{{$mod}}" action="?m={{$m}}&{{$actionType}}=configure" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}

  <table class="form">
    {{mb_include module=system template=inc_config_str var=implementation_version}}
    {{mb_include module=system template=inc_config_str var=implementation_sop_class}}

    <tr>
      <td class="button" colspan="100">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
