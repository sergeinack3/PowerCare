{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">{{mb_label object=$operation field="codes_ccam" defaultFor="_codes_ccam"}}</th>
    <td colspan="2">
      <input type="hidden" name="_class" value="COperation" />
      {{mb_field object=$operation field=codes_ccam hidden=1 onchange="DHE.operation.updateTokenfield();"}}
      <input type="text" name="_codes_ccam" ondblclick="CCAMSelector.init();" style="width: 12em" class="autocomplete" />
      <div style="display: none; width: 200px !important" class="autocomplete" id="_codes_ccam_auto_complete"></div>
      <button class="add notext" type="button" onclick="DHE.operation.ccam_tokenfield.add(this.form._codes_ccam.value, true);">{{tr}}Add{{/tr}}</button>
      <button type="button" class="search notext" onclick="CCAMSelector.init();">{{tr}}button-CCodeCCAM-choix{{/tr}}</button>
    </td>
  </tr>
  <tr>
    <th>Liste des codes CCAM</th>
    <td id="codes_ccam_area"></td>
  </tr>
</table>