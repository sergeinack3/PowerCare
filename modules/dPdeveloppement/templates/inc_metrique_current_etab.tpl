{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl main">
  <tr>
    <th width="50%">Type de données</th>
    <th width="50%">Quantité</th>
  </tr>
  {{foreach from=$res_current_etab item=curr_res key=field_res}}
  <tr>
    <td>{{tr}}{{$field_res}}{{/tr}}</td>
    <td>{{$curr_res|integer}}</td>
  </tr>
  {{/foreach}}
</table>