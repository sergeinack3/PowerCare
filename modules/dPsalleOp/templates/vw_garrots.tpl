{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if 'dPsalleOp COperation garrots_multiples'|gconf && "dPsalleOp timings use_garrot"|gconf}}
  <script>
    reloadGarrots = function (operation_id) {
      var url = new Url('dPsalleOp', 'ajax_reload_garrots');
      url.addParam('operation_id', operation_id);
      url.requestUpdate('vw_garrots_' + operation_id);
    }
    retirerGarrot = function (form) {
      $V(form.elements.datetime_retrait, new Date().toDATETIME());
      onSubmitFormAjax(form, reloadGarrots.curry('{{$operation->_id}}'));
    }
    deleteGarrot = function (form) {
      $V(form.elements.del, '1');
      onSubmitFormAjax(form, reloadGarrots.curry('{{$operation->_id}}'));
    }
  </script>

  <table class="form">
    <tr>
      <th class="category" colspan="6">{{tr}}COperationGarrot|pl{{/tr}} ({{$operation->_ref_garrots|@count}})</th>
    </tr>
    <tr>
      <th class="section" style="width:12%">{{tr}}COperationGarrot-cote{{/tr}}</th>
      <th class="section" style="width:12%" title="{{tr}}COperationGarrot-pression-desc{{/tr}}">{{tr}}COperationGarrot-pression{{/tr}}</th>
      <th class="section" style="width:26%">{{tr}}COperationGarrot-datetime_pose{{/tr}}</th>
      <th class="section" style="width:26%">{{tr}}COperationGarrot-datetime_retrait{{/tr}}</th>
      <th class="section" style="width:18%">{{tr}}COperationGarrot-_duree{{/tr}}</th>
      <th class="section" style="width:6%"></th>
    </tr>
    {{foreach from=$operation->_ref_garrots item=_garrot}}
      <tr>
        <td colspan="6" style="padding-left:0">
          <form name="edit-garrot-{{$_garrot->_id}}" method="post" onsubmit="return onSubmitFormAjax(this);">
            {{mb_key object=$_garrot}}
            {{mb_class object=$_garrot}}
            {{mb_field object=$_garrot field=operation_id hidden=true}}
            <input type="hidden" name="del" value="0"/>
            <table class="main layout">
              <tr>
                <td style="width:12%;padding-left:0">
                  {{mb_field object=$_garrot field=cote onchange="this.form.onsubmit();"}}
                </td>
                <td class="" style="width:12%">
                  {{mb_field  object=$_garrot field=pression increment=1 step=50 prop=$_garrot->_props.pression
                              form="edit-garrot-`$_garrot->_id`" register=true onchange="this.form.onsubmit();"}}
                </td>
                <td style="width:26%">
                  {{mb_field  object=$_garrot field=datetime_pose form="edit-garrot-`$_garrot->_id`"
                              register=true onchange="this.form.onsubmit();"}}
                </td>
                <td style="width:26%">
                  {{if $_garrot->datetime_retrait}}
                    {{mb_field  object=$_garrot field=datetime_retrait form="edit-garrot-`$_garrot->_id`"
                                register=true onchange="this.form.onsubmit();"}}
                  {{else}}
                    {{mb_field object=$_garrot field=datetime_retrait hidden=true}}
                    <button type="submit" class="save compact"
                            onclick="retirerGarrot(this.form)">
                      {{tr}}COperationGarrot-action-Remove{{/tr}}
                    </button>
                  {{/if}}
                </td>
                <td style="width:18%">
                  {{mb_value object=$_garrot field=_duree}} {{tr}}common-minute-court{{/tr}}
                </td>
                <td style="width:6%">
                  <button type="button" class="trash notext compact"
                          onclick="deleteGarrot(this.form)">
                    {{tr}}Delete{{/tr}}
                  </button>
                </td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
      {{foreachelse}}
      <td class="empty">{{tr}}COperationGarrot.none{{/tr}}</td>
    {{/foreach}}

    <tr>
      <td colspan="6">
        <hr/>
        <form name="edit-garrot" method="post" onsubmit="return onSubmitFormAjax(this, reloadGarrots.curry('{{$operation->_id}}'));">
          {{mb_key object=$garrot}}
          {{mb_class object=$garrot}}
          {{mb_field object=$garrot field=operation_id value=$operation->_id hidden=true}}

          {{mb_label object=$garrot field=cote}}
          {{mb_field object=$garrot field=cote}}

          {{mb_label object=$garrot field=pression}}
          {{mb_field object=$garrot field=pression increment=1 step=50 prop=$garrot->_props.pression form="edit-garrot" register=true}}
          <span>
            {{tr}}common-mmhg{{/tr}}
          </span>
          <button type="submit" class="save">{{tr}}COperationGarrot-action-Put{{/tr}}</button>
        </form>
      </td>
    </tr>
  </table>
{{/if}}