{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="import-ex_class" method="post" onsubmit="return onSubmitFormAjax(this, {}, 'ex_class-import-report')">
  <input type="hidden" name="m" value="forms" />
  <input type="hidden" name="dosql" value="do_import_ex_class" />
  <input type="hidden" name="file_uid" value="{{$uid}}" />

  <fieldset>
    <legend>2. Options</legend>

    <table class="main form">
      <tr>
        <th>Nom du formulaire</th>
        <td><input type="text" name="options[ex_class_name]" value="{{$ex_class_name}}" size="50" /></td>
      </tr>

      {{if $in_hermetic_mode && $app->_ref_user->isAdmin()}}
        <tr>
          <th>{{mb_label class=CExClass field=group_id}}</th>
          <td>
            <select name="group_id" onchange="ExClass.uploadSaveUID('{{$uid}}', '{{$ignore_similar}}', $V(this));">
              <option value="" {{if $group_id === null}}selected{{/if}}> &ndash; Tous </option>

                {{foreach from=$groups item=_group}}
                  <option value="{{$_group->_id}}" {{if $_group->_id === $group_id}} selected{{/if}}>
                      {{$_group}}
                  </option>
                {{/foreach}}
            </select>
          </td>
        </tr>
      {{/if}}

      <tr>
        <th></th>
        <td>
          <label>
            <input type="checkbox" name="options[ignore_disabled_fields]" checked /> Ignorer les champs désactivés
          </label>
        </td>
      </tr>
    </table>
  </fieldset>

  <fieldset>
    <legend>3. {{tr}}CExList{{/tr}}s</legend>
    <table class="main tbl">
      <colgroup style="width: 50%;">
        <col style="width: 25%;"/>
        <col style="width: 25%;"/>
      </colgroup>

      <colgroup style="width: 50%;">
        <col style="width: 25%;"/>
        <col style="width: 25%;"/>
      </colgroup>

      <tr>
        <th class="category" colspan="2">Présent dans le fichier</th>
        <th class="category" colspan="2">Présent en base</th>
      </tr>
      {{foreach from=$lists item=_list key=_key}}
        <tr>
          <td>
            {{$_list.values.name}}

            <br />
            {{if $_list.values.coded}}
              Codée
            {{else}}
              <em>Non codée</em>
            {{/if}}
          </td>
          <td class="text">
            <ul>
              {{foreach from=$_list.elements item=_values}}
                <li>{{$_values.name}} &mdash; {{if $_values.code !== ""}}{{$_values.code}}{{else}}<span class="empty">Aucun code</span>{{/if}}</li>
              {{/foreach}}
            </ul>
          </td>
          <td>
            <script>
              Main.add(function(){
                var form = getForm("import-ex_class");
                displayListItems(form.elements["fromdb[{{$_key}}]"], '{{$_key}}');
              })
            </script>
            <select name="fromdb[{{$_key}}]" style="width: 20em;" onchange="displayListItems(this, '{{$_key}}')">
              {{foreach from=$_list.similar item=_similar}}
                <option value="{{$_similar->_guid}}">{{$_similar}}</option>
              {{/foreach}}
              <option value="__create__"> &ndash; Créer (renommé si déjà présent) &ndash; </option>
              {{if !$ignore_similar}}
                <optgroup label="Autre">
                  {{foreach from=$all_lists item=_list_object}}
                    <option value="{{$_list_object->_guid}}">{{$_list_object}}</option>
                  {{/foreach}}
                </optgroup>
              {{/if}}
            </select>
          </td>
          <td id="list-items-{{$_key}}" class="text"></td>
        </tr>
      {{/foreach}}
    </table>
  </fieldset>

  <fieldset>
    <legend>4. {{tr}}CExConcept{{/tr}}s</legend>
    <table class="main tbl">
      <tr>
        <th class="category" style="width: 50%;">Présent dans le fichier</th>
        <th class="category">Présent en base</th>
      </tr>
      {{foreach from=$concepts item=_concept key=_key}}
        <tr>
          <td>{{$_concept.values.name}}</td>
          <td>
            <select name="fromdb[{{$_key}}]" style="width: 30em;">
              {{foreach from=$_concept.similar item=_similar}}
                {{assign var=_concept_spec value=$_similar->loadConceptSpec()}}
                <option value="{{$_similar->_guid}}">
                  {{$_similar}} {{if $_concept_spec->getSpecType() != $_concept.spec_type}} (ATTENTION: type différent){{/if}}
                </option>
              {{/foreach}}
              <option value="__create__">Créer (renommé si déjà présent)</option>

              {{if !$ignore_similar}}
                <optgroup label="Autre de même type">
                  {{foreach from=$all_concepts item=_concept_object}}
                    {{assign var=_concept_spec value=$_concept_object->loadConceptSpec()}}
                    {{if $_concept_spec->getSpecType() == $_concept.spec_type}}
                      <option value="{{$_concept_object->_guid}}">{{$_concept_object}}</option>
                    {{/if}}
                  {{/foreach}}
                </optgroup>

                <optgroup label="Autre de type différent (déconseillé)">
                  {{foreach from=$all_concepts item=_concept_object}}
                    {{assign var=_concept_spec value=$_concept_object->loadConceptSpec()}}
                    {{if $_concept_spec->getSpecType() != $_concept.spec_type}}
                      <option value="{{$_concept_object->_guid}}">{{$_concept_object}}</option>
                    {{/if}}
                  {{/foreach}}
                </optgroup>
              {{/if}}
            </select>
          </td>
        </tr>
      {{/foreach}}
    </table>
  </fieldset>

  <table class="main tbl">
    <tr>
      <td style="width: 50%;"></td>
      <td><button class="save">{{tr}}Import{{/tr}}</button></td>
    </tr>
  </table>
</form>
