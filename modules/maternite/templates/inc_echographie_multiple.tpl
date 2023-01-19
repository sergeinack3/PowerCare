{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Echographie_{{$number_foetus}}" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$echo}}
    {{mb_key   object=$echo}}
  <input type="hidden" name="grossesse_id" value="{{$echographie->grossesse_id}}"/>
  <input type="hidden" name="_count_changes" value="0"/>
  {{mb_field object=$echo field=date      hidden=true}}
  {{mb_field object=$echo field=type_echo hidden=true}}

  {{if $grossesse->multiple}}
    {{mb_field object=$echo field=bcba hidden=true}}
    {{mb_field object=$echo field=mcma hidden=true}}
    {{mb_field object=$echo field=mcba hidden=true}}
  {{/if}}

  <table class="form">
    {{if $grossesse->multiple}}
      <tr>
        <th class="title" colspan="2">
          {{mb_label object=$echo field=num_enfant}}: {{mb_field object=$echo field=num_enfant style="width: 25px;"
          readonly=true}}
        </th>
      </tr>
    {{/if}}
    <tr>
      <td class="halfPane">
        <table class="form">
          <tr>
            <th class="category" colspan="2">{{tr}}CSurvEchoGrossesse-Measure|pl{{/tr}}</th>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$echo field=lcc}}</th>
            <td>{{mb_field object=$echo field=lcc}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=cn}}</th>
            <td>{{mb_field object=$echo field=cn}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=bip}}</th>
            <td>{{mb_field object=$echo field=bip}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=pc}}</th>
            <td>{{mb_field object=$echo field=pc}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=dat}}</th>
            <td>{{mb_field object=$echo field=dat}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=pa}}</th>
            <td>{{mb_field object=$echo field=pa}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=lf}}</th>
            <td>{{mb_field object=$echo field=lf}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=lp}}</th>
            <td>{{mb_field object=$echo field=lp}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=dfo}}</th>
            <td>{{mb_field object=$echo field=dfo}} mm</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=poids_foetal}}</th>
            <td>{{mb_field object=$echo field=poids_foetal}} g</td>
          </tr>
        </table>
      </td>
      <td class="halfPane me-valign-top">
        <table class="form">
          <tr>
            <th class="category" colspan="2">Vérifications</th>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=opn}}</th>
            <td>{{mb_field object=$echo field=opn default=""}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=avis_dan}}</th>
            <td>{{mb_field object=$echo field=avis_dan}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=pos_placentaire}}</th>
            <td>{{mb_field object=$echo field=pos_placentaire form=Echographie-`$echo->_guid` rows=2}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$echo field=remarques}}</th>
            <td class="text">{{mb_field object=$echo field=remarques form=Echographie-`$echo->_guid` rows=2}}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
