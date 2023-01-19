{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>
      <a class="button new" onclick="Bloc.updateSelectedSalle(); Bloc.editSalle(0)">{{tr}}CSalle-title-create{{/tr}}</a>
      <table class="tbl">
        {{foreach from=$blocs_list item=_bloc}}
          <tr>
            <th class="">{{$_bloc->nom}}</th>
          </tr>
          {{foreach from=$_bloc->_ref_salles item=_salle}}
            <tr class="{{if $_salle->_id == $salle_id}}selected{{/if}} {{if !$_salle->actif}}hatching{{/if}}">
              <td>
                <a href="#!" onclick="Bloc.updateSelectedSalle(this.up('tr')); Bloc.editSalle({{$_salle->_id}})" {{if $_salle->color}} style="border-left: 4px solid #{{$_salle->color}}; padding-left: 4px;"{{/if}}>
                  {{$_salle}}
                </a>
              </td>
            </tr>
            {{foreachelse}}
            <tr><td class="empty">{{tr}}CSalle.none{{/tr}}</td></tr>
          {{/foreach}}
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>