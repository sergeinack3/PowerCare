{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table  class="main">
  <tr>
    <td>
      <a class="button new" onclick="Bloc.updateSelectedBloc(); Bloc.editBloc(0);">{{tr}}CBlocOperatoire-title-create{{/tr}}</a>
      <table class="tbl">
        <tr>
          <th>{{mb_title class=CBlocOperatoire field=nom}}</th>
          <th>{{mb_title class=CBlocOperatoire field=type}}</th>
          <th>{{mb_title class=CBlocOperatoire field=tel}}</th>
          <th>{{mb_title class=CBlocOperatoire field=fax}}</th>
          <th>{{mb_title class=CBlocOperatoire field=days_locked}}</th>
          <th>{{tr}}CBlocOperatoire-back-salles{{/tr}}</th>
        </tr>
        {{foreach from=$blocs_list item=_bloc}}
          <tr class="{{if $_bloc->_id == $bloc_id}}selected{{/if}} {{if !$_bloc->actif}}hatching{{/if}}">
            <td>
              <a href="#!" onclick="Bloc.updateSelectedBloc(this.up('tr')); Bloc.editBloc({{$_bloc->_id}})">
                {{mb_value object=$_bloc field=nom}}
              </a>
            </td>
            <td>{{mb_value object=$_bloc field=type}}</td>
            <td>{{mb_value object=$_bloc field=tel}}</td>
            <td>{{mb_value object=$_bloc field=fax}}</td>
            <td>{{mb_value object=$_bloc field=days_locked}}</td>
            <td>
              {{foreach from=$_bloc->_ref_salles item=_salle}}
                <div>{{$_salle}}</div>
                {{foreachelse}}
                <div class="empty">{{tr}}CSalle.none{{/tr}}</div>
              {{/foreach}}
            </td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>