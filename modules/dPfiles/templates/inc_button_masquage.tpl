{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="search" onclick="Modal.open('masquage-area-{{$docitem->_guid}}');">
    {{tr}}CDocumentItem-Masquage{{/tr}}
</button>

<style>
  #masquage-area-{{$docitem->_guid}} label{
    background-color: transparent !important;
  }
</style>

<div id="masquage-area-{{$docitem->_guid}}" style="display: none;">
  <table class="form">
    <tr>
      <td>
          {{mb_field object=$docitem field="masquage_praticien" typeEnum=checkbox}}
          {{mb_label object=$docitem field="masquage_praticien" typeEnum=checkbox}}
      </td>
      <td>
          {{mb_field object=$docitem field="masquage_patient" typeEnum=checkbox}}
          {{mb_label object=$docitem field="masquage_patient" typeEnum=checkbox}}
      </td>
      <td>
          {{mb_field object=$docitem field="masquage_representants_legaux" typeEnum=checkbox}}
          {{mb_label object=$docitem field="masquage_representants_legaux" typeEnum=checkbox}}
      </td>
    </tr>
    <tr>
      <td colspan="6" class="button">
        <button type="button" class="tick" onclick="this.form.onsubmit(); Control.Modal.close();">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>
