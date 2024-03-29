{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=atc_uniq_id}}

<div id="config_atc_{{$atc_uniq_id}}">
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
        createLi{{$atc_uniq_id}} = function(elt, target) {
          var _id = ($("config_atc_{{$atc_uniq_id}}").select('li.line_config').length)+1;

          var _button = DOM.button({'type': 'button', 'className': 'trash notext me-tertiary', 'onclick': 'this.up().remove();'});

          var _span_atc = DOM.span({'class': 'atc'});
          var _auto_cp;
          var seek_id = 'seek_{{$atc_uniq_id}}'+_id;

          if (elt) {
            _span_atc.insert(DOM.span({'className' : 'tag_tab'}, elt));
          }
          else {
            _auto_cp = DOM.input({"id" : seek_id, "type" : "text", "name" : 'keywords_atc', 'class': "tag_tab"});
          }
          var _div_ac = DOM.div({"style" : "float:right"}, _auto_cp);
          var _line = DOM.li({'className' : 'line_config'}, _button, _span_atc, _div_ac);

          $(target).insert(_line);
        };

        // final save for the textarea
        saveText{{$atc_uniq_id}} = function() {
          var textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
          var textarea_lines = [];

          $("config_atc_{{$atc_uniq_id}}").select('ul.list_tabs_doc li').each(function(elt) {
            // tags
            var elements = elt.select("input.tag_tab").concat(elt.select("span.tag_tab"));
            elements.each(function(tag) {
              //innerHTML si span, value si input
              var value = tag.value ? tag.value : tag.innerHTML;
              if (value) {
                textarea_lines.push(value);
              }
            });
          });

          var final_text = textarea_lines.join("|");

          $V(textarea, final_text);

          return true;
        };

        Main.add(function() {
          var form = getForm("edit-configuration-{{$uid}}");
          var list = $("config_atc_{{$atc_uniq_id}}").select('ul.list_tabs_doc')[0];
          var field = form["c[{{$_feature}}]"][1];
          var lines = $V(field).split("|");

          if ($V(field).length && lines.length) {
            $(lines).each(function (elt) {
              createLi{{$atc_uniq_id}}(elt, list);
            });
          }
            {{if $is_inherited}}
          toggleCustomValue($('div_atc_tracabilite_{{$atc_uniq_id}}'), false);
            {{/if}}
        });
      </script>

      <textarea style="display:none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable" value="{{$value}}">{{$value}}</textarea>
      <ul class="list_tabs_doc">
      </ul>
      <div style="clear:both;"></div>
      <button id="div_atc_tracabilite_{{$atc_uniq_id}}" type="button" class="add me-text-align-left me-margin-3"
              onclick="createLi{{$atc_uniq_id}}(null, $('config_atc_{{$atc_uniq_id}}').select('ul.list_tabs_doc')[0]);">{{tr}}CApicrypt-Add_email{{/tr}}</button>
      <p><button type="submit" onclick="return saveText{{$atc_uniq_id}}()" class="save me-text-align-lef me-margin-3">{{tr}}Save{{/tr}}</button></p>
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
</div>
