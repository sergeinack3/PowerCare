{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=can_search value=true}}

{{assign var=self_guid value="$reference_class-$reference_id $target_element $detail $ex_class_id"}}
{{assign var=self_guid value=$self_guid|md5}}
{{assign var=self_guid value="guid_$self_guid"}}

{{if !$print}}
<script type="text/javascript">

ExObject.refreshSelf['{{$self_guid}}'] = function(start){
  start = start || 0;
  var options = {
    start: start,
    readonly: {{$readonly|ternary:1:0}},
    can_search: {{$can_search|ternary:1:0}}
  };

  var form = getForm('filter-ex_object');
  
  if (form) {
    options = Object.extend(getForm('filter-ex_object').serialize(true), {
      start: start, 
      a: 'ajax_list_ex_object',
      readonly: {{$readonly|ternary:1:0}},
      can_search: {{$can_search|ternary:1:0}}
    });
  }

  {{if $cross_context_class && $cross_context_id}}
    options.cross_context_class = '{{$cross_context_class}}';
    options.cross_context_id    = '{{$cross_context_id}}';
  {{/if}}

  {{if $creation_context && $creation_context->_id}}
    options.creation_context_class = '{{$creation_context->_class}}';
    options.creation_context_id    = '{{$creation_context->_id}}';
  {{/if}}

  {{if $event_names}}
    options.event_names = '{{$event_names}}';
  {{/if}}
  
  ExObject.loadExObjects('{{$reference_class}}', '{{$reference_id}}', '{{$target_element}}', '{{$detail}}', '{{$ex_class_id}}', options);
};

{{if $other_container}}
  Main.add(function(){
    var element = $("{{$other_container}}");
    if (element) {
      var count = element.down(".ex-object-count");
      if (count) {
        count.update("{{$ex_objects_counts.$ex_class_id}}");
      }

      {{if $ex_objects_results.$ex_class_id != null}}
        var result = element.down(".ex-object-result");
        if (result) {
          result.update("= {{$ex_objects_results.$ex_class_id}}");
        }
      {{/if}}
    }
  });
{{/if}}

</script>

{{/if}}

{{if $step && $detail < 3}}
  {{assign var=align value=null}}
  
  {{if $detail > 1}}
    {{assign var=align value=left}}
  {{/if}}
  
  {{mb_include module=system template=inc_pagination  change_page="ExObject.refreshSelf.$self_guid" total=$total current=$start step=$step align=$align show_results=false}}
{{/if}}

{{* FULL DETAIL = ALL *}}
{{if $detail == 3}}
  {{mb_include module=forms template=inc_list_ex_object_detail_3}}

{{* FULL DETAIL = COLUMNS *}}
{{elseif $detail == 2}}
  {{mb_include module=forms template=inc_list_ex_object_detail_2}}

{{* MEDIUM DETAIL *}}
{{elseif $detail == 1}}
  {{mb_include module=forms template=inc_list_ex_object_detail_1}}
  
{{elseif $detail == 0.5}}
  {{mb_include module=forms template=inc_list_ex_object_detail_05}}
  
{{* NO DETAIL *}}
{{else}}
  {{if $cross_context_class && $cross_context_id}}
    <div class="small-info">
      Seuls les formulaires paramétrés en <em>Type de contexte transversal "{{tr}}{{$cross_context_class}}{{/tr}}"</em> sont affichés
    </div>
  {{/if}}
  {{mb_include module=forms template=inc_list_ex_object_detail_0}}
{{/if}}

{{if "appFineClient"|module_active && ($detail == 0 || $detail == 0.5) && $orders_item_form}}
  <table class="tbl me-no-align"  style="width: 20em; max-width: 35em;">
    <tr>
      <th>{{tr}}CAppFineClient-msg-Order form waiting{{/tr}}</th>
    </tr>
      {{foreach from=$orders_item_form item=_order_item}}
        <tr>
          <td>{{mb_value object=$_order_item->_ref_order field=name}}</td>
        </tr>
      {{/foreach}}
  </table>
{{/if}}