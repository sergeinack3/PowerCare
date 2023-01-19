{{*
* @package Mediboard\System
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=smtp ajax=true}}
{{mb_script module=system script=exchange_source ajax=true}}

{{mb_default var=light value=""}}
{{mb_default var=callback value=""}}
{{mb_default var=dont_close_modal value=0}}
{{mb_default var=hide_address_type value=false}}
{{mb_default var=wanted_type value=""}}

<script type="text/javascript">
    guessDataFormEmail = function (element) {
        var email = $V(element).match(/^([^@]+)@(.*)$/);
        if (!email) {
            return;
        }

        var form = element.form;

        if (!$V(form.elements.host)) {
            $V(form.elements.host, "smtp." + email[2]);
        }

        if (!$V(form.elements.user)) {
            $V(form.elements.user, email[1]);
        }
    }
</script>

<table class="main">
    <tr>
        <td>
            <form name="editSourceSMTP-{{$source->name}}" action="?m={{$m}}" method="post"
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
                <input type="hidden" name="dosql" value="do_source_smtp_aed"/>
                <input type="hidden" name="source_smtp_id" value="{{$source->_id}}"/>
                <input type="hidden" name="del" value="0"/>

                <input type="hidden" name="callback" value=""/>

                <fieldset>
                    <legend>
                        {{tr}}CSourceSMTP{{/tr}}
                        {{mb_include module=system template=inc_object_history object=$source css_style="float: none"}}
                    </legend>

                    <table class="form me-no-box-shadow">
                        {{mb_include module=system template=CExchangeSource_inc}}

                        <tr>
                            <th>{{mb_label object=$source field="email"}}</th>
                            <td>{{mb_field object=$source field="email" onchange="guessDataFormEmail(this)" size="50"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field="email_reply_to"}}</th>
                            <td>{{mb_field object=$source field="email_reply_to" size="50"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field="user"}}</th>
                            <td>{{mb_field object=$source field="user" size="50"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field="password"}}</th>
                            <td>{{mb_password object=$source field="password" size="30"}}</td>
                        </tr>
                        {{if !$hide_address_type}}
                        <tr>
                            <th>{{mb_label object=$source field=address_type}}</th>
                            <td>{{mb_field object=$source field=address_type}}</td>
                        </tr>
                        {{/if}}
                        <tr>
                            <th>{{mb_label object=$source field="port"}}</th>
                            <td>{{mb_field object=$source field="port"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field=secure}}</th>
                            <td>{{mb_field object=$source field=secure typeEnum='radio'}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field="auth"}}</th>
                            <td>{{mb_field object=$source field="auth"}}</td>
                        </tr>
                        <tr>
                            <th>{{mb_label object=$source field=asynchronous}}</th>
                            <td>{{mb_field object=$source field=asynchronous}}</td>
                        </tr>

                        <tr {{if !$can->admin}}style="display:none;"{{/if}}>
                            <th>{{mb_label object=$source field="timeout"}}</th>
                            <td>{{mb_field object=$source field="timeout" register=true increment=true form="editSourceSMTP-`$source->name`" size=3 step=1 min=0}}</td>
                        </tr>
                        <tr {{if !$can->admin}}style="display:none;"{{/if}}>
                            <th>{{mb_label object=$source field="debug"}}</th>
                            <td>{{mb_field object=$source field="debug"}}</td>
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

    <tr>
        <td>
            {{if !$light}}
            <fieldset>
                <legend>{{tr}}utilities-source-smtp{{/tr}}</legend>

                <table class="main tbl">
                    <!-- Test de connexion pop -->
                    <tr>
                        <td class="button">
                            {{mb_include module=system template=CSourceSMTP_tools_inc _source=$source}}
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        {{/if}}
        </td>
    </tr>
</table>
