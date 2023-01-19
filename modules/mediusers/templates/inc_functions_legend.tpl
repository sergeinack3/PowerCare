{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table id="mediuser_function_legend" style="display:none; width: 450px" class="tbl">
  <tr>
    <td class="narrow">
      <button type="button" class="text-button mediuser-flat-button mediuser-flat-button-g opacity-50" title="{{tr}}CGroups{{/tr}}">
        <i class="fas fa-building"></i>
      </button>
    </td>
    <td>{{tr}}CMediuser-Functions groups without perms{{/tr}}</td>
  </tr>
  <tr>
    <td class="narrow">
      <button type="button" class="text-button mediuser-flat-button mediuser-flat-button-g" title="{{tr}}CGroups{{/tr}}">
        <i class="fas fa-building"></i>
      </button>
    </td>
    <td>{{tr}}CMediuser-Functions groups with perms{{/tr}}</td>
  </tr>
  <tr>
    <td class="narrow">
      <button class="text-button mediuser-flat-button mediuser-flat-button-pf" title="{{tr}}CMediusers-function_id{{/tr}}">P</button>
    </td>
    <td>{{tr}}CMediuser-Functions primary{{/tr}}</td>
  </tr>
  <tr>
    <td class="narrow">
      <button type="button" title="{{tr}}CSecondaryFunction{{/tr}}"
              class="text-button{{if !$can->edit}} mediuser-flat-button{{/if}} mediuser-button-sf">S</button>
    </td>
    <td>{{tr}}CMediuser-Functions secondary{{/tr}}{{if $can->edit}} - {{tr}}CMediuser-Functions click to upgrade{{/tr}}{{/if}}</td>
  </tr>
  <tr>
    <td class="narrow">
      <button type="button" class="text-button mediuser-flat-button mediuser-flat-button-wp" title="{{tr}}Permission{{/tr}}">A</button>
    </td>
    <td>{{tr}}CMediuser-Functions not attributed but with perms{{/tr}}</td>
  </tr>
</table>