{{*
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dicom  script=dicom ajax=true}}

{{mb_default var=callback value=""}}
{{mb_default var=wanted_type value=""}}

<table class="main">
  <tr>
    <td>
      <form name="editSourceDicom-{{$source->name}}" action="?m={{$m}}" method="post"
            onsubmit="return onSubmitFormAjax(this, { onComplete : (function() {
            {{if $callback}}{{$callback}}{{/if}}
              if (this.up('.modal')) {
              Control.Modal.close();
              } else {
              ExchangeSource.refreshExchangeSource('{{$source->name}}', '{{$wanted_type}}');
              }}).bind(this)})">

          {{mb_class object=$source}}
          {{mb_key   object=$source}}
        <input type="hidden" name="m" value="dicom"/>
        <input type="hidden" name="dosql" value="do_source_dicom_aed"/>
        <input type="hidden" name="del" value="0"/>

        <fieldset>
          <legend>
            {{tr}}CSourceDicom{{/tr}}
            {{mb_include module=system template=inc_object_history object=$source css_style="float: none"}}
          </legend>

          <table class="form">
            <tr>
              {{mb_include module=system template=CExchangeSource_inc}}
            </tr>
            <tr>
              <th>{{mb_label object=$source field="port"}}</th>
              <td>{{mb_field object=$source field="port"}}</td>
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

      <fieldset>
        <legend>{{tr}}CSourceDicom{{/tr}}</legend>

        <table class="main form">
          <tr>
            <td class="button">
              <button type="button" class="search" onclick="Dicom.send();"
                      {{if !$source->_id}}disabled{{/if}}>
                {{tr}}utilities-source-dicom-send{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>
