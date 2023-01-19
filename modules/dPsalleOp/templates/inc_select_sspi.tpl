{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title">
      {{tr}}CSSPI-Choose one{{/tr}}
    </th>
  </tr>
  <tr>
    <td>
      {{foreach from=$sspis item=_sspi}}
        <button class="big singleclick" onclick="Control.Modal.close(); window.submitFormTiming('{{$_sspi->_id}}');">
          {{$_sspi->_view}}
        </button>
      {{/foreach}}
    </td>
  </tr>
</table>