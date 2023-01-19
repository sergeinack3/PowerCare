{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-Sender" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}
  <table class="form">
    <tr>
      <th class="category" colspan="2">{{tr}}Purge{{/tr}}</th>
    </tr>
    {{assign var="class" value="CExtractPassages"}}
    <tr>
      <th class="section" colspan="2">{{tr}}{{$class}}{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_num var=purge_probability numeric=true}}
    {{mb_include module=system template=inc_config_num var=purge_empty_threshold numeric=true}}
    {{mb_include module=system template=inc_config_num var=purge_delete_threshold numeric=true}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
