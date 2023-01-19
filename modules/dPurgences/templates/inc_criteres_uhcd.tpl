{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCriteres-{{$rpu->_id}}" method="post">
  {{mb_key object=$rpu}}
  {{mb_class object=$rpu}}

  <table class="tbl">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CRPU-Criteres validation UHCD{{/tr}}
      </th>
    </tr>
    {{foreach from='Ox\Mediboard\Urgences\CRPU'|static:"criteres_uhcd" item=_critere}}
    <tr>
      <td>
        {{mb_label object=$rpu field=$_critere}}
      </td>
      <td>
        {{mb_field object=$rpu field=$_critere}}
      </td>
    </tr>
    {{/foreach}}

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="tick singleclick" onclick="Urgences.valideCriteres(this.form);">
          {{tr}}Validate{{/tr}}
        </button>

        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
