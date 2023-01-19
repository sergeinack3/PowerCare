{{*
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigSMP" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{assign var="mod" value="smp"}}
    <tr>
      <th class="title" colspan="2">{{tr}}config-{{$mod}}{{/tr}}</th>
    </tr>

    {{mb_include module=system template=configure_handler class_handler=CSmpObjectHandler}}

    <tr>
      <th class="category" colspan="2">{{tr}}config-traitement-{{$mod}}{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_str var=tag_visit_number}}

    {{mb_include module=system template=inc_config_bool var=create_object_by_vn}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>