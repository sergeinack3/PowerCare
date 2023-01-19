{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=total_pats value='?'}}

<script>
  nextCheck = function (start, total) {
    var form = getForm("check-integrity-form");
    $V(form.elements.progress, start);
    $('total-pats').innerHTML = total;

    if ($V(form.elements.continue) && start < total) {
      form.onsubmit();
    }
  };

  checkSQL = function (group_id, class_name, directory , additionnals_prats) {
    var url = new Url("importTools", "do_check_integrity", "dosql");
    url.addParam("group_id", group_id);
    url.addParam("class_name", class_name);
    url.addParam("directory", directory);
    url.addParam("additionnals_prats", additionnals_prats);
    url.requestUpdate("span-" + group_id + '-' + class_name, {method: 'post'})
  };

  showIntegrityCompare = function (group_id, directory) {
    var url = new Url("importTools", "ajax_compare_integrity");
    url.addParam('group_id', group_id);
    url.addParam('directory', directory);
    url.requestModal("50%", "50%");
  };
</script>

<form name="check-integrity-form" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-check-integrity')">
  <input type="hidden" name="m" value="importTools"/>
  <input type="hidden" name="a" value="ajax_check_integrity"/>

  <table class="main form">
    <tr>
      <th><label for="directory">{{tr}}Directory{{/tr}}</label></th>
      <td><input type="text" name="directory" size="50"/></td>
    </tr>

    <tr>
      <th><label for="step">{{tr}}Step{{/tr}}</label></th>
      <td><input type="number" name="step" value="10"/></td>
    </tr>

    <tr>
      <th>{{tr}}Progress{{/tr}}</th>
      <td><input type="number" readonly name="progress" value=""/> / <span id="total-pats">{{$total_pats}}</span></td>
    </tr>

    <tr>
      <th><label for="continue">{{tr}}Continue{{/tr}}</label></th>
      <td><input type="checkbox" name="continue" value="1"/></td>
    </tr>

    <tr>
      <td colspan="2" class="button"><button class="button change" type="submit">{{tr}}Check{{/tr}}</button></td>
    </tr>
  </table>
</form>

<div id="result-check-integrity"></div>


<div>
  <table class="main tbl">
    {{foreach from=$export_dirs key=_dir item=_stats}}
      <tr>
        <th class="section" rowspan="2"></th>
        <th class="section" rowspan="2">{{tr}}Progress{{/tr}}</th>
        <th class="section" colspan="2">CSejour ({{tr}}CSejour{{/tr}})</th>
        <th class="section" colspan="2">CConsultation ({{tr}}CConsultation{{/tr}})</th>
        <th class="section" colspan="2">COperation ({{tr}}COperation{{/tr}})</th>
        <th class="section" colspan="2">CFile ({{tr}}CFile{{/tr}})</th>
        <th class="section" colspan="2">CCompteRendu ({{tr}}CCompteRendu{{/tr}})</th>
      </tr>

      <tr>
        <th>Fichier exporté</th>
        <th>Base de données</th>
        <th>Fichier exporté</th>
        <th>Base de données</th>
        <th>Fichier exporté</th>
        <th>Base de données</th>
        <th>Fichier exporté</th>
        <th>Base de données</th>
        <th>Fichier exporté</th>
        <th>Base de données</th>
      </tr>

      <tr>
        {{if $_stats}}
          <td>
            {{$root_dir}}/{{$_dir}}
            <button class="search notext" type="button" onclick="showIntegrityCompare('{{$_stats.CGroups}}', '{{$_dir}}')">
              Afficher les différences
            </button>
          </td>
          <td {{if $_stats.start == $_stats.total}}class="ok"{{/if}}>{{$_stats.start}} / {{$_stats.total}}</td>
          <td align="right">{{$_stats.CSejour|@count}}</td>
          <td align="right">
            <span id="span-{{$_stats.CGroups}}-CSejour">
              {{if "SQL"|array_key_exists:$_stats && "CSejour"|array_key_exists:$_stats.SQL}}
                {{$_stats.SQL.CSejour|@count}}
              {{/if}}
             </span>
            <button type="button" class="change" onclick="checkSQL({{$_stats.CGroups}}, 'CSejour', '{{$_dir}}')"></button>
          </td>
          <td align="right">{{$_stats.CConsultation|@count}}</td>
          <td align="right">
            <span id="span-{{$_stats.CGroups}}-CConsultation">
              {{if "SQL"|array_key_exists:$_stats && "CConsultation"|array_key_exists:$_stats.SQL}}
                {{$_stats.SQL.CConsultation|@count}}
              {{/if}}
            </span>
            <button type="button" class="change" onclick="checkSQL({{$_stats.CGroups}}, 'CConsultation', '{{$_dir}}')"></button>
          </td>
          <td align="right">{{$_stats.COperation|@count}}</td>
          <td align="right">
            <span id="span-{{$_stats.CGroups}}-COperation">
              {{if "SQL"|array_key_exists:$_stats && "COperation"|array_key_exists:$_stats.SQL}}
                {{$_stats.SQL.COperation|@count}}
              {{/if}}
             </span>
            <button type="button" class="change" onclick="checkSQL({{$_stats.CGroups}}, 'COperation', '{{$_dir}}')"></button>
          </td>
          <td align="right">{{$_stats.CFile|@count}}</td>
          <td align="right">
            <span id="span-{{$_stats.CGroups}}-CFile">
              {{if "SQL"|array_key_exists:$_stats && "CFile"|array_key_exists:$_stats.SQL}}
                {{$_stats.SQL.CFile|@count}}
              {{/if}}
            </span>
            <button type="button" class="change" onclick="checkSQL({{$_stats.CGroups}}, 'CFile', '{{$_dir}}')"></button>
          </td>
          <td align="right">{{$_stats.CCompteRendu|@count}}</td>
          <td align="right">
            <span id="span-{{$_stats.CGroups}}-CCompteRendu">
              {{if "SQL"|array_key_exists:$_stats && "CCompteRendu"|array_key_exists:$_stats.SQL}}
                {{$_stats.SQL.CCompteRendu|@count}}
              {{/if}}
            </span>
            <button type="button" class="change" onclick="checkSQL({{$_stats.CGroups}}, 'CCompteRendu', '{{$_dir}}')"></button>
          </td>
        {{else}}
          <td colspan="12" class="empty">
            Nothing
          </td>
        {{/if}}
      </tr>

    {{/foreach}}
  </table>
</div>