{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    <td class="halfPane">
        <span>
          <span class="type_item circled">{{tr}}CUser-user_birthday{{/tr}}</span>
          &mdash; {{mb_value object=$list[0] field=naissance}} ({{mb_value object=$list[0] field=_age}})
        </span>
      <br />
    </td>
  </tr>
</table>
