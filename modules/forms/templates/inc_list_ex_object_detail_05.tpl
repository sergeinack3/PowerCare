{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    loadExObjectsList = function (element, reference_class, reference_id, ex_class_id) {
        element = $(element);

        var row = element.up('tr');
        var body = element.up('tbody');
        var otherContainer = body.down("tr:first");

        var listContainer = body.down('.list-container');
        if (listContainer.visible()) {
            listContainer.hide();
            row.removeClassName('selected');
            body.removeClassName('opened');
            return;
        }

        element.up('table').select('.list-container').each(function (c) {
            c.previous('tr').removeClassName('selected');
        });

        listContainer.show();

        row.addClassName('selected');
        body.addClassName('opened');
        ExObject.loadExObjects(reference_class, reference_id, listContainer.down('td'), 1, ex_class_id, {
            other_container: otherContainer
        });
    };

    filterExClasses = function (input) {
        var keyword = input.value.toLowerCase();
        var lines = $(input).up('fieldset').select("tbody[data-name]");

        if (keyword == "") {
            lines.invoke("show");
            return;
        }

        lines.invoke("hide");

        lines.filter(function (line) {
            return line.get('name').toLowerCase().indexOf(keyword) > -1;
        }).invoke("show");
    };

    Main.add(function () {
        var form = getForm("create-new-ex_object-{{$self_guid}}");

        ExObject.initExClassAutocomplete(
            form.keywords,
            {
                self_guid:              '{{$self_guid}}',
                reference_class:        '{{$reference_class}}',
                reference_id:           '{{$reference_id}}',
                cross_context_class:    '{{$cross_context_class}}',
                cross_context_id:       '{{$cross_context_id}}',
                creation_context_class: '{{$creation_context->_class}}',
                creation_context_id:    '{{$creation_context->_id}}',
                event_names:            '{{$event_names}}'
            },
            {
                containerStyle: "max-height: 110px;"
            }
        );
    });
</script>

<table class="main tbl me-no-box-shadow me-no-align me-margin-top-4">
    <tr>
        <td class="me-bg-white me-padding-0 me-dropdown-wrapped">
            <form name="create-new-ex_object-{{$self_guid}}" method="get" onsubmit="return false">
                <input type="text" name="keywords" placeholder=" &ndash; {{tr}}common-action-Fill new form{{/tr}}"
                       style="width: 20em; max-width: 35em; float: left;"/>
            </form>

            <label style="float: right; display: block;">
                {{tr}}common-search{{/tr}}
                <input type="text"{{* type="search" *}} onkeyup="filterExClasses(this)" size="15"/>
                <button class="cancel notext not-printable compact me-tertiary me-dark"
                        onclick="var input = $(this).previous(); $V(input,''); filterExClasses(input)"></button>
            </label>
        </td>
    </tr>
</table>

<table class="main tbl treegrid me-no-align me-margin-top-2">
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
                    <td style="background: #{{$_category->color}}"></td>
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
                    <tbody data-name="{{$ex_classes.$_ex_class_id->name}}">
                    <tr>
                        <td style="background: #{{$_category->color}}; width: 1px;"></td>
                        <td class="text">
                            {{if array_key_exists($reference_id,$alerts) && array_key_exists($_ex_class_id,$alerts.$reference_id)}}
                                <span style="color: red; float: right;">
                    {{foreach from=$alerts.$reference_id.$_ex_class_id item=_alert}}
                        <span style="padding: 0 4px;"
                              title="{{tr}}CExObject_{{$_alert.ex_class->_id}}-{{$_alert.ex_class_field->name}}{{/tr}}: {{$_alert.result}}">
                        {{mb_include module=forms template=inc_ex_field_threshold threshold=$_alert.alert title="none"}}
                      </span>
                    {{/foreach}}
                  </span>
                            {{/if}}

                            <strong style="float: right;" class="ex-object-result">
                                {{if $ex_objects_results.$_ex_class_id !== null}}
                                    = {{$ex_objects_results.$_ex_class_id}}
                                {{/if}}
                            </strong>

                            <a href="#1" class="tree-folding"
                               onclick="loadExObjectsList(this, '{{$reference_class}}', '{{$reference_id}}', '{{$_ex_class_id}}'); return false;">
                                {{$ex_classes.$_ex_class_id->name}}
                            </a>
                        </td>

                        <td class="narrow">
                            {{if isset($ex_classes_creation.$_ex_class_id|smarty:nodefaults)}}
                                {{assign var=_ex_class_event value=$ex_classes_creation.$_ex_class_id|@first}}
                                <button class="add notext compact me-tertiary me-dark"
                                        onclick="showExClassForm('{{$_ex_class_id}}', '{{$reference_class}}-{{$reference_id}}', '{{$_ex_class_event->host_class}}-{{$_ex_class_event->event_name}}', null, '{{$_ex_class_event->event_name}}', '@ExObject.refreshSelf.{{$self_guid}}');">
                                    {{tr}}New{{/tr}}
                                </button>
                            {{/if}}
                        </td>

                        <td class="narrow ex-object-count" style="text-align: right;">
                            {{$_ex_objects_count}}
                        </td>
                    </tr>
                    <tr style="display: none;" class="list-container">
                        <td colspan="4"></td>
                    </tr>
                    </tbody>
                {{/if}}
            {{/if}}
        {{/foreach}}
        {{foreachelse}}
        <tr>
            <td colspan="3" class="empty">{{tr}}common-No form entered{{/tr}}</td>
        </tr>
    {{/foreach}}
</table>
