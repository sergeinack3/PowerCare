{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$sspi->_ref_postes item=_poste}}
  <tr {{if !$_poste->actif}}class="hatching"{{/if}}>
    <td><a href="#1" onclick="Bloc.editPoste('{{$_poste->_id}}', '{{$_poste->sspi_id}}');">{{$_poste}}</a></td>
    <td>{{mb_value object=$_poste field=type}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="2" class="empty">
      {{tr}}CSSPI-back-postes_sspi.empty{{/tr}}
    </td>
  </tr>
{{/foreach}}