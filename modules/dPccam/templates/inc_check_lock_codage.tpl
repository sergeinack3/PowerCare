{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=error value=0}}

{{if !$askPassword}}
  <script type="text/javascript">
    Main.add(function() {
      getForm('lock_codage').onsubmit();
    });
  </script>
{{/if}}

<form name="lock_codage" method="post" action="?m=ccam&a=lockCodage"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true, onComplete: Control.Modal.close.curry()})">
  <input type="hidden" name="praticien_id" value="{{$praticien_id}}"/>
  <input type="hidden" name="codable_id" value="{{$codable_id}}"/>
  <input type="hidden" name="codable_class" value="{{$codable_class}}"/>
  <input type="hidden" name="lock" value="{{$lock}}"/>
  <input type="hidden" name="date" value="{{$date}}"/>
  <input type="hidden" name="export" value="{{$export}}"/>

  <table class="form"{{if !$askPassword}} style="display: none;"{{/if}}>
    {{if $askPassword}}
      <script>
        Main.add(function() {
          getForm("lock_codage").user_password.focus();
        });
      </script>
      <tr>
        <td colspan="2">
          <div class="small-info">
            {{tr}}CCodageCCAM-msg-lock{{/tr}}
          </div>
        </td>
      </tr>
      <tr>
        <th>
          <label for="user_password">{{tr}}Password{{/tr}}</label>
        </th>
        <td>
          <input type="password" name="user_password" class="notNull password str" />
        </td>
      </tr>
    {{/if}}
    {{if $codable_class == 'CSejour'}}
      {{mb_ternary var=msg test=$lock value='CCodageCCAM-msg-lock_all_codages' other='CCodageCCAM-msg-unlock_all_codages'}}
      <tr>
        <td colspan="2">
          <label for="lock_all_codage">{{tr}}{{$msg}}{{/tr}}?</label>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="text-align: center;">
          <label><input type="radio" name="lock_all_codages" value="1"/>{{tr}}Yes{{/tr}}</label>
          <label><input type="radio" name="lock_all_codages" value="0"/>{{tr}}No{{/tr}}</label>
        </td>
      </tr>
    {{/if}}
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}Validate{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
