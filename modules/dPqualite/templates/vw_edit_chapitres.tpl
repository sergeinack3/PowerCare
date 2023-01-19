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
        <input type="hidden" name="nav_chapitre_id" value="0" />
        <label for="typeVue">{{tr}}_classification{{/tr}}</label>
        <select name="typeVue" onchange="this.form.submit();">
          <option value="0" {{if $typeVue == 0}}selected="selected"{{/if}}>{{tr}}_CChapitreDoc_classification_chap{{/tr}}</option>
          <option value="1" {{if $typeVue == 1}}selected="selected"{{/if}}>{{tr}}_CThemeDoc_classification_theme{{/tr}}</option>
        </select>
        <br />
        <label for="etablissement">Etablissement</label>
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
      <a class="button new me-primary me-margin-top-4" href="?m=qualite&tab=vw_edit_classification&doc_chapitre_id=0">
        {{tr}}CChapitreDoc.create{{/tr}}
      </a>
      <table class="tbl">
        <tr>
          <th colspan="4" class="title">
            Hierarchie : {{$nav_chapitre->_path|default:"Tous"}}
          </th>
        </tr>
        <tr>
          <th colspan="3">{{tr}}CChapitreDoc-nom{{/tr}}</th>
          <th>{{tr}}CChapitreDoc-code{{/tr}}</th>
        </tr>
        {{if $nav_chapitre->_id}}
          <tr>
            <td>
              <a href="?m=qualite&tab=vw_edit_classification&nav_chapitre_id={{$nav_chapitre->_ref_pere->_id}}" title="Retour">
                <img src="images/icons/uparrow.png" title="Retour" />
              </a>
            </td>
            <td colspan="2" class="greedyPane">
              {{$nav_chapitre->nom}}
            </td>
            <td>
              {{$nav_chapitre->code}}
            </td>
          </tr>
        {{/if}}
        {{foreach from=$listChapitres item=curr_chapitre}}
          <tr>
            <td class="narrow"></td>
            <td class="narrow">
              {{if $nav_chapitre->_level < $maxDeep}}
                <a href="?m=qualite&tab=vw_edit_classification&nav_chapitre_id={{$curr_chapitre->_id}}" title="Ouvrir">
                  <img src="images/icons/downarrow.png" title="Ouvrir" />
                </a>
              {{else}}
                <img src="images/icons/rightarrow.png" title="Dernier niveau atteint" />
              {{/if}}
            </td>
            <td class="text greedyPane">
              <a href="?m=qualite&tab=vw_edit_classification&doc_chapitre_id={{$curr_chapitre->doc_chapitre_id}}"
                 title="{{tr}}CChapitreDoc.modify{{/tr}}">
                {{$curr_chapitre->nom}}
              </a>
            </td>
            <td class="text">
              <a href="?m=qualite&tab=vw_edit_classification&doc_chapitre_id={{$curr_chapitre->doc_chapitre_id}}"
                 title="{{tr}}CChapitreDoc.modify{{/tr}}">
                {{$curr_chapitre->code}}
              </a>
            </td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td class="halfPane">
      <form name="editChapitre" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$chapitre}}
        {{mb_key   object=$chapitre}}
        <input type="hidden" name="del" value="0" />
        <table class="form">
          <tr>
            {{if $chapitre->doc_chapitre_id}}
              <th class="title modify" colspan="2">{{tr}}CChapitreDoc-title-modify{{/tr}}: {{$chapitre->_view}}</th>
            {{else}}
              <th class="title me-th-new" colspan="2">{{tr}}CChapitreDoc-title-create{{/tr}}</th>
            {{/if}}
          </tr>
          <tr>
            <th>{{mb_label object=$chapitre field="group_id"}}</th>
            <td>
              {{if $chapitre->_id && $chapitre->group_id}}
                {{$chapitre->_ref_group->_view}}
                <input name="group_id" type="hidden" value="{{$chapitre->pere_id}}" />
              {{elseif $chapitre->_id}}
                {{tr}}All{{/tr}}
                <input name="group_id" type="hidden" value="" />
              {{elseif $nav_chapitre->_id && $nav_chapitre->group_id}}
                {{$nav_chapitre->_ref_group->_view}}
                <input name="group_id" type="hidden" value="{{$nav_chapitre->group_id}}" />
              {{elseif $nav_chapitre->_id}}
                {{tr}}All{{/tr}}
                <input name="group_id" type="hidden" value="" />
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
            <th>{{mb_label object=$chapitre field="pere_id"}}</th>
            <td>
              {{if $chapitre->_id}}
                {{if $chapitre->pere_id}}
                  {{$chapitre->_ref_pere->_view}}
                {{else}}
                  {{tr}}All{{/tr}}
                {{/if}}
              {{else}}
                <input type="hidden" name="pere_id" value="{{$nav_chapitre->_id}}">
                {{if $nav_chapitre->_id}}
                  {{$nav_chapitre->_view}}
                {{else}}
                  {{tr}}All{{/tr}}
                {{/if}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$chapitre field="nom"}}</th>
            <td>{{mb_field object=$chapitre field="nom"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$chapitre field="code"}}</th>
            <td>{{mb_field object=$chapitre field="code"}}</td>
          </tr>
          <tr>
            <td class="button" colspan="2">
              {{if $chapitre->doc_chapitre_id}}
                <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                <button class="trash" type="button"
                        onclick="confirmDeletion(this.form,{typeName:'{{tr escape="javascript"}}CChapitreDoc.one{{/tr}}',objName:'{{$chapitre->_view|smarty:nodefaults|JSAttribute}}'})">{{tr}}Delete{{/tr}}</button>
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