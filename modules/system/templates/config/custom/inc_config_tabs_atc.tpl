{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <style>
    .textarea-container {
      border:none;
    }

    .list_tabs_doc {
      padding:0;
    }

    .list_tabs_doc li {
      border:solid 1px #cacaca;
      margin-top:5px;
      list-style: none;
    }

    .list_tabs_doc li span.tag_tab {
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

    domUp = function(elt) {
      var previous = elt.previous();
      if (previous) {
        previous.remove();
        elt.insert({after:previous});
      }
    };

    domDown = function(elt) {
      var next = elt.next();
      if (next) {
        next.remove();
        elt.insert({before:next});
      }
    };

    createLi = function(elt, target) {
      var _id = ($$('li.line_config').length)+1;
      var title = '';
      var elts = [];

      if (elt) {
        var explode = elt.split('|');
        title = explode[0];
        elts = explode.slice(1);
      }

      var _button = DOM.button({'type': 'button', 'className': 'trash', 'onclick': 'this.up().remove();'});
      var _up = DOM.button({'type': 'button', 'className': 'up', 'onclick': 'domUp(this.up("li"));'});
      var _down = DOM.button({'type': 'button', 'className': 'down', 'onclick': 'domDown(this.up("li"));'});
      var _input = DOM.input({'type': 'text', 'className':'title', 'name':'title', 'value': title});

      var _span_atc = DOM.span({'class': 'atc'});
      $(elts).each(function(elt) {
        if (elt) {
          _span_atc.insert(DOM.span({'className' : 'tag_tab', 'onclick': '$(this).remove();'}, elt));
        }
      });

      var _auto_cp = DOM.input({"id" : 'seek_'+_id, "type" : "text", "name" : 'keywords_atc', "placeholder": "Rercherche"});
      var _div_ac = DOM.div({"style" : "float:right"}, _auto_cp);
      var _line = DOM.li({'className' : 'line_config'}, _button, _up, _down, _input, _span_atc, _div_ac);

      $(target).insert(_line);

      // ac
      var urlATC = new Url("medicament", "ajax_atc_autocomplete");
      urlATC.autoComplete("seek_"+_id, null, {
        minChars: 1,
        dropdown: true,
        updateElement: function(selected) {
          var div = selected.up('li').down("span.atc");
          var name = selected.select(".view")[0].innerHTML.replace(/<em>|<\/em>/g, '');
          div.insert(DOM.span({'class' : "tag_tab", 'onclick': '$(this).remove();'}, name));
          $V("seek"+_id, '');
        }
      });

    };

    // final save for the textarea
    saveText = function() {
      var textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
      var textarea_lines = [];

      var needs_corrections = 0;

      $$('ul.list_tabs_doc li').each(function(elt) {

        // title
        var input = $V(elt.select("input")[0]);
        if (!input) {
          return;
        }
        if (input.indexOf("|") != -1) {
          needs_corrections = 1;
        }
        var current_line = input+"|";

        // tags
        var _tags = [];
        elt.select("span.tag_tab").each(function(elt) {
          _tags.push(elt.innerHTML);
        });
        if (_tags.length) {
          current_line = current_line+_tags.join("|");
        }

        textarea_lines.push(current_line);
      });

      if (needs_corrections) {
        alert('Les titres ne doivent pas contenir le caractère \' (pipe)');
        return false;
      }

      var final_text = textarea_lines.join("\n");
      $V(textarea, final_text);

      return true;
    };

    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      var list = $$('ul.list_tabs_doc')[0];
      var field = form["c[{{$_feature}}]"][1];
      var lines = $V(field).split("\n");
      if (lines.length) {
        $(lines).each(function (elt) {
          if (elt !== '') {
            createLi(elt, list);
          }
        });
      }
    });
  </script>

<textarea style="display:none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable">{{$value}}</textarea>
  <ul class="list_tabs_doc">
  </ul>
  <div style="clear:both;"></div>
  <button type="button" {{if $is_inherited}}disabled{{/if}} onclick="createLi(null, $$('ul.list_tabs_doc')[0]);" class="add">Nouvel onglet</button>
  <p style="text-align:center;"><button type="submit" {{if $is_inherited}}disabled{{/if}} onclick="return saveText()" class="save">Enregistrer</button></p>
{{else}}
  <style>
    ul.parent_onglets {
      padding:0;
    }

    ul.parent_onglets li {
      line-height: 1.6em;
    }

    ul.parent_onglets span {
      border:solid 1px grey;
      border-radius: 3px;
      padding:0 3px;
      margin:0 3px;
      background-color: grey;
      color:white;
    }
  </style>
  {{if $value}}
    {{assign var=lines value="\n"|explode:$value}}

    <ul class="parent_onglets opacity-30">
    {{foreach from=$lines item=_line}}
      <li>
        {{assign var=elts value="|"|explode:$_line}}
        {{foreach from=$elts item=_elt name=_loop}}
          {{if $_elt}}
            {{if $smarty.foreach._loop.first}}<strong>{{else}}<span>{{/if}}
            {{$_elt}}
            {{if $smarty.foreach._loop.first}}</strong>{{else}}</span>{{/if}}
          {{/if}}
        {{/foreach}}
      </li>
    {{/foreach}}
    </ul>
  {{/if}}
{{/if}}