{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('affectationMultiplePersonnel');
    users_personnel = new TokenField(form.ids_personnel);
  });
</script>

{{assign var=pct_width value="50"}}
{{if "soins UserSejour type_affectation"|gconf == "segment"}}
  {{assign var=pct_width value="33"}}
{{/if}}

<form name="delUserMultiAffectation" action="?" method="post">
  <input type="hidden" name="sejour_affectation_id" value="">
  <input type="hidden" name="@class" value="CUserSejour">
  <input type="hidden" name="del" value="1">
</form>

<form name="affectationMultiplePersonnel" action="?" method="post" onsubmit="return PersonnelSejour.submitMultiAffectations(this);">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_affectation_personnel_sejours_aed" />
  <input type="hidden" name="_service_id" value="{{$service_id}}" />
  <input type="hidden" name="ids_sejour" value="{{$ids_sejour}}" />
  <input type="hidden" name="ids_personnel" value="" />
  {{mb_field object=$user_sejour field=debut hidden=1}}
  {{mb_field object=$user_sejour field=fin hidden=1}}

  <table class="main">
    <tr>
      <th colspan="3" class="title">{{tr}}mod-planningOp-tab-vw_affectations_multiple_personnel{{/tr}}</th>
    </tr>
    <tr>
      <td style="width:{{$pct_width}}%;">
        <fieldset style="min-height: 200px;">
          <legend>{{tr}}CSejour{{/tr}}s</legend>
          <table class="form me-no-box-shadow">
            <tr>
              <td colspan="2">
                <span style="float:right;top: -12px;position: relative;margin-bottom: -12px;">
                  <form name="filter_with_old" action="?" method="post">
                    <label title="{{tr}}CUserSejour-with_old-desc{{/tr}}">
                      <input type="checkbox" name="with_old" value="{{$with_old}}" {{if $with_old}}checked{{/if}}
                             onclick="PersonnelSejour.refreshListSejours(this.checked ? 1 : 0);"/>
                      {{tr}}CUserSejour-with_old{{/tr}}
                    </label>
                  </form>
                </span>
              </td>
            </tr>
            <tbody id="list_sejours_affectations_multiples">
              {{mb_include module=planningOp template=list_sejours_affectations_multiple}}
            </tbody>
          </table>
        </fieldset>
      </td>
      <td style="width:{{$pct_width}}%;">
        <fieldset style="min-height: 200px;">
          <legend>{{tr}}CUserSejour{{/tr}}</legend>
          <script>
            Main.add( function () {
              var form = getForm("affectationMultiplePersonnel");
              var url = new Url("personnel", "httpreq_do_personnels_autocomplete");
              url.autoComplete(form._view, form._view.id+'_autocomplete', {
                minChars: 0,
                dropdown: true,
                afterUpdateElement: function (field, selected) {
                  $V(field, '');
                  var id = selected.id.split('-')[1];
                  PersonnelSejour.addPersonnel(id);
                  var button = '<button class="remove" type="button" onclick="PersonnelSejour.removePersonnel(\''+id+'\', this);">'+selected.down('.view').innerHTML+'</button>';
                  $('view_personnels_affected').insert({before: button});
                },
                callback: function(input, queryString) {
                  return queryString + "&service_id="+$V(form._service_id)+"&use_personnel_affecte="+(form.use_user_affected.checked ? 1 : 0);
                }
              });
            });
          </script>
            <input type="text" name="_view" value="" style="width: 200px;" class="autocomplete" />
            <input type="checkbox" name="use_user_affected" value="1" checked/>
          <br/>
          <span id="view_personnels_affected"></span>
        </fieldset>
      </td>
      <td style="width:{{$pct_width}}%;">
        {{if $pct_width == "33"}}
          <fieldset style="min-height: 200px;">
            <legend>{{tr}}CTimeUserSejour-court{{/tr}}</legend>
            <table class="form me-no-box-shadow">
              <tr>
                <td>{{mb_label object=$user_sejour field=debut}}</td>
                <td>
                  {{mb_field object=$user_sejour field=_debut register=true form=affectationMultiplePersonnel canNull=false
                             onchange="PersonnelSejour.changeDatesUserSejour();"}}
                </td>
                <td>{{mb_label object=$user_sejour field=fin}}</td>
                <td>
                  {{mb_field object=$user_sejour field=_fin register=true form=affectationMultiplePersonnel canNull=false
                             onchange="PersonnelSejour.changeDatesUserSejour();"}}
                </td>
              </tr>
              <tr>
                <td colspan="4" class="button">
                  {{foreach from=$timings item=_timing name=list_timing}}
                    <label title="{{$_timing->description}} {{mb_value object=$_timing field=time_debut}}-{{mb_value object=$_timing field=time_fin}}">
                      <input type="radio" name="dates" value="{{$_timing->_id}}" {{if $smarty.foreach.list_timing.first}}checked{{/if}}
                             onclick="PersonnelSejour.changePeriodUserSejour('{{$_timing->time_debut}}', '{{$_timing->time_fin}}');"/>
                      {{$_timing->name}}
                    </label>
                  {{if $smarty.foreach.list_timing.first}}
                    <script>
                      Main.add(function() {
                        PersonnelSejour.changePeriodUserSejour('{{$_timing->time_debut}}', '{{$_timing->time_fin}}');
                      });
                    </script>
                  {{/if}}
                    {{foreachelse}}
                    <label title="Aucun timing paramétré">
                      <input type="radio" name="dates" value="aucun" checked
                             onclick="PersonnelSejour.changePeriodUserSejour('00:00:00', '23:59:00');"/>
                      {{tr}}dPAdmission.admission all the day{{/tr}}
                    </label>
                    <script>
                      Main.add(function() {
                        PersonnelSejour.changePeriodUserSejour('00:00:00', '23:59:00');
                      });
                    </script>
                  {{/foreach}}
                </td>
              </tr>
            </table>
          </fieldset>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th colspan="3" class="button">
        {{if $sejours|@count}}
          <button type="button" class="tick" onclick="this.form.onsubmit();" disabled id="submit_affectations_multiples">{{tr}}Validate{{/tr}}</button>
        {{/if}}
        <button type="button" class="close" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </th>
    </tr>
  </table>
</form>
