{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  {{unique_id var=id_type_document}}
  <style>
    .textarea-container {
      border:none;
    }

    ul li span.tag_tab {
      border:solid 1px grey;
      cursor:pointer;
      display: inline-block;
      border-radius: 2px;
      margin:0 2px;
      padding:2px;
      background-color: #4e4e4e;
      color:white;
    }
  </style>

  <script>
    createLi{{$id_type_document}} = function(categorie, target, value) {
      if (!categorie) {
        return;
      }

      var categorie_name = null;
      var categorie_id = null;

      if (value == 1) {
        $('select_categories').select('option').each(function(option) {
          if (categorie.include(option.value)) {
            categorie_name = option.text;
            categorie_id = option.value;
          }
        });
      }
      else {
       categorie_name = categorie.options[categorie.selectedIndex].text;
       categorie_id   = categorie.options[categorie.selectedIndex].value;
      }

      var _button = DOM.button({"type": "button", "className": "trash notext", "onclick": "this.up().remove();"});
      var _span = DOM.span({"type": "text", "className":"tag_tab title notext", "style": "margin-left: 5px;"}, categorie_name);

      var _line = DOM.li({"className" : "line_config categorie_custom", "data-categorie": categorie_id}, _button, _span);

      $(target).insert(_line);
    };

    saveText{{$id_type_document}} = function() {
      var textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
      var list = $$("ul.list_categories_{{$id_type_document}}")[0];

      var categorie_ids = [];

      list.select("li").each(function(elt) {
        categorie_ids.push(elt.get("categorie"));
      });

      $V(textarea, categorie_ids.length ? categorie_ids.join("|") : '');

      return true;
    };

    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      var list = $$("ul.list_categories_{{$id_type_document}}")[0];
      var field = form["c[{{$_feature}}]"][1];
      var lines = $V(field).split("|");

      if (lines.length) {
        $(lines).each(function(elt) {
          createLi{{$id_type_document}}(elt, list, 1);
        });
      }
    });
  </script>

  <textarea style="display: none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable" value="{{$value}}">{{$value}}</textarea>

  <select id="select_categories" name="categories" {{if $is_inherited}}disabled{{/if}} onchange="createLi{{$id_type_document}}(this, $$('ul.list_categories_{{$id_type_document}}')[0], 0); this.selectedIndex = 0;" style="width: 350px;">
    <option value="">&mdash; Choisissez une catégorie de fichiers</option>
    {{foreach from='Ox\Mediboard\Files\CFilesCategory::getFileCategories'|static_call:null item=_category}}
      <option value="{{$_category->_id}}">{{$_category->nom}}</option>
    {{/foreach}}
  </select>

  <ul class="list_categories_{{$id_type_document}}"></ul>

  <p style="text-align: center;">
    <button type="submit" class="save" onclick="return saveText{{$id_type_document}}();">{{tr}}Save{{/tr}}</button>
  </p>
{{else}}
  {{if $value}}
   {{assign var=elts value="|"|explode:$value}}
    <ul>
      {{foreach from='Ox\Mediboard\Files\CFilesCategory::getFileCategories'|static_call:null item=_category}}
        {{foreach from=$elts item=_elt}}
          {{if $_elt == $_category->_id}}
            <li class="categorie_custom">{{$_category}}</li>
          {{/if}}
        {{/foreach}}
      {{/foreach}}
    </ul>
  {{/if}}
{{/if}}
