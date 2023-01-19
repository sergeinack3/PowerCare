{{*
 * @package Mediboard\system
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <style>
    .list_fields li {
      margin-top:5px;
      list-style: none;
    }

    .select_field_matching {
      margin-left : 15px;
    }
  </style>

  <script>
    addField = function(select, value_initialise) {
      var value = select ? select.value : null;

      if (!value && !value_initialise) {
        return false;
      }

      // Cas quand on arrive du Main.add
      if (value_initialise && !value) {
        value = value_initialise;
      }

      if (!value_initialise) {
        var input_name = document.getElementById(value);
        if (input_name) {
          alert($T('CPatient-msg-Field already exist for matching'));
          return false;
        }
      }

      var ul      = document.getElementById('list_field_matching');
      var _button = DOM.button({'type': 'button', 'className': 'trash', 'onclick': 'this.up().remove();', 'style': 'display:inline-block'});
      var _input  = DOM.input({'type': 'text', 'className':'input_field_matching', 'id':value, 'name':value, 'value': value, 'style' : 'display:none'});
      var div     = DOM.div({'style': 'display:inline-block'}, $T('CPatient-'+value));
      var _line   = DOM.li({'className' : 'line_config'}, _button, _input, div);

      ul.insert(_line);
    }

    // final save for the textarea
    saveText = function() {
      var textarea = getForm("edit-configuration-{{$uid}}")["c[{{$_feature}}]"][1];
      var values_input = [];

      var fields_selected = document.getElementsByClassName('input_field_matching');

      for (var i=0; i<fields_selected.length; i++) {
        values_input.push(fields_selected[i].value);
      }

      var final_value = values_input.join("|");
      $V(textarea, final_value);

      return true;
    };

    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      var field = form["c[{{$_feature}}]"][1];
      var lines = $V(field).split("|");

      if (lines.length) {
        $(lines).each(function (elt) {
          if (elt !== '') {
            addField(null, elt);
          }
        });
      }
    });
  </script>

  <textarea style="display:none;" name="c[{{$_feature}}]" {{if $is_inherited}}disabled{{/if}} class="editable">{{$value}}</textarea>
  <h3>{{tr}}CPatient-msg-Fields are available for matching{{/tr}} :</h3>
  <select name="select_field_matching" class="select_field_matching" {{if $is_inherited}}disabled{{/if}} onchange="addField(this)">
    <option value="">&mdash; {{tr}}CModeleEtiquette.choose_field{{/tr}}</option>
    {{foreach from='Ox\Mediboard\Patients\CPatient'|static:"field_matching" item=_field}}
      <option value="{{$_field}}">{{tr}}CPatient-{{$_field}}{{/tr}}</option>
    {{/foreach}}
  </select>
  <h3>{{tr}}CPatient-msg-Fields are selected for matching{{/tr}} :</h3>
  <ul class="list_fields" id="list_field_matching">
  </ul>
  <div style="clear:both;"></div>
  <p style="text-align:center;"><button type="submit" {{if $is_inherited}}disabled{{/if}} onclick="return saveText()" class="save">{{tr}}Save{{/tr}}</button></p>
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
    {{assign var=lines value="|"|explode:$value}}
    <ul class="parent_onglets opacity-30">
      {{foreach from=$lines item=_line}}
        <li>{{$_line}}</li>
      {{/foreach}}
    </ul>
  {{/if}}
{{/if}}
