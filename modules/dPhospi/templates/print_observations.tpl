{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th style="width: 10%;">
      {{tr}}User{{/tr}}
    </th>
    <th style="width: 10%;">
      {{tr}}Date{{/tr}}
    </th>
    <th>
      {{tr}}Content{{/tr}}
    </th>
  </tr>

  {{foreach from=$obs item=_obs}}
  <tr>
    <td>
      {{$_obs->_ref_user->_view}}
    </td>
    <td>
      {{mb_value object=$_obs field=date}}
    </td>
    <td>
      {{mb_value object=$_obs field=text}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="3" class="empty">
      {{tr}}CObservationMedicale.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>