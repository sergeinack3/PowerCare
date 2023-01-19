{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=skip_locales value=false}}
{{assign var=columns value=2}}

{{if array_key_exists('skip_locales',$_prop) && $_prop.skip_locales}}
  {{assign var=skip_locales value=true}}
{{/if}}

{{if array_key_exists('columns',$_prop)}}
  {{assign var=columns value=$_prop.columns}}
{{/if}}

{{if $is_last}}
  {{unique_id var=uid}}
  <script>
    Main.add(function(){
      var cont = $('set-container-{{$uid}}'),
        element = cont.down('input[type=hidden]'),
        tokenField = new TokenField(element);

      cont.select('input[type=checkbox]').invoke('observe', 'click', function(event){
        element.fire('ui:change');
        var elt = Event.element(event);
        tokenField.toggle(elt.value, elt.checked);
      });
    });
  </script>

  <div style="max-height: 24em; overflow-y: scroll; border: 1px solid #999; background: rgba(255,255,255,0.5); padding: 3px;" class="columns-{{$columns}}" id="set-container-{{$uid}}">
    {{assign var=_list value='|'|explode:$_prop.list}}
    {{assign var=_list_value value="|"|explode:$value}}
    <input type="hidden" class="{{$_prop.string}}" name="c[{{$_feature}}]" {{if $is_inherited}} disabled {{/if}} value="{{$value}}" />

    {{foreach from=$_list item=_item}}
      <label title="{{if $skip_locales}}{{$_item}}{{else}}{{tr}}config-{{$_feature|replace:' ':'-'}}.{{$_item}}{{/tr}}{{/if}}">
        <input type="checkbox" value="{{$_item}}" {{if in_array($_item, $_list_value, true)}} checked {{/if}} {{if $is_inherited}} disabled {{/if}} />
        {{if $skip_locales}}
          {{$_item}}
        {{else}}
          {{tr}}config-{{$_feature|replace:' ':'-'}}.{{$_item}}{{/tr}}
        {{/if}}
      </label>
      <br />
    {{/foreach}}

    {{* Allow removing impossible values from list*}}
    {{foreach from=$_list_value item=_value}}
      {{if !in_array($_value, $_list, true)}}
        <label title="{{tr}}common-unknown-value{{/tr}} : {{$_value}}" style="background-color: red">
          <input type="checkbox" value="{{$_value}}" checked {{if $is_inherited}} disabled {{/if}} />
          {{$_value}}
        </label>
        <br />
      {{/if}}
    {{/foreach}}
  </div>
{{else}}
  {{assign var=_list value="|"|explode:$value}}
  {{foreach from=$_list item=_item name=_list}}
    {{if $skip_locales}}
      {{$_item}}{{if !$smarty.foreach._list.last}}, {{/if}}
    {{else}}
      {{tr}}config-{{$_feature|replace:' ':'-'}}.{{$_item}}{{/tr}}{{if !$smarty.foreach._list.last}}, {{/if}}
    {{/if}}
  {{/foreach}}
{{/if}}