{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=uid_exobject}}

{{if $cross_context_class && $cross_context_id}}
  {{assign var=can_create value=true}}
{{else}}
  {{assign var=can_create value=$readonly|ternary:false:true}}
{{/if}}

<script>
  Main.add(function(){
    {{if $can_create && $can_search}}
      var form = getForm("create-new-ex_object-{{$self_guid}}");
  
      ExObject.initExClassAutocomplete(
        form.keywords,
        {
          self_guid: '{{$self_guid}}',
          reference_class: '{{$reference_class}}',
          reference_id: '{{$reference_id}}',
          cross_context_class: '{{$cross_context_class}}',
          cross_context_id: '{{$cross_context_id}}',
          creation_context_class: '{{$creation_context->_class}}',
          creation_context_id: '{{$creation_context->_id}}',
          event_names: '{{$event_names}}'
        }
      );
    {{/if}}
  });
</script>

<table class="main layout">
  <tr>
    <td class="narrow" style="min-width: 20em; vertical-align: top;">

      {{if $can_create && $can_search}}
        <form name="create-new-ex_object-{{$self_guid}}" method="get" onsubmit="return false">
          <input type="text" name="keywords" placeholder=" &ndash; Nv. formulaire dans {{$creation_context}} "
                 style="width: 20em; max-width: 35em; float: left;"/>
        </form>
      {{/if}}

      <table class="main tbl">
        {{foreach from=$ex_class_categories item=_category}}
          {{if $_category->ex_class_category_id}}
            {{assign var=_show_catgegory value=false}}

            {{foreach from=$_category->_ref_ex_classes item=_ex_class}}
              {{assign var=_ex_class_id value=$_ex_class->_id}}
              {{if array_key_exists($_ex_class_id,$ex_objects_counts) && $ex_objects_counts.$_ex_class_id > 0}}
                {{assign var=_show_catgegory value=true}}
              {{/if}}
            {{/foreach}}

            {{if $_show_catgegory}}
              <tr>
                <td style="width: 1px; background: #{{$_category->color}}"></td>
                <th colspan="3" style="text-align: left;" title="{{$_category->description}}">
                  {{$_category}}
                </th>
              </tr>
            {{/if}}
          {{/if}}

          {{foreach from=$_category->_ref_ex_classes item=_ex_class}}
            {{assign var=_ex_class_id value=$_ex_class->_id}}

            {{if array_key_exists($_ex_class_id,$ex_objects_counts)}}
              {{assign var=_ex_objects_count value=$ex_objects_counts.$_ex_class_id}}
              {{if $_ex_objects_count}}
                <tr>
                  <td style="width: 1px; background: #{{$_category->color}}"></td>
                  <td class="text">
                    {{if array_key_exists($reference_id,$alerts) && array_key_exists($_ex_class_id,$alerts.$reference_id)}}
                      <span style="color: red; float: right;">
                        {{foreach from=$alerts.$reference_id.$_ex_class_id item=_alert}}
                          <span style="padding: 0 4px;" title="{{tr}}CExObject_{{$_alert.ex_class->_id}}-{{$_alert.ex_class_field->name}}{{/tr}}: {{$_alert.result}}">
                            {{mb_include module=forms template=inc_ex_field_threshold threshold=$_alert.alert title="none"}}
                          </span>
                        {{/foreach}}
                      </span>
                    {{/if}}

                    <strong style="float: right;" class="ex-object-result">
                      {{if array_key_exists($_ex_class_id, $ex_objects_results)}}
                        = {{$ex_objects_results.$_ex_class_id}}
                      {{/if}}
                    </strong>

                    <a href="#1" onclick="$(this).up('tr').addUniqueClassName('selected'); ExObject.loadExObjects('{{$reference_class}}', '{{$reference_id}}', 'ex_class-list-{{$uid_exobject}}', 2, '{{$_ex_class_id}}', {other_container: this.up('tr'), readonly: {{$readonly|ternary:1:0}}, can_search: {{$can_search|ternary:1:0}}, cross_context_class: '{{$cross_context_class}}', cross_context_id: '{{$cross_context_id}}'}); return false;">
                      {{$ex_classes.$_ex_class_id->name}}
                    </a>
                  </td>
                  <td class="narrow">
                    {{if $can_create && isset($ex_classes_creation.$_ex_class_id|smarty:nodefaults)}}
                      {{assign var=_ex_class_event value=$ex_classes_creation.$_ex_class_id|@first}}
                      <button class="add notext compact me-tertiary me-dark"
                              onclick="showExClassForm('{{$_ex_class_id}}', '{{$creation_context->_guid}}', '{{$_ex_class_event->host_class}}-{{$_ex_class_event->event_name}}', null, '{{$_ex_class_event->event_name}}', '@ExObject.refreshSelf.{{$self_guid}}');">
                        {{tr}}New{{/tr}}
                      </button>
                    {{/if}}
                  </td>
                  <td class="narrow me-ex-list-count" style="text-align: right;">
                    <span class="compact ex-object-count">{{$_ex_objects_count}}</span>
                    <button class="right notext compact me-tertiary me-btn-small me-dark"
                            onclick="$(this).up('tr').addUniqueClassName('selected'); ExObject.loadExObjects('{{$reference_class}}', '{{$reference_id}}', 'ex_class-list-{{$uid_exobject}}', 2, '{{$_ex_class_id}}', {other_container: this.up('tr'), readonly: {{$readonly|ternary:1:0}}, can_search: {{$can_search|ternary:1:0}}, cross_context_class: '{{$cross_context_class}}', cross_context_id: '{{$cross_context_id}}'})">
                    </button>
                  </td>
                </tr>
              {{/if}}
            {{/if}}
          {{/foreach}}
        {{/foreach}}
      </table>
    </td>
    <td id="ex_class-list-{{$uid_exobject}}" style="vertical-align: top;">
      <div class="small-info">
        Cliquez sur le bouton correspondant au formulaire dont vous voulez voir le détail
      </div>
    </td>
  </tr>
</table>
