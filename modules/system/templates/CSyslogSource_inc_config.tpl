{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=light value=""}}
{{mb_default var=callback value=""}}
{{mb_default var=wanted_type value=""}}

<div class="small-info">
  {{tr}}CSyslogSource-msg-If TLS arguments not sufficient, TCP connection will be used.{{/tr}}
</div>

<table class="main">
  <tr>
    <td>
      <form name="editSyslogSource-{{$source->name}}" method="post"
            onsubmit="return onSubmitFormAjax(this, { onComplete : (function() {
            {{if $callback}}{{$callback}}{{/if}}
              if (this.up('.modal')) {
              Control.Modal.close();
              } else {
              ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$wanted_type}}');
              }}).bind(this)})">

          {{mb_key object=$source}}
          {{mb_class object=$source}}

        <fieldset>
          <legend>
              {{tr}}CSyslogSource{{/tr}}
              {{mb_include module=system template=inc_object_history object=$source css_style="float: none"}}
          </legend>

          <table class="form">
            {{mb_include module=system template=CExchangeSource_inc}}

            <tr>
              <th>{{mb_label object=$source field=port}}</th>
              <td>{{mb_field object=$source field=port}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field=protocol}}</th>
              <td>{{mb_field object=$source field=protocol typeEnum='radio'}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field=user}}</th>
              <td>{{mb_field object=$source field=user size=50}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field=password}}</th>
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"common-msg-No password"}}
              {{if $source->password}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"common-msg-Password saved"}}
              {{/if}}
              <td>{{mb_field object=$source field=password placeholder=$placeholder size=50}}</td>
            </tr>

            <tr>
              <th>{{mb_label object=$source field=ssl_certificate}}</th>
              <td>{{mb_field object=$source field=ssl_certificate size="50"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$source field=ssl_passphrase}}</th>
              {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"common-msg-No passphrase"}}
              {{if $source->ssl_passphrase}}
                {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"common-msg-Passphrase saved"}}
              {{/if}}
              <td>{{mb_field object=$source field=ssl_passphrase placeholder=$placeholder size=50}}</td>
            </tr>

            <tr {{if !$app->_ref_user->isAdmin()}}style="display:none;"{{/if}}>
              <th>{{mb_label object=$source field=timeout}}</th>
              <td>{{mb_field object=$source field=timeout register=true increment=true form="editSyslogSource-`$source->name`" size=3 step=1 min=0}}</td>
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
                      }
                      else {
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

  {{if !$light}}
    {{mb_include module=system template=CSyslogSource_tools_inc _source=$source}}
  {{/if}}
</table>
