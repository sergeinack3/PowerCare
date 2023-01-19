{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=light value=""}}
{{mb_default var=callback value=""}}
{{mb_default var=dont_close_modal value=0}}
{{mb_default var=wanted_type value=""}}

{{if $source->_id && 'messagerie'|module_active && $source->name|strpos:'apicrypt' === false}}
    <script type="text/javascript">
        loadSMimeKey = function (source_id) {
            var url = new Url('messagerie', 'ajax_edit_smime_key');
            url.addParam('source_id', source_id);
            url.requestUpdate('edit_s-mime_key');
        };

        Main.add(function () {
            loadSMimeKey('{{$source->_id}}');
        });
    </script>
{{/if}}

<table class="main">
    <tr>
        <td>
            <form name="editSourcePOP-{{$source->name}}" action="?m={{$m}}" method="post"
                  onsubmit="return onSubmitFormAjax(this, { onComplete : (function() {
                  {{if $callback}}{{$callback}}{{/if}}
                  {{if !$dont_close_modal}}
                    if (this.up('.modal')) {
                      Control.Modal.close();
                    } else {
                      ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$wanted_type}}');
                    }
                  {{else}}
                    ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$wanted_type}}', true);
                  {{/if}}
                    }).bind(this)})">

                <input type="hidden" name="m" value="system"/>
                <input type="hidden" name="dosql" value="do_source_pop_aed"/>
                <input type="hidden" name="source_pop_id" value="{{$source->_id}}"/>
                <input type="hidden" name="del" value="0"/>

                <input type="hidden" name="object_class" value="{{$source->object_class}}"/>
                <input type="hidden" name="object_id" value="{{$source->object_id}}"/>

                <fieldset>
                    <legend>
                        {{tr}}CSourcePOP{{/tr}}
                        {{mb_include module=system template=inc_object_history object=$source css_style="float: none"}}
                    </legend>

                    <table class="form me-no-box-shadow">
                        {{mb_include module=system template=CExchangeSource_inc}}

                        <tr>
                            <th>{{mb_label object=$source field="port"}}</th>
                            <td>{{mb_field object=$source field="port"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field="type"}}</th>
                            <td>{{mb_field object=$source field="type" typeEnum="radio"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field="auth_ssl"}}</th>
                            <td>{{mb_field object=$source field="auth_ssl" typeEnum="radio"}}</td>
                        </tr>
                        {{if $source->_id}}
                            <tr>
                                <th>{{mb_label object=$source field="object_id"}}</th>
                                <td>
                                    {{if $source->_ref_mediuser}}
                                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$source->_ref_mediuser}}
                                    {{else}}
                                        <input type="text" readonly="readonly" name="_object_view"
                                               value="{{$source->_ref_metaobject->_view}}" size="50"/>
                                    {{/if}}
                                </td>
                            </tr>
                        {{/if}}
                        <tr>
                            <th>{{mb_label object=$source field="user"}}</th>
                            <td>{{mb_field object=$source field="user" size="50"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field="password"}}</th>
                            <td>{{mb_password object=$source field="password" size="30"}}</td>
                        </tr>

                        <tr {{if !$app->_ref_user->isAdmin()}}style="display:none;"{{/if}}>
                            <th>{{mb_label object=$source field="timeout"}}</th>
                            <td>{{mb_field object=$source field="timeout" register=true increment=true form="editSourcePOP-`$source->name`" size=3 step=1 min=0}}</td>
                        </tr>

                        <tr>
                            <th>{{mb_label object=$source field="is_private"}}</th>
                            <td>{{mb_field object=$source field="is_private" }}</td>
                        </tr>

                        <tr>
                            <th>{{mb_label object=$source field="cron_update"}}</th>
                            <td>{{mb_field object=$source field="cron_update" }}</td>
                        </tr>

                        <tr>
                            <th>{{mb_label object=$source field="extension"}}</th>
                            <td>{{mb_field object=$source field="extension" }}</td>
                        </tr>

                        <tr>
                            <td class="button" colspan="2">
                                {{if $source->_id}}
                                  <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                                  <button class="trash" type="button" onclick="confirmDeletion(this.form,
                                    { ajax: 1, typeName: '', objName: '{{$source->_view}}'},
                                    { onComplete: (function() {
                                    if (this.up('.modal')) {
                                    Control.Modal.close();
                                    } else {
                                    ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$wanted_type}}');
                                    }}).bind(this.form)})">

                                      {{tr}}Delete{{/tr}}
                                  </button>
                                {{else}}
                                  <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
                                {{/if}}
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </td>
    </tr>
    {{if $source->_id && 'messagerie'|module_active && $source->name|strpos:'apicrypt' === false}}
        <tr>
            <td id="edit_s-mime_key"></td>
        </tr>
    {{/if}}

    {{if !$light}}
        <tr>
            <td>
                <fieldset>
                    <legend>{{tr}}utilities-source-pop{{/tr}}</legend>

                    <table class="main tbl">
                        <!-- Test de connexion pop -->
                        <tr>
                            <td class="button">
                                {{mb_include module=system template=CSourcePOP_tools_inc _source=$source}}
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    {{/if}}
</table>
