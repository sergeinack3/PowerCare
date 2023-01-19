{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_selector}}

<script>
  function savePersonnel(user_id, tag) {
    var listPers = {{$listPers|@json}};
    // liste des personnel_id de l'utilisateur
    var listPers_ids = listPers[user_id];
    document.editAffectation.personnel_id.value = listPers_ids[tag];
  }

  function loadRadio(user_id, personnel_id) {
    var listPers = {{$listPers|@json}};
    // liste des personnel_id de l'utilisateur
    var listPers_ids = listPers[user_id];

    $A(document.editAffectation._tag).each( function(input) {
      input.disabled = !listPers_ids[input.value];

      if(personnel_id == listPers_ids[input.value]){
        input.checked = "true";
      }
    });
  }

  function radio(user_id) {
    var listPers = {{$listPers|@json}};
    // liste des personnel_id de l'utilisateur
    if (user_id) {
      var listPers_ids = listPers[user_id];
    }
    document.editAffectation.personnel_id.value = "";
    $A(document.editAffectation._tag).each( function(input) {
      if (!user_id) {
        input.checked = "";
        input.disabled = "true";
        return;
      }
      input.checked = "";
      input.disabled = !listPers_ids[input.value];
    });
  }

  function viewCheckbox(user_id) {
    var oForm = document.filterFrm;
    var listPers = {{$listPers|@json}};

    if (user_id) {
      var listPers_ids = listPers[user_id];
    }
    $$("input.tag").each(function(input) {
      var matches = input.name.match(/list\[(.+)\]/);
      var tag = matches[1];

      if (!user_id) {
        input.checked = "";
        input.disabled = "true";
        return;
      }

      if (listPers_ids[tag]) {
        input.value = listPers_ids[tag];

        input.checked = "true";
        input.disabled = "";
      }
      else {
        input.value = "";
        input.checked = "";
        input.disabled = "true";
      }
    });
  }

  Main.add(function () {
    // Chargement des checkbox si un user est selectionné
    {{if $user_id}}
      viewCheckbox("{{$user_id}}");
    {{/if}}

    // chargement des bouton radio qd on selectionne une affectation
    {{if $affectation->_id}}
      loadRadio("{{$user_id}}","{{$affectation->personnel_id}}");
    {{else}}
    // tous les champs desactivés
      $A(document.editAffectation._tag).each( function(input) {
        input.checked = "";
        input.disabled = "true";
      });
    {{/if}}
  });
</script>

<div class="me-margin-top-8">
  <a href="?m={{$m}}&tab={{$tab}}&affect_id=0" class="button new">
   Créer une affectation
 </a>
</div>

<table class="main">
  <tr>
    <td>
      <form name="filterFrm" action="?m={{$m}}" method="get" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="dialog" value="{{$dialog}}" />

        <table class="form">
          <tr>
            <th colspan="2" class="title">Recherche sur le personnel</th>
            <th colspan="2" class="title">Recherche sur l'objet</th>
          </tr>
          <tr>
            <th class="category" colspan="10">
              {{if $listAffectations|@count == 50}}
                Plus de 50 affectations, seules les 50 plus récentes sont affichées
              {{else}}
                {{$listAffectations|@count}} affectations trouvées
              {{/if}}
            </th>
          </tr>
          <tr>
            <th>
              {{mb_label object=$filter field="personnel_id"}}
            </th>

            <!--  Affichage des membres du personnel suivant leurs types d'affectations -->
            <td>
              <select name="user_id" onchange="viewCheckbox(this.value)">
              <option value="">&mdash; Personnel &mdash;</option>
              {{foreach from=$listUsers item=user}}
                  <option value="{{$user->_id}}" {{if $user->_id == $user_id}}selected = "selected"{{/if}}>{{$user->_view}}</option>
              {{/foreach}}
               </select>
            </td>
            <td>
              {{mb_label object=$filter field="object_class"}}
              <select name="object_class" class="str maxLength|25">
                <option value="">&mdash; Toutes les classes</option>
                {{foreach from=$classes item=curr_class}}
                <option value="{{$curr_class}}" {{if $curr_class == $filter->object_class}}selected="selected"{{/if}}>
                {{$curr_class}}
                </option>
                {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <td></td>
            <td>
              {{foreach from=$personnel->_specs.emplacement->_list item=emplacement}}
              <input class="tag" type="checkbox" name="list[{{$emplacement}}]" disabled="disabled" value="" />
                <label for="filterFrm_list[{{$emplacement}}]" title="{{tr}}CPersonnel.emplacement.{{$emplacement}}{{/tr}}">
                  {{tr}}CPersonnel.emplacement.{{$emplacement}}{{/tr}}
                </label>
                <br />
              {{/foreach}}
            </td>
            <td>
              {{mb_label object=$filter field="object_id"}}
              <input type="text" name="object_id" class="canNull"  />
              <button class="search" type="button" onclick="ObjectSelector.initFilter()">Chercher</button>
              <script>
                ObjectSelector.initFilter = function() {
                  this.sForm     = "filterFrm";
                  this.sId       = "object_id";
                  this.sClass    = "object_class";
                  this.onlyclass = "false";
                  this.pop();
                }
              </script>
            </td>
          </tr>
          <tr>
            <td class="button" colspan="6">
              <button class="search me-primary">{{tr}}Show{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>

      <table class="tbl">
        <tr>
          <th>{{mb_title class=CAffectationPersonnel field=personnel_id}}</th>
          <th>{{mb_title class=CAffectationPersonnel field=object_class}}</th>
          <th>{{mb_title class=CAffectationPersonnel field=object_id}}</th>
        </tr>
        {{foreach from=$listAffectations key=key item=_affectation}}
        <tr>
           <th class="category" colspan="4">{{tr}}CPersonnel.emplacement.{{$key}}{{/tr}}</th>
        </tr>
        {{foreach from=$_affectation item=affect}}
        <tr {{if $affect->_id == $affectation->_id}}class="selected"{{/if}}>
          <td>
            <a href="?m={{$m}}&tab={{$tab}}&affect_id={{$affect->_id}}">
              {{$affect->_ref_personnel->_ref_user}}
            </a>
          </td>
          <td>{{tr}}{{$affect->object_class}}{{/tr}}</td>
          <td>
            <label onmouseover="ObjectTooltip.createEx(this, '{{$affect->_ref_object->_guid}}')">
              {{$affect->_ref_object}}
            </label>
          </td>
        </tr>
        {{/foreach}}
        {{/foreach}}
      </table>

    </td>
    <td>
      <form name="editAffectation" action="?m={{$m}}" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="affect_id" value="{{$affectation->_id}}" />
        <input type="hidden" name="dosql" value="do_affectation_aed" />
        <input type="hidden" name="del" value="0" />
        <table class="form" style="vertical-align: top">
          <tr>
            {{mb_include module=system template=inc_form_table_header object=$affectation}}
          </tr>
          <tr>
            <th>{{mb_label object=$affectation field="object_id"}}</th>
            <td>
              <input name="object_id" class="notNull" value="{{$affectation->object_id}}"/>
              <button class="search" type="button" onclick="ObjectSelector.initEdit()">{{tr}}Search{{/tr}}</button>
              <script>
                ObjectSelector.initEdit = function() {
                  this.sForm     = "editAffectation";
                  this.sId       = "object_id";
                  this.sClass    = "object_class";
                  this.onlyclass = "false";
                  this.pop();
                }
              </script>
            </td>
          </tr>
          {{if $affectation->object_id}}
          <tr>
            <td></td><td>{{$affectation->_ref_object}}</td>
          </tr>
          {{/if}}
          <tr>
            <th>{{mb_label object=$affectation field="object_class"}}</th>
            <td>
              <select name="object_class" class="notNull">
                <option value="">&mdash; Choisir une classe</option>
                {{foreach from=$classes item=curr_class}}
                <option value="{{$curr_class}}" {{if $affectation->object_class == $curr_class}}selected{{/if}}>
                  {{tr}}{{$curr_class}}{{/tr}}
                </option>
                {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$affectation field="personnel_id"}}</th>
            <td>
              <select name="user_id" onchange="radio(this.value)">
              <option value="">&mdash; Personnel &mdash;</option>
                {{mb_include module=mediusers template=inc_options_mediuser list=$listUsers selected=$user_id}}
              </select>
            </td>
          </tr>
          <tr>
            <td></td>
            <td colspan="2">
              {{foreach from=$personnel->_specs.emplacement->_list item=emplacement}}
              <input type="radio" name="_tag" value="{{$emplacement}}" onclick="savePersonnel(this.form.user_id.value, this.value)" />
              <label for="editAffectation__tag_{{$emplacement}}" title="{{tr}}CPersonnel.emplacement.{{$emplacement}}{{/tr}}">
                {{tr}}CPersonnel.emplacement.{{$emplacement}}{{/tr}}
              </label><br />
              {{/foreach}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$affectation field="realise"}}</th>
            <td>{{mb_field object=$affectation field="realise"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$affectation field="debut"}}</th>
            <td>{{mb_field object=$affectation field="debut" form="editAffectation" register=true}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$affectation field="fin"}}</th>
            <td>{{mb_field object=$affectation field="fin" form="editAffectation" register=true}}</td>
          </tr>
          <tr>
            <td colspan="2" style="text-align: center">
            {{if $affectation->_id}}
              <button class="modify">{{tr}}Validate{{/tr}}</button>
              <button class="trash" type="button" onclick="confirmDeletion(this.form,{typeName:'l\'affectation ',objName:'{{$affectation->_id|smarty:nodefaults|JSAttribute}}'})">
              {{tr}}Delete{{/tr}}
              </button>
            {{else}}
              <button class="submit" name="envoyer">{{tr}}Create{{/tr}}</button>
            {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>