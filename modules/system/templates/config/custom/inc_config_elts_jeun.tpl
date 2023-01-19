{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=jeun_uniq_id}}
<div id="config_jeun_{{$jeun_uniq_id}}">
  {{if $is_last}}
    <style>
      .textarea-container {
        border:none;
      }
      .tag_jeun {
        display: none;
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
      createLiJeun = function(elt, target) {
        var _id = ($("config_jeun_{{$jeun_uniq_id}}").select('li.line_config').length)+1;

        var _button = DOM.button({'type': 'button', 'className': 'trash notext', 'onclick': 'this.up().remove();'});

        var _span_jeun = DOM.span({'class': 'atc'});
        if (elt) {
          _span_jeun.insert(DOM.span({'className' : "tag_tab"}, elt.split(':')[1]));
          _span_jeun.insert(DOM.span({'className' : 'tag_jeun'}, elt));
        }
        else {
          var _auto_cp = DOM.input(
            {"id" : 'seek_jeun_'+_id, "type" : "text", "name" : 'keywords_jeun', "placeholder": $T('Search')}
          );
        }
        var _div_ac = DOM.div({"style" : "float:right"}, _auto_cp);
        var _line = DOM.li({'className' : 'line_config'}, _button, _span_jeun, _div_ac);

        $(target).insert(_line);

        var form = getForm("edit-configuration-{{$uid}}");
        var urlEltJeun = new Url("prescription", "httpreq_do_element_autocomplete");
        urlEltJeun.addParam('field_name', 'keywords_jeun');
        urlEltJeun.autoComplete($('seek_jeun_'+_id) , null, {
          minChars: 1,
          dropdown: true,
          updateElement: function(selected) {
            var element_id = selected.down("small").innerHTML;
            var libelle = selected.down("strong").get("libelle");
            if (element_id) {
              var div = selected.up('li').down("span.atc");
              div.insert(DOM.span({'class' : "tag_jeun"}, element_id+': '+libelle));
              div.insert(DOM.span({'class' : "tag_tab", 'onclick': '$(this).remove();'}, libelle));
              $V("seek"+_id, '');
              $('seek_jeun_'+_id).up('div').up('div').style.display = "none";
            }
          }
        });
      };

      // final save for the textarea
      saveText{{$jeun_uniq_id}} = function() {
        var textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
        var textarea_lines = [];
        $("config_jeun_{{$jeun_uniq_id}}").select('ul.list_tabs_doc li').each(function(elt) {
          elt.select("span.tag_jeun").each(function(tag) {
            textarea_lines.push(tag.innerHTML);
          });
        });
        var final_text = textarea_lines.join("|");
        $V(textarea, final_text);
        return true;
      };

      Main.add(function() {
        var form = getForm("edit-configuration-{{$uid}}");
        var list = $("config_jeun_{{$jeun_uniq_id}}").select('ul.list_tabs_doc')[0];
        var field = form["c[{{$_feature}}]"][1];
        var lines = $V(field).split("|");

        if ($V(field).length && lines.length) {
          $(lines).each(function (elt) {
            createLiJeun(elt, list);
          });
        }

        {{if $_feature != "soins UserSejour elts_colonne_jeun" && !$value}}
        createLiJeun(null, $('config_jeun_{{$jeun_uniq_id}}').select('ul.list_tabs_doc')[0]);
        {{elseif $is_inherited}}
        toggleCustomValue($('div_jeun_{{$jeun_uniq_id}}'), false);
        {{/if}}
      });
    </script>

    <textarea style="display:none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable" value="{{$value}}">{{$value}}</textarea>
    <ul class="list_tabs_doc">
    </ul>
    <div style="clear:both;"></div>
  {{if $_feature == "soins UserSejour elts_colonne_jeun"}}
    <button id="div_jeun_{{$jeun_uniq_id}}" type="button" class="add"
            onclick="createLiJeun(null, $('config_jeun_{{$jeun_uniq_id}}').select('ul.list_tabs_doc')[0]);">
      {{tr}}CElementPrescription{{/tr}}
    </button>
  {{/if}}
    <p style="text-align:center;">
      <button type="submit" onclick="return saveText{{$jeun_uniq_id}}()" class="save">{{tr}}Save{{/tr}}</button>
    </p>
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