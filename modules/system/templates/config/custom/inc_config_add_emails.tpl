{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=email_uniq_id}}

<div id="config_email_{{$email_uniq_id}}">
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
        createLi{{$email_uniq_id}} = function(elt, target) {
          let _id = ($("config_email_{{$email_uniq_id}}").select('li.line_config').length)+1;
          let _button = DOM.button({'type': 'button', 'className': 'trash notext me-tertiary', 'onclick': 'this.up().remove();'});

          let _span_email = DOM.span({'class': 'email'});
          let _auto_cp;
          let seek_id = 'seek_{{$email_uniq_id}}'+_id;

          if (elt) {
            _span_email.insert(DOM.span({'className' : 'tag_tab'}, elt));
          }
          else {
            _auto_cp = DOM.input({"id" : seek_id, "type" : "text", "name" : 'keywords_email', 'class': "tag_tab"});
          }
          let _div_ac = DOM.div({"style" : "float:right"}, _auto_cp);
          let _line = DOM.li({'className' : 'line_config'}, _button, _span_email, _div_ac);

          $(target).insert(_line);
        };

        // final save for the textarea
        saveText{{$email_uniq_id}} = function() {
          let textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
          let textarea_lines = [];

          $("config_email_{{$email_uniq_id}}").select('ul.list_tabs_doc li').each(function(elt) {
            // tags
            let elements = elt.select("input.tag_tab").concat(elt.select("span.tag_tab"));
            elements.each(function(tag) {
              //innerHTML si span, value si input
              let value = tag.value ? tag.value : tag.innerHTML;
              if (value) {
                textarea_lines.push(value);
              }
            });
          });

          let final_text = textarea_lines.join("|");

          $V(textarea, final_text);

          return true;
        };

        Main.add(function() {
          let form = getForm("edit-configuration-{{$uid}}"),
            list = $("config_email_{{$email_uniq_id}}").select('ul.list_tabs_doc')[0],
            field = form["c[{{$_feature}}]"][1],
            lines = $V(field).split("|");

          if ($V(field).length && lines.length) {
            $(lines).each(function (elt) {
              createLi{{$email_uniq_id}}(elt, list);
            });
          }
            {{if $is_inherited}}
          toggleCustomValue($('div_email_{{$email_uniq_id}}'), false);
            {{/if}}
        });
      </script>

      <textarea style="display:none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable" value="{{$value}}">{{$value}}</textarea>
      <ul class="list_tabs_doc">
      </ul>
      <div style="clear:both;"></div>
      <button id="div_email_{{$email_uniq_id}}" type="button" class="add me-text-align-left me-margin-3"
              onclick="createLi{{$email_uniq_id}}(null, $('config_email_{{$email_uniq_id}}').select('ul.list_tabs_doc')[0]);">{{tr}}common-action-Add email{{/tr}}</button>
      <p><button type="submit" onclick="return saveText{{$email_uniq_id}}()" class="save me-text-align-lef me-margin-3">{{tr}}Save{{/tr}}</button></p>
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
