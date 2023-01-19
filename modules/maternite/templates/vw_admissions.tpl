{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});
  });
</script>

{{mb_script module=admissions  script=admissions}}
{{mb_script module=maternite   script=naissance}}
{{mb_script module=maternite   script=grossesse}}
{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=files       script=file}}
{{mb_script module=sante400    script=Idex}}
{{mb_script module=patients    script=identity_validator}}

{{assign var=manage_provisoire value="maternite CGrossesse manage_provisoire"|gconf}}

<script>
  Main.add(() => {
    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}
  });
</script>

<table class="main">
  <tr>
    <td style="width: 100px">
      <table class="tbl" style="text-align: center;">
        <tr>
          <th class="title" colspan="4">
            <a style="display: inline;" href="?m=maternite&tab=vw_admissions&date={{$prev_month}}">&lt;&lt;&lt;</a>
            {{$date|date_format:"%b %Y"}}
            <a style="display: inline;" href="?m=maternite&tab=vw_admissions&date={{$next_month}}">&gt;&gt;&gt;</a>
          </th>
        </tr>
        <tr>
          <th rowspan="2">Date</th>
        </tr>
        <tr>
          <th class="text">
            <a class="{{if $view == "all"}}selected{{else}}selectable{{/if}}" title="Toutes les admissions"
               href="?m=maternite&&tab=vw_admissions&view=all">
              Adm.
            </a>
          </th>
          <th class="text">
            <a class="{{if $view == "non_prep"}}selected{{else}}selectable{{/if}}" title="Admissions non préparées"
               href="?m=maternite&&tab=vw_admissions&view=non_prep">
              Non prép.
            </a>
          </th>
          <th class="text">
            <a class="{{if $view == "non_eff"}}selected{{else}}selectable{{/if}}" title="Admissions non effectuées"
               href="?m=maternite&&tab=vw_admissions&view=non_eff">
              Non eff.
            </a>
          </th>
        </tr>

        {{foreach from=$days key=day item=counts}}
          <tr {{if $day == $date}}class="selected"{{/if}}>
            {{assign var=day_number value=$day|date_format:"%w"}}
            <td align="right"
              {{if in_array($day, $bank_holidays)}}
              style="background-color: #fc0"
              {{elseif $day_number == '0' || $day_number == '6'}}
              style="background-color: #ccc;"
              {{/if}}>
              <a href="?m={{$m}}&tab=vw_admissions&date={{$day|iso_date}}" title="{{$day|date_format:$conf.longdate}}">
                <strong>
                  {{$day|date_format:"%a"|upper|substr:0:1}}
                  {{$day|date_format:"%d"}}
                </strong>
              </a>
            </td>
            <td {{if $view == "all" && $day == $date}}style="font-weight: bold;"{{/if}}>
              {{if $counts.num1}}{{$counts.num1}}{{else}}-{{/if}}
            </td>
            <td {{if $view == "non_prep" && $day == $date}}style="font-weight: bold;"{{/if}}>
              {{if $counts.num3}}{{$counts.num3}}{{else}}-{{/if}}
            </td>
            <td {{if $view == "non_eff" && $day == $date}}style="font-weight: bold;"{{/if}}>
              {{if $counts.num2}}{{$counts.num2}}{{else}}-{{/if}}
            </td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td>
      <table class="tbl me-no-align" id="admissions">
        <tr>
          <th class="title" colspan="{{if $manage_provisoire}}11{{else}}10{{/if}}">
            <strong>
              <a href="?m=maternite&tab=vw_admissions&date={{$date_before}}" style="display: inline;">&lt;&lt;&lt;</a>
              {{$date|date_format:$conf.longdate}}
              <form name="changeDate" method="get">
                <input type="hidden" name="m" value="maternite" />
                <input type="hidden" name="tab" value="vw_admissions" />
                <input type="hidden" name="date" value="{{$date}}" onchange="this.form.submit();" />
              </form>
              <a href="?m=maternite&tab=vw_admissions&date={{$date_after}}" style="display: inline;">&gt;&gt;&gt;</a>
            </strong>
          </th>
        </tr>

        {{mb_include module=maternite template=inc_header_admissions}}

        {{foreach from=$sejours item=_sejour}}
          {{mb_include module=maternite template=inc_line_admission}}
          {{foreachelse}}
          <tr>
            <td colspan="11" class="empty">{{tr}}CSejour.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>
