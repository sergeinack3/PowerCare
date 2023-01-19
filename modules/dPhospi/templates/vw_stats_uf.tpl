{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=dPhospi script=unite_fonctionnelle ajax=true}}

<table class="main">
  <tr>
    <th class="title" colspan="2">Statistiques d'utilisation de l'UF: {{$uf->_view}}</th>
  </tr>
  <tr>
    <th>{{mb_label class=CUniteFonctionnelle field=type}}</th>
    <td>{{mb_value object=$uf field=type}}</td>
  </tr>
</table>

<div id="list_stats_ufs">
  {{mb_include module=dPhospi template=vw_list_stats_uf}}
</div>
