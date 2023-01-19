{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="2">{{tr}}CSSPI-back-postes_sspi{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CPosteSSPI-nom{{/tr}}</th>
  </tr>

  {{foreach from=$postes_preop item=_poste}}
    <tr>
      <td><a href="#1" onclick="Bloc.editPoste('{{$_poste->_id}}', null, 1);">{{$_poste}}</a></td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">
          {{tr}}CSSPI-back-postes_sspi.empty{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>