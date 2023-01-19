{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_type_agenda value=true}}

{{if !@$modules.dPboard->_can->read}}
  <div class="small-error">
    {{tr var1=$dPboard_name}}common-error-You have no access to %s module.{{/tr}}
  </div>
{{/if}}

<script>
  function showToken(form) {
    new Url()
      .addFormData(form)
      .requestModal("500px", null, {method: "post", getParameters: {"m": "mediusers", "a": "ajax_generate_token"}});
    return false;
  }
</script>

<form name="creerurl" method="post" onsubmit="return showToken(this);">
  <table class="main form">

    <tr>
      <th class="title" colspan="2">{{tr}}CPatient-msg-Generation of a synchronization link schedule{{/tr}}</th>
    </tr>

    <tr {{if !$show_type_agenda}}style="display: none;"{{/if}}>
      <th>{{tr}}CMediusers-You want to export the calendar{{/tr}}</th>
      <td>
        <label>
          <input type="checkbox" name="export[]" value="consult" checked /> {{tr}}CConsultation|pl{{/tr}}
        </label>
      </td>
    </tr>

    <tr {{if !$show_type_agenda}}style="display: none;"{{/if}}>
      <th></th>
      <td>
        <label>
          <input type="checkbox" name="export[]" value="interv" /> {{tr}}COperation|pl{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <th class="halfPane"><label for="weeks_before">{{tr}}CMediusers-Number of weeks before current date{{/tr}}</label></th>
      <td>
        <select name="weeks_before">
          <option value="0">0</option>
          <option value="1" selected>1</option>
          <option value="2">2</option>
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="weeks_after">{{tr}}CMediusers-Number of weeks after current date{{/tr}}</label></th>
      <td>
        <select name="weeks_after">
          {{foreach from=1|range:52 item=i}}
            <option value="{{$i}}" {{if $i == 2}}selected{{/if}}>{{$i}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{tr}}common-label-Group event|pl{{/tr}}</th>
      <td>
        <label>
          <input type="radio" name="group" value="0" checked /> {{tr}}common-No{{/tr}}
        </label>

        <label>
          <input type="radio" name="group" value="1" /> {{tr}}common-Per day{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <th>{{tr}}CMediusers-Event detail|pl{{/tr}}</th>
      <td>
        <label>
          <input type="radio" name="details" value="0" checked /> {{tr}}common-No{{/tr}}
        </label>

        <label>
          <input type="radio" name="details" value="1" /> {{tr}}CPatient|pl{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <th>{{tr}}common-label-Hide patient identity|pl{{/tr}}</th>
      <td>
        <input type="checkbox" name="anonymize" />
      </td>
    </tr>

    <tr>
      <th></th>
      <td>
        <button type="submit" class="submit" {{if !@$modules.dPboard->_can->read}}disabled{{/if}}>{{tr}}CMediusers-action-Generate{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>