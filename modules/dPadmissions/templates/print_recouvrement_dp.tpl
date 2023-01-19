{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include style=mediboard_ext template=open_printable}}
<script>
  Main.add(function() {
    window.print();
  });
</script>
{{foreach from=$lines_by_user key=_user_id item=_lines name=lines_by_user}}
  {{assign var=_user value=$_lines.user}}

  <table class="tbl"{{if !$smarty.foreach.lines_by_user.last}} style="page-break-after: always;"{{/if}}>
    <tr>
      <th class="title" colspan="4" onclick="window.print()">
        Feuille de suivi des remises de dépassements d'honoraires
      </th>
    </tr>
    <tr>
      <td colspan="2" style="height: 2em; vertical-align: top;">
        Chirurgien ou anesthésiste : {{$_user}}
      </td>
      <td colspan="2" style="height: 2em; vertical-align: top;">
        Date de sortie : {{$date|date_format:$conf.date}}
      </td>
    </tr>
    <tr>
      <th class="category" style="width: 30%;">{{tr}}CPatient{{/tr}}</th>
      <th class="category" style="width: 20%;">{{tr}}CFactureCabinet-_montant_dh{{/tr}}</th>
      <th class="category" style="width: 20%;">Mode de réglement</th>
      <th class="category" style="width: 30%;">Signature praticien</th>
    </tr>
    {{foreach from=$_lines.lines item=_line}}
      <tr>
        <td style="width: 30%; height: 3em;">{{$_line.patient}} {{if $_line.nda}}[{{$_line.nda}}]{{/if}}</td>
        <td style="width: 20%; height: 3em;">
          {{$_line.dp}} {{$conf.currency_symbol|html_entity_decode}}
          {{if array_key_exists('dp_prevu', $_line)}}
            (Prévu: {{$_line.dp_prevu}} {{$conf.currency_symbol|html_entity_decode}})
          {{/if}}
        </td>
        <td style="width: 20%; height: 3em;">
          <label>
            <span style="height: 10px; width: 10px; border: 1px solid black; display:inline-block;">
              {{if $_line.state == 'cheque'}}
                <i class="fa fa-check fa-lg"></i>
              {{/if}}
            </span>
            {{tr}}CReglement.mode.cheque{{/tr}}
          </label><br/>
          <label>
            <span style="height: 10px; width: 10px; border: 1px solid black; display:inline-block;">
              {{if $_line.state == 'espece'}}
                <i class="fa fa-check fa-lg"></i>
              {{/if}}
            </span>
            {{tr}}CReglement.mode.especes{{/tr}}
          </label><br/>
          <label>
            <span style="height: 10px; width: 10px; border: 1px solid black; display:inline-block;">
              {{if $_line.state == 'cb'}}
                <i class="fa fa-check fa-lg"></i>
              {{/if}}
            </span>
            {{tr}}CReglement.mode.CB{{/tr}}
          </label><br/>
          <label>
            <span style="height: 10px; width: 10px; border: 1px solid black; display:inline-block;">
              {{if $_line.state == 'virement'}}
                <i class="fa fa-check fa-lg"></i>
              {{/if}}
            </span>
            {{tr}}CReglement.mode.virement{{/tr}}
          </label><br/>
          <label>
            <span style="height: 10px; width: 10px; border: 1px solid black; display:inline-block;"></span>
            Non réglé
          </label>
        </td>
        <td style="width: 30%; height: 4em;"></td>
      </tr>
    {{/foreach}}

    <tr>
      <th class="category" colspan="4"></th>
    </tr>
    <tr>
      <td colspan="2" style="height: 4em; vertical-align: top;">Remise des DP par :</td>
      <td colspan="2" style="height: 4em; vertical-align: top;">DP remis à :</td>
    </tr>
    <tr>
      <td colspan="2"></td>
      <td colspan="2">
        Date de remise :
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height: 4em; vertical-align: top;">{{tr}}common-Signature{{/tr}} :</td>
      <td colspan="2" style="height: 4em; vertical-align: top;">{{tr}}common-Signature{{/tr}} :</td>
    </tr>
  </table>
{{foreachelse}}
  <div class="small-info">
    Aucune intervention avec des dépassements d'honoraires
  </div>
{{/foreach}}

{{mb_include style=mediboard_ext template=close_printable}}
