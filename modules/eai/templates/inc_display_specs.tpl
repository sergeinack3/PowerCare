{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{tr}}CSpecialtyAsip-libelle{{/tr}}</th>
    <th>{{tr}}CSpecialtyAsip-code{{/tr}}</th>
  </tr>
  {{foreach from=$specs item=_spec}}
    <tr>
      <td>{{$_spec->libelle}}</td>
      <td>{{$_spec->code}}</td>
    </tr>
  {{/foreach}}
</table>