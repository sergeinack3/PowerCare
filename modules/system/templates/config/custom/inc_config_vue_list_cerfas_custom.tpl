{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <style>
    li.cerfa_custom {
      list-style: none;
    }
  </style>

  <script>
    createLi = function(cerfa, target) {
      if (!cerfa) {
        return;
      }
      var _button = DOM.button({"type": "button", "className": "trash notext", "onclick": "this.up().remove();"});
      var _up = DOM.button({"type": "button", "className": "up notext", "onclick": "domUp(this.up('li'));"});
      var _down = DOM.button({"type": "button", "className": "down notext", "onclick": "domDown(this.up('li'));"});
      var _span = DOM.span({"type": "text", "className":"title notext", "style": "margin-left: 5px;"}, $T("Cerfa-" + cerfa));

      var _line = DOM.li({"className" : "line_config cerfa_custom", "data-cerfa": cerfa}, _button, _up, _down, _span);

      $(target).insert(_line);
    };

    domUp = function(elt) {
      var previous = elt.previous();
      if (previous) {
        previous.remove();
        elt.insert({after: previous});
      }
    };

    domDown = function(elt) {
      var next = elt.next();
      if (next) {
        next.remove();
        elt.insert({before:next});
      }
    };

    saveText = function() {
      var list = $$("ul.list_tabs_cerfas")[0];
      var textarea = getForm("edit-configuration-{{$uid}}").elements["c[{{$_feature}}]"][1];

      var cerfas = [];
      list.select("li").each(function(elt) {
        cerfas.push(elt.get("cerfa"));
      });

      $V(textarea, cerfas.join("|"));

      return true;
    };

    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      var field = form["c[{{$_feature}}]"][1];
      var list = $$("ul.list_tabs_cerfas")[0];
      var lines = $V(field).split("|");

      if (lines.length) {
        $(lines).each(function(elt) {
          createLi(elt, list);
        });
      }
    });
  </script>

  <textarea style="display: none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable">{{$value}}</textarea>

  {{assign var=components value="|"|explode:$_prop.components}}
  <select name="components" {{if $is_inherited}}disabled{{/if}} onchange="createLi($V(this), $$('ul.list_tabs_cerfas')[0]); this.selectedIndex = 0;" style="width: 350px;">
    <option value="">&mdash; {{tr}}Cerfa-action-Choose a Cerfa{{/tr}}</option>
  {{foreach from=$components item=_component}}
    <option value="{{$_component}}">{{tr}}Cerfa-{{$_component}}{{/tr}}</option>
  {{/foreach}}
  </select>

  <ul class="list_tabs_cerfas"></ul>
    
  <p style="text-align: center;">
    <button class="save" onclick="return saveText();">{{tr}}Save{{/tr}}</button>
  </p>
{{else}}
  {{if $value}}
    <ul>
      {{assign var=elts value="|"|explode:$value}}
      {{foreach from=$elts item=_elt}}
      <li class="cerfa_custom">{{tr}}Cerfa-{{$_elt}}{{/tr}}</li>
      {{/foreach}}
    </ul>
  {{/if}}
{{/if}}
