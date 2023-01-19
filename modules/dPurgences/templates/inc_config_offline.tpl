{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Mode offline</h2>

<table class="tbl">
  <tr>
    <td>
      <a class="button search" href="?m={{$m}}&a=print_main_courante&offline=1&dialog=1&_aio=1">
        {{tr}}Main_courante{{/tr}}
      </a>
      <a class="button search" href="?m=hospi&a=vw_bilan_service&token_cat=all&dialog=1&mode_urgences=1&offline=1">
        Bilan
      </a>
    </td>
  </tr>
</table>