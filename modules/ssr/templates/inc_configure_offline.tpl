{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <td>
      <a class="button search" href="?m={{$m}}&a=offline_plannings_equipements&dialog=1">
        {{tr}}ssr-planning_equipement|pl{{/tr}}
      </a>
      <a class="button search" href="?m={{$m}}&a=offline_plannings_techniciens&dialog=1">
        {{tr}}ssr-planning_reeduc|pl{{/tr}}
      </a>
      <a class="button search" href="?m={{$m}}&a=offline_repartition&dialog=1">
        {{tr}}ssr-repartition_patient{{/tr}}
      </a>
      <a class="button search" href="?m={{$m}}&a=offline_sejours&dialog=1">
        {{tr}}CSejour|pl{{/tr}}
      </a>
    </td>
  </tr>
</table>