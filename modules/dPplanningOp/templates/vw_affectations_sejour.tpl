{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=type_affectation value="soins UserSejour type_affectation"|gconf}}
{{assign var=see_global_users value="soins UserSejour see_global_users"|gconf}}

<script>
  {{if !$refresh}}
    addUser = function(form, sejour_id, type_affectation) {
      if (type_affectation == "segment") {
        var form_dates = getForm('datesCUserSejour');
        $V(form.debut, $V(form_dates.debut));
        $V(form.fin, $V(form_dates.fin));
      }
      return onSubmitFormAjax(form, function() {
        refreshListAffectationSejour(sejour_id);
      });
    };
    refreshListAffectationSejour = function(sejour_id, with_old) {
      var url = new Url("planningOp", "vw_affectations_sejour");
      url.addParam("sejour_id", sejour_id);
      if (!Object.isUndefined(with_old)) {
        url.addParam("with_old", with_old);
      }
      url.addParam("refresh", 1);
      url.requestUpdate('refresh_'+sejour_id);
    };

    delUser = function(form, sejour_id) {
      var options = {
        typeName:'l\'association',
        ajax: 1
      };
      var ajax = {
        onComplete: function() {
          refreshListAffectationSejour(sejour_id);
        }
      };
      confirmDeletion(form, options, ajax);
    };

    changePeriodUserSejour = function(debut, fin) {
      var form_dates = getForm('datesCUserSejour');
      $V(form_dates.debut,    $V(form_dates._debut)+' '+debut);
      $V(form_dates.fin,      $V(form_dates._fin)+' '+fin);
    };

    changeDatesUserSejour = function() {
      var form_dates = getForm('datesCUserSejour');
      var time_debut = $V(form_dates.debut).split(" ")[1];
      var time_fin = $V(form_dates.fin).split(" ")[1];
      $V(form_dates.debut,    $V(form_dates._debut)+' '+time_debut);
      $V(form_dates.fin,      $V(form_dates._fin)+' '+time_fin);
    };
  {{/if}}
</script>

<table class="tbl me-no-hover" id="refresh_{{$sejour->_id}}">
  <tr>
    <th class="title" colspan="2">Affectation de personnel pour :<br/>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">{{$sejour->_view}}</span>
    </th>
  </tr>
  {{if $type_affectation == "segment"}}
    <tr>
      <td colspan="2">
        <form name="datesCUserSejour" action="?" method="post">
          {{mb_key   object=$user_sejour}}
          {{mb_class object=$user_sejour}}
          {{mb_field object=$user_sejour field=debut hidden=1}}
          {{mb_field object=$user_sejour field=fin hidden=1}}
          <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
          <table class="tbl me-no-box-shadow me-no-border me-no-hover">
            <tr>
              <td>{{mb_label object=$user_sejour field=debut}}</td>
              <td>{{mb_field object=$user_sejour field=_debut register=true form=datesCUserSejour canNull=false onchange="changeDatesUserSejour();"}}</td>
              <td>{{mb_label object=$user_sejour field=fin}}</td>
              <td>{{mb_field object=$user_sejour field=_fin register=true form=datesCUserSejour canNull=false onchange="changeDatesUserSejour();"}}</td>
            </tr>
            <tr>
              <td colspan="4" class="button">
                {{foreach from=$timings item=_timing name=list_timing}}
                  <label title="{{$_timing->description}} {{mb_value object=$_timing field=time_debut}}-{{mb_value object=$_timing field=time_fin}}">
                    <input type="radio" name="dates" value="{{$_timing->_id}}" {{if $smarty.foreach.list_timing.first}}checked{{/if}}
                           onclick="changePeriodUserSejour('{{$_timing->time_debut}}', '{{$_timing->time_fin}}');"/>
                    {{$_timing->name}}
                  </label>
                  {{if $smarty.foreach.list_timing.first}}
                    <script>
                      Main.add(function() {
                        changePeriodUserSejour('{{$_timing->time_debut}}', '{{$_timing->time_fin}}');
                      });
                    </script>
                  {{/if}}
                {{foreachelse}}
                  <label title="Aucun timing paramétré">
                    <input type="radio" name="dates" value="aucun" checked onclick="changePeriodUserSejour('00:00:00', '23:59:00');"/>
                    Toute la journée
                  </label>
                  <script>
                    Main.add(function() {
                      changePeriodUserSejour('00:00:00', '23:59:00');
                    });
                  </script>
                {{/foreach}}
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  {{/if}}
  {{if !$see_global_users}}
    {{foreach from=$sejour->_ref_users_by_type item=_users key=type}}
      <tr>
        <th colspan="2">{{tr}}CUserSejour.{{$type}}{{/tr}}</th>
      </tr>
      <tr>
        <td>
          <form name="selUser-{{$type}}" action="?" method="post">
            {{mb_key   object=$user_sejour}}
            {{mb_class object=$user_sejour}}
            {{mb_field object=$user_sejour field=debut hidden=hidden}}
            {{mb_field object=$user_sejour field=fin hidden=hidden}}
            <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />

            <select name="user_id" onchange="addUser(this.form, '{{$sejour->_id}}', '{{$type_affectation}}');" style="width:135px">
              <option value="">{{tr}}Choose{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_mediuser list=$users.$type}}
            </select>
          </form>
        </td>

        <td {{if !$_users|@count}}class="empty"{{/if}}>
          {{foreach from=$_users item=_user_sejour}}
            <div style="clear:both;">
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_sejour->_ref_user}}
              {{if $type_affectation == "segment" && $_user_sejour->debut}}
                <span class="compact">
                <em>
                  (Du {{$_user_sejour->debut|date_format:$conf.datetime}} au {{$_user_sejour->fin|date_format:$conf.datetime}})
                </em>
              </span>
              {{/if}}
              <form name="delUser-{{$type}}-{{$_user_sejour->_id}}" action="?" method="post">
                {{mb_key   object=$_user_sejour}}
                {{mb_class object=$_user_sejour}}
                <input type="hidden" name="del" value="1" />
                <button type="button" class="trash notext me-tertiary me-dark" style="float: right;" onclick="delUser(this.form, '{{$sejour->_id}}');">{{tr}}Delete{{/tr}}</button>
              </form>
              <br/>
            </div>
            {{foreachelse}}
            {{tr}}CUserSejour.none{{/tr}}
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
  {{else}}
    <tr>
      <th colspan="2">
        <form name="filter_with_old" action="?" method="post" class="me-float-none me-margin-right-16" style="float:left;font-weight: initial;margin-right: -210px;">
          <label title="{{tr}}CUserSejour-with_old-desc{{/tr}}">
            <input type="checkbox" name="with_old" value="{{$with_old}}" {{if $with_old}}checked{{/if}}
                   onclick="refreshListAffectationSejour('{{$sejour->_id}}', this.checked ? 1 : 0)"/>
            {{tr}}CUserSejour-with_old{{/tr}}
          </label>
        </form>
        {{tr}}CUserSejour.all_sejour{{/tr}}
      </th>
    </tr>
    <tr>
      <td style="width:30%;">
        {{if $service_id && $service_id != "NP"}}
          <script>
            Main.add( function () {
              var form = getForm("addUserToSejour");
              var url = new Url("personnel", "httpreq_do_personnels_autocomplete");
              url.autoComplete(form._view, form._view.id+'_autocomplete', {
                minChars: 0,
                dropdown: true,
                updateElement : function(element){
                  $V(form.user_id, element.id.split('-')[1]);
                  $V(form._view, element.select(".view")[0].innerHTML.stripTags());
                },
                callback: function(input, queryString) {
                  return queryString + "&service_id="+$V(form._service_id)+"&use_personnel_affecte="+(form.use_personnel_affecte.checked ? 1 : 0);
                }
              });
            });
          </script>
          <form name="addUserToSejour" action="?" method="post">
            {{mb_key   object=$user_sejour}}
            {{mb_class object=$user_sejour}}
            {{mb_field object=$user_sejour field=debut hidden=hidden}}
            {{mb_field object=$user_sejour field=fin hidden=hidden}}
            {{mb_field object=$user_sejour field=user_id hidden=hidden onchange="addUser(this.form, '`$sejour->_id`', '`$type_affectation`')"}}
            <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
            <input type="hidden" name="_service_id" value="{{$service_id}}" />

            <input type="text" name="_view" value="" style="width: 200px;" class="autocomplete" />
            <input type="checkbox" name="use_personnel_affecte" value="1" checked/>
          </form>
        {{else}}
          <div class="small-warning">Patient non placé</div>
        {{/if}}
      </td>
      <td style="padding:0;">
        <table class="main tbl">
          {{assign var=day_used value=null}}
          {{foreach from=$sejour->_ref_users_sejour item=_user_sejour}}
            {{if $type_affectation == "segment" && $_user_sejour->debut && $_user_sejour->debut|date_format:$conf.date != $day_used}}
              <tr>
                <th class="section">{{$_user_sejour->debut|date_format:$conf.date}}</th>
              </tr>
              {{assign var=day_used value=$_user_sejour->debut|date_format:$conf.date}}
            {{/if}}
            <tr>
              <td>
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_sejour->_ref_user}}
                {{if $type_affectation == "segment" && $_user_sejour->debut}}
                  <span class="compact">
                    <em>
                      (Du {{$_user_sejour->debut|date_format:$conf.datetime}} au {{$_user_sejour->fin|date_format:$conf.datetime}})
                    </em>
                  </span>
                {{/if}}
                <form name="delUser-{{$_user_sejour->_id}}" action="?" method="post">
                  {{mb_key   object=$_user_sejour}}
                  {{mb_class object=$_user_sejour}}
                  <input type="hidden" name="del" value="1" />
                  <button type="button" class="trash notext me-tertiary me-dark" style="float: right;" onclick="delUser(this.form, '{{$sejour->_id}}');">
                    {{tr}}Delete{{/tr}}
                  </button>
                </form>
              </td>
            </tr>
          {{foreachelse}}
            <tr>
              <td class="empty">{{tr}}CUserSejour.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
      </td>
    </tr>
  {{/if}}
</table>
