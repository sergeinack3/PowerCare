{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$sspis_list item=_sspi}}
  <tr {{if $_sspi->_id == $sspi_id}}class="selected"{{/if}}>
    <td><a href="#!" onclick="Bloc.updateSelectedSSPI(this.up('tr')); Bloc.editSSPI({{$_sspi->_id}})">{{$_sspi}}</a></td>
    <td {{if !$_sspi->_ref_blocs|@count}}class="empty"{{/if}}>
      {{if $_sspi->_ref_blocs|@count}}
        <ul>
          {{foreach from=$_sspi->_ref_blocs item=_bloc}}
            <li>{{$_bloc->_view}}</li>
          {{/foreach}}
        </ul>
      {{else}}
        {{tr}}CBlocOperatoire.none{{/tr}}
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="2" class="empty">
      {{tr}}CSSPI.none{{/tr}}
    </td>
  </tr>
{{/foreach}}