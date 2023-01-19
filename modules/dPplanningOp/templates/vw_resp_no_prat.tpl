{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-prat');
  });
</script>

<table class="main">
  <tr>
    <td style="white-space:nowrap;" class="narrow">
      <ul id="tabs-prat" class="control_tabs_vertical">
      {{foreach from=$sejour_no_prat key=_responsable_id item=_sejours}}
        {{assign var=_sejour value=$_sejours.0 }}
        <li><a href="#prat-{{$_responsable_id}}">{{$_sejour->_ref_praticien}} ({{$_sejours|@count}})</a></li>
      {{/foreach}}
        <li>
          <form name="repairSejour" action="?m={{$m}}&a=vw_resp_no_prat&dialog=1" method="post">
            <input type="hidden" name="repair" value="1" />
            <br />
            <button type="button" class="tick" onclick="this.form.submit()">Corriger tous les séjours valides</button>
          </form>
        </li>
      </ul>
    </td>
    <td>
      {{foreach from=$sejour_no_prat key=_responsable_id item=_sejours}}
        <table class="tbl" id="prat-{{$_responsable_id}}" style="display: none;">
        {{foreach from=$_sejours item=_sejour}}
        <tr>
          <td>
            <div class="{{if ($_sejour->_ref_consult_atu->_ref_chir->_id)}}message{{else}}error{{/if}}">
              <span onclick="window.opener.location.href='?m=planningOp&tab=vw_edit_sejour&sejour_id={{$_sejour->_id}}'" onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
              {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour}} - {{$_sejour}} ({{$_sejour->_ref_consult_atu->_ref_chir}})
              </span>
            </div>
          </td>
        </tr>
        {{/foreach}}
        </table>
      {{/foreach}}
    </td>
  </tr>
</table>