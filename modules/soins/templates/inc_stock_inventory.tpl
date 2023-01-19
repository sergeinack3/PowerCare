{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dispensation script=dispensation ajax=true}}

<table class="main me-no-align me-margin-top-8" style="border-collapse: collapse;">
  <tr>
    <td class="narrow">
      <table class="main tbl me-no-align">
        <tr>
          <th class="title" colspan="2">{{tr}}CSejour|pl{{/tr}} ({{$sejours|@count}})</th>
        </tr>
        {{foreach from=$sejours item=_sejour}}
          <tr id="inventaire_{{$_sejour->_id}}">
            <td>
              <a href="#" onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')" onclick="refreshInventorySejour('{{$_sejour->_id}}');">
                {{$_sejour->_ref_patient->_view}}
              </a>
              <span class="compact">
                {{$_sejour->_shortview}}
              </span>
            </td>
          </tr>
        {{foreachelse}}
          <tr>
            <td class="empty" colspan="2">{{tr}}CPatient.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td id="stock_inventory_sejour"></td>
  </tr>
</table>