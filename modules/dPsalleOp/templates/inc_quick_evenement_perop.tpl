{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  saveQuickEvent = function (incident, element) {
    {{if $limit_date_min}}
      var limit_date_min = '{{$limit_date_min}}';

      if ($V(element.form.datetime) < limit_date_min) {
        alert($T('CAnesthPerop-msg-You can no longer record events on a date and time earlier than %s', '{{$limit_date_min|date_format:$conf.datetime}}'));
        return false;
      }
    {{/if}}

    $V(element.form.incident, incident);
    $V(element.form.libelle, element.getText());

    onSubmitFormAjax(element.form, function(){Control.Modal.close()});
  };
</script>

<h2>{{$operation}}</h2>

{{unique_id var=uid}}
<form name="addAnesthPerop-{{$uid}}" action="?" method="post">
  {{mb_key   object=$evenement}}
  {{mb_class object=$evenement}}
  <input type="hidden" name="operation_id" value="{{$operation->_id}}" />
  <input type="hidden" name="datetime" value="now" />
  <input type="hidden" name="incident" value="0" />
  <input type="hidden" name="libelle" value="0" />
  {{mb_field object=$evenement field=user_id value=$app->_ref_user->_id hidden=true}}

  {{foreach from=$evenement->_aides item=_by_label}}
    {{foreach from=$_by_label.no_enum item=_aides key=_owner}}
      <div style="text-align: right;">
        <em>{{$_owner}} &ndash; </em>
      </div>

      <table class="main tbl">
        <tr>
          <td class="text">
            {{foreach from=$_aides item=_aide}}
              <div style="display: inline-block; width: 20em; margin-bottom: 3px;">
                <button type="button" class="tick notext compact"
                        onclick="saveQuickEvent(0, this);">{{$_aide}}</button>

                <button type="button" class="warning notext compact" title="{{tr}}CAnesthPerop-incident{{/tr}}"
                        onclick="saveQuickEvent(1, this);">{{$_aide}}</button>
                {{$_aide}}
              </div>
            {{/foreach}}
          </td>
        </tr>
      </table>
    {{/foreach}}
  {{/foreach}}
</form>