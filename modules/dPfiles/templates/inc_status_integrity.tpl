{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$status}}
  <div class="small-info">
    Cron non paramétré
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="3">
      Progression
    </th>
  </tr>
  <tr>
    <td colspan="3">
      <div class="progressBar">
        <div class="bar" style="width: {{$progression}}%; background: #abe;" >
          <div class="text" style="color: black; text-shadow: 1px 1px 2px white;">{{$progression}}%</div>
        </div>
      </div>
    </td>
  </tr>
</table>

