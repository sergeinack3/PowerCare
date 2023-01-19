{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=categories ajax=$ajax}}

<script>
    Main.add(function () {
        Categories.actionsCategory();
    });
</script>

<form id="edit_category" name="edit_category" method="post">
    {{mb_class object=$category}}
    {{mb_key object=$category}}
    <table class="form">
        <tr>
            {{me_form_field nb_cells=2 mb_object=$category mb_field=name class="me-no-border"}}
            {{mb_field object=$category field=name}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_object=$category mb_field=color class="me-no-border"}}
            {{mb_field object=$category field=color form=edit_category register=true}}
            {{/me_form_field}}
        </tr>

        {{if !$category->_id || !$category->group_id}}
            <tr>
                {{me_form_field nb_cells=2 mb_object=$category mb_field=group_id class="me-no-border"}}
                    <select name="group_id">
                        {{foreach from=$groups item=_group}}
                            <option value="{{$_group->_id}}">{{$_group}}</option>
                        {{/foreach}}
                    </select>
                {{/me_form_field}}
            </tr>
        {{/if}}

        {{if $category->_id && !$category->group_id}}
            <tr>
                <td>
                    <div class="small-warning">{{tr}}CCategorieAstreinte-msg-Group id is mandatory{{/tr}}</div>
                </td>
            </tr>
        {{/if}}

        <tr>
            <td colspan="2" style="text-align: center">
                <button class="save" type="button">{{tr}}Save{{/tr}}</button>
                {{if $category->_id}}
                    <button class="trash" type="button"
                            onclick="confirmDeletion(this.form, {}, {})">{{tr}}Delete{{/tr}}</button>
                {{/if}}
            </td>
        </tr>
    </table>
</form>
