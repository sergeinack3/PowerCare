{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane" rowspan="3">
      <form name="FrmTypeVue" action="?m={{$m}}" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <label for="typeVue">{{tr}}_classification{{/tr}}</label>
        <select name="typeVue" onchange="this.form.submit();">
          <option value="0" {{if $typeVue == 0}}selected="selected"{{/if}}>{{tr}}_CChapitreDoc_classification_chap{{/tr}}</option>
          <option value="1" {{if $typeVue == 1}}selected="selected"{{/if}}>{{tr}}_CThemeDoc_classification_theme{{/tr}}</option>
        </select>
        <br />
        <label for="etablissement">{{tr}}common-Establishment{{/tr}}</label>
        <select name="etablissement" onchange="this.form.submit();">
          <option value="0" {{if $etablissement == 0}}selected="selected"{{/if}}>{{tr}}All{{/tr}}</option>
          {{foreach from=$etablissements item=curr_etab}}
            <option value="{{$curr_etab->_id}}" {{if $etablissement == $curr_etab->_id}}selected="selected"{{/if}}>
              {{$curr_etab->_view}}
            </option>
          {{/foreach}}
        </select>
      </form>
      <br />
      <a class="button new" href="?m=qualite&tab=vw_edit_classification&doc_theme_id=0">
        {{tr}}CThemeDoc.create{{/tr}}
      </a>
      <table class="tbl">
        <tr>
          <th>{{tr}}CThemeDoc-nom-court{{/tr}}</th>
        </tr>
        {{foreach from=$listThemes item=curr_theme}}
          <tr>
            <td class="text">
              <a href="?m=qualite&tab=vw_edit_classification&doc_theme_id={{$curr_theme->doc_theme_id}}"
                 title="{{tr}}CThemeDoc.modify{{/tr}}">
                {{$curr_theme->nom}}
              </a>
            </td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td class="halfPane">
      <form name="editThème" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$theme}}
        {{mb_key   object=$theme}}
        <input type="hidden" name="del" value="0" />
        <table class="form">
          <tr>
            {{if $theme->_id}}
              <th class="title modify" colspan="2">{{tr}}CThemeDoc-title-modify{{/tr}}: {{$theme->_view}}</th>
            {{else}}
              <th class="title me-th-new" colspan="2">{{tr}}CThemeDoc-title-create{{/tr}}</th>
            {{/if}}
          </tr>
          <tr>
            <th>{{mb_label object=$theme field="group_id"}}</th>
            <td>
              {{if $theme->_id}}
                {{$theme->_ref_group->_view}}
              {{else}}
                <select name="group_id">
                  <option value="">{{tr}}All{{/tr}}</option>
                  {{foreach from=$etablissements item=curr_etab}}
                    <option value="{{$curr_etab->_id}}" {{if $etablissement == $curr_etab->_id}}selected="selected"{{/if}}>
                      {{$curr_etab->_view}}
                    </option>
                  {{/foreach}}
                </select>
              {{/if}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$theme field="nom"}}</th>
            <td>{{mb_field object=$theme field="nom"}}</td>
          </tr>
          <tr>
            <td class="button" colspan="2">
              {{if $theme->doc_theme_id}}
                <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                <button class="trash" type="button"
                        onclick="confirmDeletion(this.form,{typeName:'{{tr escape="javascript"}}CThemeDoc.one{{/tr}}',objName:'{{$theme->_view|smarty:nodefaults|JSAttribute}}'})">{{tr}}Delete{{/tr}}</button>
              {{else}}
                <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>