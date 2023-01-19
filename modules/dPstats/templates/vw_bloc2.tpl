{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $display_form}}
  <script>
    function getSpreadSheet() {
      var form = document.bloc;
      var url = new Url('stats', 'vw_bloc2', 'raw');
      url.addParam('mode', 'csv');
      url.addElement(form.bloc_id);
      url.addElement(form.deblistbloc);
      url.addElement(form.finlistbloc);
      url.addElement(form.type);
      url.addElement(form.show_constantes);
      url.popup(550, 300, 'statsBloc');
    }

    Main.add(function () {
      Calendar.regField(getForm("bloc").deblistbloc);
      Calendar.regField(getForm("bloc").finlistbloc);
    });
  </script>
  <form name="bloc" action="?" method="get" onsubmit="return checkForm(this)">
    <input type="hidden" name="m" value="dPstats" />
    <table class="main form">
      <tr>
        <th colspan="5" class="title">Tableau d'activité du bloc sur une journée</th>
      </tr>

      <tr>
        <td class="button" rowspan="4" style="width: 1%">
          <img src="images/pictures/spreadsheet.png" title="Télécharger le fichier CSV" onclick="getSpreadSheet()" />
        </td>

        <td class="button" rowspan="4" style="width: 10%">
          <div class="small-info">Cliquer sur l'icône pour télécharger les données au format CSV.</div>
        </td>

        <th><label for="deblistbloc" title="Date de début">Du</label></th>
        <td>
          <input type="hidden" name="deblistbloc" class="notNull date" value="{{$deblist}}" />
        </td>
      </tr>

      <tr>
        <th><label for="finlistbloc" title="Date de début">Au</label></th>
        <td>
          <input type="hidden" name="finlistbloc" class="notNull date" value="{{$finlist}}" />
        </td>
      </tr>

      <tr>
        <th><label for="bloc_id" title="Bloc opératoire">Bloc</label></th>

        <td colspan="4">
          <select name="bloc_id">
            <option value="">&mdash; {{tr}}CBlocOperatoire.select{{/tr}}</option>
            {{foreach from=$blocs item=_bloc}}
              <option value="{{$_bloc->_id}}" {{if $_bloc->_id == $bloc->_id }}selected="selected"{{/if}}>
                {{$_bloc->nom}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>

      <tr>
        <th><label for="type" title="Type">Type</label></th>

        <td colspan="4">
          <select name="type">
            <option value="all" {{if $type == "all"}}selected="selected" {{/if}}>{{tr}}All{{/tr}}</option>
            <option value="prevue" {{if $type == "prevue"}}selected="selected" {{/if}}>Programmées seules</option>
            <option value="hors_plage" {{if $type == "hors_plage"}}selected="selected" {{/if}}>Hors plages seules</option>
          </select>
        </td>
      </tr>

      <tr>
        <td class="button" colspan="5">
          <button class="search" type="submit">Afficher</button>
          
          <label>
            <input type="checkbox" name="show_constantes" {{if $show_constantes}}checked{{/if}} />
            {{tr}}common-Show constant|pl{{/tr}}
          </label>
        </td>
      </tr>
    </table>
  </form>
{{else}}
  <script>
    exportToCSV = function () {
      var form = getForm('operations-stats-filter');

      var url = new Url('stats', 'vw_bloc2', 'raw');
      url.addParam('operation_ids', '{{$operation_ids}}');
      url.addParam('mode', 'csv');
      url.addParam('deblistbloc', $V(form.elements.date_min));
      url.addParam('finlistbloc', $V(form.elements.date_max));
      url.addParam('show_constantes', 1);
      url.popup(550, 300, 'statsBloc', null, {operation_ids: '{{$operation_ids}}'});
    }
  </script>
  <table class="main tbl">
    <tr>
      <td class="button">
        <button type="button" class="download" onclick="exportToCSV();">
          {{tr}}common-action-Export{{/tr}}
        </button>
      </td>
    </tr>
  </table>
{{/if}}

{{mb_include module=dPstats template=inc_bloc2_lines}}