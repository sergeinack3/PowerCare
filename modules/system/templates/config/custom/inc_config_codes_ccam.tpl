{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=codes value='|'|explode:$value}}
{{assign var=feature_name value=$_feature|replace:' ':'-'}}

{{if $is_last}}
  <script type="text/javascript">
    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      var url = new Url('ccam', 'autocompleteCcamCodes');
      url.autoComplete(form.elements['{{$feature_name}}-search_code_ccam'], '_ccam_autocomplete_{{$feature_name}}', {
          minChars: 1,
          dropdown: true,
          width: '250px',
          callback: function(input, queryString){
            return (queryString + "&_codes_ccam="+$V(input));
          },
          updateElement: function(selected) {
            addCodeCCAM(selected.get('code'), '{{$_feature}}');
          }
      });
    });

    addCodeCCAM = function(code, feature) {
      var feature_name = feature.replace(/ /g, '-');
      var elts = $(feature_name + '_list_codes');
      var elt_code = '<span class="circled ccam_' + code + '-' + feature_name + '">' +
        code + '<span style="margin-left: 5px; cursor: pointer;" onclick="deleteCCAMCode(\'' + code + '\', \'' + feature + '\')" title="{{tr}}Delete{{/tr}}"><i class="fa fa-times"></i></span>' +
        '</span>';
      elts.insert(elt_code);

      var form = getForm("edit-configuration-{{$uid}}");
      var input = $A(form.elements['c[{{$_feature}}]']).filter(function(element) {
        return !element.hasClassName('inherit-value');
      })[0];

      if ($V(input) != '') {
        var codes = $V(input).split('|');
      }
      else {
        var codes = [];
      }
      codes.push(code);
      $V(input, codes.join('|'));
    };

    deleteCCAMCode = function(code, feature) {
      var form = getForm("edit-configuration-{{$uid}}");
      var input = $A(form.elements['c[{{$_feature}}]']).filter(function(element) {
        return !element.hasClassName('inherit-value');
      })[0];
      var feature_name = feature.replace(/ /g, '-');

      if (!input.disabled) {
        var elts = $$('span.ccam_' + code + '-' + feature_name);
        if (elts.length > 0) {
          elts[0].remove();
        }

        var codes = $V(input).split('|');
        codes.splice(codes.indexOf(code), 1);
        $V(input, codes.join('|'));
      }
    };
  </script>
  <input type="hidden" name="c[{{$_feature}}]" value="{{$value}}" {{if $is_inherited}}disabled{{/if}}>

  <input type="text" name="{{$feature_name}}-search_code_ccam" {{if $is_inherited}}disabled{{/if}} class="autocomplete" size="10">
  <div style="text-align: left; color: #000; display: none; width: 200px !important; font-weight: normal; font-size: 11px; text-shadow: none;"
       class="autocomplete" id="_ccam_autocomplete_{{$feature_name}}"></div>

  <span id="{{$feature_name}}_list_codes">
    {{foreach from=$codes item=_code}}
      {{if $_code != ''}}
        <span class="circled ccam_{{$_code}}-{{$feature_name}}">
          {{$_code}}
          <span style="margin-left: 5px; cursor: pointer;" onclick="deleteCCAMCode('{{$_code}}', '{{$_feature}}');" title="{{tr}}Delete{{/tr}}"><i  class="fa fa-times"></i></span>
        </span>
      {{/if}}
    {{/foreach}}
  </span>
{{else}}
  {{foreach from=$codes item=_code name=list_codes}}
    {{if !$smarty.foreach.list_codes.first}}
      ,
    {{/if}}
    {{$_code}}
  {{/foreach}}
{{/if}}
