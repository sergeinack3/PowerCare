{{*
* @package Mediboard\Cabinet
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{if $at->sorties_autorisees}}
      AccidentTravail.checkSortiesAutorisees();
    {{/if}}

    $('AT_sorties_autorisees_part{{$uid}}').show();
  });
</script>

<table class="form">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CAccidentTravail-title-sorties{{/tr}}
    </th>
  </tr>
  <tbody id="AT_sorties_autorisees_part{{$uid}}" {{if !$at->sorties_autorisees}}style="display: none;"{{/if}}>
    <tr>
      <th>
        {{mb_label object=$accident_travail field=sorties_autorisees}}
      </th>
      <td>
        {{mb_field object=$accident_travail field=sorties_autorisees onchange="AccidentTravail.checkSortiesAutorisees(this.form);"}}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div class="small-info">
          {{tr}}CAccidentTravail-msg-legal-sorties_autorisees{{/tr}}
        </div>
      </td>
    </tr>
    <tbody id="sorties_autorisees" {{if !$accident_travail->sorties_autorisees}}style="display: none;"{{/if}}>
      <tr class="sorties_autorisees_details">
        <th>
          {{mb_label object=$accident_travail field=sorties_restriction}}
        </th>
        <td>
          {{mb_field object=$accident_travail field=sorties_restriction typeEnum="checkbox" onchange="AccidentTravail.checkSortiesAutoriseesType(this, 'restriction');"}}
        </td>
      </tr>
      <tr id="sorties_autorisees_restriction" {{if !$accident_travail->sorties_restriction}}style="display: none;"{{/if}}>
        <th>
          {{mb_label object=$accident_travail field=date_sortie}}
        </th>
        <td>
          {{mb_field object=$accident_travail field=date_sortie form="createAT$uid" register=true onchange="AccidentTravail.checkDateSortie(this, 'restriction');"}}
        </td>
      </tr>
      <tr class="sorties_autorisees_details">
        <th>
          {{mb_label object=$accident_travail field=sorties_sans_restriction}}
        </th>
        <td>
          {{mb_field object=$accident_travail field=sorties_sans_restriction typeEnum="checkbox" onchange="AccidentTravail.checkSortiesAutoriseesType(this, 'sans_restriction');"}}
        </td>
      </tr>
      <tbody id="sorties_autorisees_sans_restriction" {{if !$accident_travail->sorties_sans_restriction}}style="display: none;"{{/if}}>
        <tr>
          <th>
            {{mb_label object=$accident_travail field=date_sortie_sans_restriction}}
          </th>
          <td>
            {{mb_field object=$accident_travail field=date_sortie_sans_restriction form="createAT$uid" register=true onchange="AccidentTravail.checkDateSortie(this, 'sans_restriction');"}}
          </td>
        </tr>
        <tr>
          <th>
            {{mb_label object=$accident_travail field=motif_sortie_sans_restriction}}
          </th>
          <td>
            {{mb_field object=$accident_travail field=motif_sortie_sans_restriction form="createAT$uid" aidesaisie="validateOnBlur: 0"}}
          </td>
        </tr>
      </tbody>
    </tbody>
  </tbody>
  <tr id="AAT_msg_sorties_temps_partiel{{$uid}}" style="display: none;">
    <td colspan="2">
      <div class="small-warning">
        {{tr}}CAccidentTravail-msg-sorties_temps_partiel{{/tr}}
      </div>
    </td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      {{assign var=previous value="at_patient_situation$uid"}}

      {{mb_include module=cabinet template=at/inc_navigation actual='sorties' previous=$previous next="at_summary$uid"}}
    </td>
  </tr>
</table>
