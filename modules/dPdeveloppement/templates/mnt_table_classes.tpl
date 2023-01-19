{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  submitFormTable = function () {
    var types = '';
    $$('input.enum_type').each(function (elt) {
      if (elt.checked) {
        if (types) {
          types += '|' + elt.value;
        }
        else {
          types = elt.value;
        }
      }
    })

    var form = getForm('mntTable');
    $V(form.elements.types, types);

    form.onsubmit();
  }
</script>

<div id="mnt-table-classes">
  <table class="main">
    <tr>
      <td style="text-align: center;">
        <form name="mntTable" method="get" onsubmit="return onSubmitFormAjax(this, null, 'mnt-table-classes');">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="a" value="mnt_table_classes" />
          <input type="hidden" name="types" value="{{$checked_types}}"/>

          <label for="module" title="Veuillez sélectionner un module">Choix du module</label>
          <select class="str" name="module">
            <option value=""{{if !$module}} selected="selected"{{/if}}>&mdash; Liste des erreurs</option>
            {{foreach from=$installed_modules item=_module}}
              <option value="{{$_module}}" {{if $module == $_module}} selected="selected"{{/if}}>{{$_module}} - {{tr}}module-{{$_module}}-court{{/tr}}</option>
            {{/foreach}}
          </select>
          <br />

          <label for="class" title="Veuillez sélectionner une classe">Choix de la classe</label>
          <select class="str" name="class">
            <option value=""{{if !$class}} selected="selected"{{/if}}>&mdash; Liste des erreurs</option>
            {{foreach from=$installed_classes item=_class}}
              <option value="{{$_class}}" {{if $class == $_class}} selected="selected"{{/if}}>{{$_class}} - {{tr}}{{$_class}}{{/tr}}</option>
            {{/foreach}}
          </select>
          <br />

          {{foreach from=$types key=type item=value}}
            <input type="checkbox" name="enum_types[]" class="enum_type" value="{{$type}}" {{if $value}}checked="checked"{{/if}} />{{$type}}
          {{/foreach}}
          <br />

          <button name="button" class="search me-primary" onclick="submitFormTable();">Filtrer</button>
        </form>
      </td>
      <td>
        <form action="?" name="csv-class-table" method="get" target="_blank">
          <input type="hidden" name="m" value="dPdeveloppement" />
          <input type="hidden" name="a" value="csv_class_tables" />
          <input type="hidden" name="suppressHeaders" value="1" />
          <button class="change">CSV classes / tables</button>
        </form>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <div class="big-info">Pour chaque spécification de propriété :
          <ul>
            <li><strong>la première ligne</strong> correspond au mapping objet => relationnel théorique,</li>
            <li><strong>la deuxième ligne </strong>correspond à ce qui est réellement présent dans la base de données.</li>
          </ul>
        </div>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <table class="tbl">
          <tr>
            <th rowspan="2">Champ</th>
            <th rowspan="2">Spec object</th>
            <th colspan="8">Base de données</th>
          </tr>
          <tr>
            <th>Type</th>
            <th>Default</th>
            <th>Index</th>
            <th>Extra</th>
          </tr>

          {{foreach from=$list_classes key=_class item=_class_details}}
            {{if $list_errors.$_class || $list_classes|@count == 1}}
              {{if $_class_details.suggestion}}
                <tr>
                  <th colspan="11" class="title">
                    <button id="sugg-{{$_class}}-trigger" class="edit" style="float: left;" onclick="$('sugg-{{$_class}}').toggle()">
                      {{tr}}Suggestion{{/tr}}
                    </button>
                    {{$_class}} ({{tr}}{{$_class}}{{/tr}})
                  </th>
                </tr>
                <tr id="sugg-{{$_class}}" style="display: none;">
                  <td colspan="100">
                    <pre>{{$_class_details.suggestion}}</pre>
                  </td>
                </tr>
              {{/if}}
              {{foreach from=$_class_details.fields key=_field_name item=_field}}

                {{if $list_errors.$_class.$_field_name || $_class_details.key == $_field_name || $class == $_class}}
                  <tr>
                    <td {{if $_class_details.key == $_field_name}}class="ok"{{/if}}>{{$_field_name}}</td>

                    {{if !$_field.object.spec}}
                      <td class="warning text">Aucune définition de propriété</td>
                    {{else}}
                      <td class="text" title="{{$_field.object.spec}}">
                        {{$_field.object.spec|replace:"|":" | "}}
                      </td>
                    {{/if}}

                    <td class="text">
                      {{if $_field.object.db_spec}}
                        {{$_field.object.db_spec.type}}

                        {{if is_array($_field.object.db_spec.params) && $_field.object.db_spec.params|@count > 0}}
                          (
                          {{foreach from=$_field.object.db_spec.params item=param name=params}}
                            {{$param}}{{if !$smarty.foreach.params.last}},{{/if}}
                          {{/foreach}}
                          )
                        {{/if}}

                        {{if $_field.object.db_spec.unsigned}}UNSIGNED{{/if}}
                        {{if $_field.object.db_spec.zerofill}}ZEROFILL{{/if}}

                        {{if !$_field.object.db_spec.null}}NOT NULL{{/if}}
                        {{if $_field.object.db_spec.default !== null}}DEFAULT {{$_field.object.db_spec.default}}{{/if}}
                      {{else}}
                        <div class="error">
                          Pas de spec pour cette colonne
                        </div>
                      {{/if}}
                      &nbsp;
                      <hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />

                      {{if !$_class_details.no_table}}
                        {{if $_field.db}}
                          <span {{if $_field.db.type != $_field.object.db_spec.type}}class="warning"{{/if}}>
                        {{$_field.db.type}}
                      </span>

                          <span {{if $_field.db.params != $_field.object.db_spec.params}}class="warning"{{/if}}>
                        {{if is_array($_field.db.params) && $_field.db.params|@count > 0}}
                          (
                          {{foreach from=$_field.db.params item=param name=params}}
                            {{$param}}{{if !$smarty.foreach.params.last}},{{/if}}
                          {{/foreach}}
                          )
                        {{/if}}
                      </span>

                          <span {{if $_field.db.unsigned != $_field.object.db_spec.unsigned}}class="warning"{{/if}}>
                        {{if $_field.db.unsigned}}UNSIGNED{{/if}}
                      </span>

                          <span {{if $_field.db.zerofill != $_field.object.db_spec.zerofill}}class="warning"{{/if}}>
                        {{if $_field.db.zerofill}}ZEROFILL{{/if}}
                      </span>

                          <span {{if $_field.db.null != $_field.object.db_spec.null}}class="warning"{{/if}}>
                        {{if !$_field.db.null}}NOT NULL{{/if}}
                      </span>

                          <span {{if $_field.db.default != $_field.object.db_spec.default}}class="warning"{{/if}}>
                        {{if $_field.db.default !== null && $_field.db.default !== ''}}DEFAULT {{$_field.db.default}} {{/if}}
                      </span>

                        {{else}}
                          <div class="error">
                            Pas de colonne pour cette spec
                          </div>
                        {{/if}}
                      {{else}}
                        <div class="error">
                          Pas de table existante pour cette classe
                        </div>
                      {{/if}}
                    </td>

                    <td>
                      {{$_field.object.db_spec.default}}&nbsp;<hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                      <span {{if $_field.db.default != $_field.object.db_spec.default}}class="warning"{{/if}}>
                    {{$_field.db.default}}&nbsp;
                  </span>
                    </td>

                    <td>
                      {{if $_field.object.db_spec.index}}
                      {{if $_field.object.db_spec.index !== '1'}}
                        Multi({{$_field.object.db_spec.index}})
                      {{else}}
                        Oui
                      {{/if}}
                      {{else}}
                      Non
                      {{/if}}&nbsp;<hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                      <span
                    {{if $_field.object.db_spec.index && !$_field.db.index && !strpos($_field.object.spec, 'index|0')}}
                        {{if strpos($_field.object.spec,'ref') === 0}}
                          class="error"
                        {{else}}
                          class="warning"
                        {{/if}}
                        {{else}}
                        {{if !$_field.object.db_spec.index && $_field.db.index}}
                          class="ok"
                        {{elseif strpos($_field.object.db_spec.index, ', ') !== false && $_field.object.db_spec.index !== $_field.db.index}}
                          class="warning"
                        {{/if}}
                        {{/if}}>
                    {{if $_field.db.index}}
                        {{if strpos($_field.db.index, ', ') !== false}}
                          Multi({{$_field.db.index}})
                        {{else}}
                          Oui
                        {{/if}}
                        {{else}}
                      Non
                    {{/if}}&nbsp;
                  </span>
                    </td>

                    <td>
                      {{$_field.object.db_spec.extra}}&nbsp;<hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                      <span {{if $_field.db.extra != $_field.object.db_spec.extra}}class="warning"{{/if}}>
                    {{$_field.db.extra}}&nbsp;
                  </span>
                    </td>
                  </tr>
                {{/if}}
              {{/foreach}}

              {{foreach from=$_class_details.fulltext_indexes item=_fulltext_index}}
                <tr>
                  <td class="text info">
                    {{$_fulltext_index.name}}
                  </td>
                  <td></td>
                  <td class="text">
                    FULLTEXT INDEX (
                    {{foreach from=$_fulltext_index.required_fields key=_fulltext_index_key item=_fulltext_index_field}}
                      {{if $_fulltext_index_key != 0}},{{/if}}
                      {{$_fulltext_index_field}}
                    {{/foreach}}
                    )
                    <hr style="border: 0; border-top: 1px solid #CCC; margin: 1px;" />
                    {{if $_fulltext_index.indexed_fields|@count > 0}}
                      FULLTEXT INDEX (
                      {{foreach from=$_fulltext_index.indexed_fields key=_fulltext_index_key item=_fulltext_index_field}}
                        {{if $_fulltext_index_key != 0}},{{/if}}
                        {{$_fulltext_index_field}}
                      {{/foreach}}
                      )
                    {{else}}
                      <span class="error">NO FULLTEXT INDEX</span>
                    {{/if}}
                  </td>
                  <td></td>
                  <td>
                    {{if $_fulltext_index.status === "valid"}}
                      <span class="ok">Oui</span>
                    {{elseif $_fulltext_index.status === "missing"}}
                      <span class="error">Non</span>
                    {{elseif $_fulltext_index.status === "incomplete"}}
                      <span class="error">Incomplet</span>
                    {{/if}}
                  </td>
                  <td></td>
                </tr>
              {{/foreach}}
            {{/if}}
          {{/foreach}}
        </table>
      </td>
    </tr>
  </table>
</div>