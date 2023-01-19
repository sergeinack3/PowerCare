{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2" class="title">{{tr}}Legend{{/tr}}</th>
  </tr>
  <tr>
    {{* Legend type *}}
    <td style="vertical-align: top">
      <table class="tbl">
        <tr>
          <th colspan="2" class="title">Type</th>
        </tr>
        <tr>
          <td style="background:#{{$conf.astreintes.astreinte_admin_color|regex_replace:"/#/":""}}; min-width: 40px;"></td>
          <td>{{tr}}CPlageAstreinte.type.admin-desc{{/tr}}</td>
        </tr>
        <tr>
          <td style="background:#{{$conf.astreintes.astreinte_informatique_color|regex_replace:"/#/":""}}; min-width: 40px;"></td>
          <td>{{tr}}CPlageAstreinte.type.informatique-desc{{/tr}}</td>
        </tr>
        <tr>
          <td style="background:#{{$conf.astreintes.astreinte_medical_color|regex_replace:"/#/":""}}; min-width: 40px;"></td>
          <td>{{tr}}CPlageAstreinte.type.medical-desc{{/tr}}</td>
        </tr>
        <tr>
          <td style="background:#{{$conf.astreintes.astreinte_personnelsoignant_color|regex_replace:"/#/":""}}; min-width: 40px;"></td>
          <td>{{tr}}CPlageAstreinte.type.personnelsoignant-desc{{/tr}}</td>
        </tr>
        <tr>
          <td style="background:#{{$conf.astreintes.astreinte_technique_color|regex_replace:"/#/":""}}; min-width: 40px;"></td>
          <td>{{tr}}CPlageAstreinte.type.technique-desc{{/tr}}</td>
        </tr>
      </table>
    </td>

    {{* Legend category *}}
    <td style="vertical-align: top">
      <table class="tbl">
        <tr>
          <th colspan="2" class="title">{{tr}}Categories{{/tr}}</th>
        </tr>
        {{foreach from=$categories item=_category}}
          <tr>
            <td style="background:#{{$_category->color}}; min-width: 40px;"></td>
            <td>{{$_category->name}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>

</table>