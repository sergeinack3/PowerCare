{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=dPpatients script=export_patients}}

<script>
  Main.add(function () {
    Control.Tabs.create('export-tabs', true);

    const form = getForm('search-praticien-function');
    form.onsubmit();
  });
</script>

<table class="main" style="margin: 4px">
  <tr>
    <td class="narrow" style="vertical-align: bottom;">
      <h2>{{tr}}CGroups{{/tr}} : {{$group}}</h2>
      <label for="input_function">Filtrer par fonction</label>
      <form name="search-praticien-function" method="get"
            onsubmit="return onSubmitFormAjax(this, null, 'praticiens_list');">
        <input type="hidden" name="m" value="dPpatients"/>
        <input type="hidden" name="a" value="listPraticiens"/>
        <select name="function_select" id="function_select" onchange="ExportPatients.listByFunction();">
          <option value="all" selected="selected">
            -- Toutes
          </option>
            {{foreach from=$functions item=_function}}
              <option value="{{$_function->_id}}">
                  {{$_function}}
              </option>
            {{/foreach}}
        </select>
      </form>
      <br/>
      Praticiens de l'établissement (<span id="praticien-count">0</span> sélectionnés)
    </td>

    <td style="width: 500px;">
      <ul id="export-tabs" class="control_tabs">
          {{if "dPplanningOp"|module_active}}
            <li><a href="#export-sejours">Séjours</a></li>
          {{/if}}
        <li><a href="#export-patients">Patients</a></li>
      </ul>
    </td>
  </tr>

  <tr>
    <td rowspan="2">
      <div id="praticiens_list"></div>
    </td>

    <td>
      <div id="export-sejours" style="display: none;">
          {{mb_include module=dPpatients template=inc_archive_sejours}}
      </div>

      <div id="export-patients" style="display: none;">
          {{mb_include module=dPpatients template=inc_export_patients}}
      </div>
    </td>
  </tr>
</table>
