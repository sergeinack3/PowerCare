{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  {{unique_id var=id_compte_rendu}}
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
    createCR{{$id_compte_rendu}} = function(elt) {
      var div = elt.up('li').down('span.compte_rendu');
      var name = $V(elt);
      div.insert(DOM.span({'class' : 'tag_tab', 'onclick': '$(this).remove();'}, name));
      elt.up('div').style.display = "none";
    };

    createLi{{$id_compte_rendu}} = function(elt, target) {
      var _id = ($$('li.line_config').length)+1;

      var _button = DOM.button({'type': 'button', 'className': 'trash notext', 'onclick': 'this.up().remove();'});

      var _span_compte_rendu = DOM.span({'class': 'compte_rendu'});
      if (elt) {
        _span_compte_rendu.insert(DOM.span({'className' : 'tag_tab'}, elt));
      }
      else {
        var _auto_cp = DOM.input({"id" : 'text'+_id, "type" : "text", 'onchange': "createCR{{$id_compte_rendu}}(this);"});
      }
      var _div_ac = DOM.div({"style" : "display:inline"}, _auto_cp);
      var _line = DOM.li({'className' : 'line_config'}, _button, _span_compte_rendu, _div_ac);

      $(target).insert(_line);
    };

    // final save for the textarea
    saveText{{$id_compte_rendu}} = function() {
      var textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
      var textarea_lines = [];

      $$('ul.list_tabs_doc{{$id_compte_rendu}} li').each(function(elt) {
        // tags
        elt.select("span.tag_tab").each(function(tag) {
          textarea_lines.push(tag.innerHTML);
        });
      });

      var final_text = textarea_lines.join("|");
      $V(textarea, final_text);

      return true;
    };

    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      var list = $$('ul.list_tabs_doc{{$id_compte_rendu}}')[0];
      var field = form["c[{{$_feature}}]"][1];
      var lines = $V(field).split("|");
      if (lines.length) {
        $(lines).each(function (elt) {
          createLi{{$id_compte_rendu}}(elt, list);
        });
      }
      {{if $is_inherited}}
      toggleCustomValue($('div_compte_rendu'+'{{$id_compte_rendu}}'), false);
      {{/if}}
    });
  </script>

  <textarea style="display:none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable" value="{{$value}}">{{$value}}</textarea>
  <ul class="list_tabs_doc{{$id_compte_rendu}}"></ul>
  <button type="button" onclick="createLi{{$id_compte_rendu}}(null, $$('ul.list_tabs_doc{{$id_compte_rendu}}')[0]);" class="add" id="div_compte_rendu{{$id_compte_rendu}}">Ajouter un nom de compte rendu</button>
  <button type="submit" onclick="return saveText{{$id_compte_rendu}}()" class="save">{{tr}}Save{{/tr}}</button>
{{else}}
  {{if $value}}
    {{assign var=lines value="|"|explode:$value}}
    <ul class="parent_onglets opacity-30">
      {{foreach from=$lines item=_line}}
        <li>{{$_line}}</li>
      {{/foreach}}
    </ul>
  {{/if}}
{{/if}}