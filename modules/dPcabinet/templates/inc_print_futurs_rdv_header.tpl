{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="header">
  {{if $header_content}}
    {{$header_content|smarty:nodefaults}}
  {{else}}

    <table class="main">
      <tr>
        <td class="center">
          <h1>{{$etablissement}}</h1>
          {{if $etablissement->adresse}}
            {{$etablissement->adresse}}, {{$etablissement->cp}} {{$etablissement->ville}}
          {{/if}}

          {{if $etablissement->tel}}
            <br />
            {{mb_label object=$etablissement field=tel}}: {{mb_value object=$etablissement field=tel}}
          {{/if}}

          {{if $etablissement->mail}}
            <br />
            {{mb_label object=$etablissement field=mail}}: {{$etablissement->mail}}
          {{/if}}

          {{if $code_finess}}
            <br /><br />
            N° FINESS : {{$etablissement->finess}} <br/>
            <img src="{{$code_finess}}" width="160" height="35"/>
          {{/if}}
        </td>
      </tr>
    </table>

    <span style="float: right;">Le {{$dtnow|date_format:"%d %B %Y"}} à {{$dtnow|date_format:$conf.time}}</span>
  {{/if}}
</div>