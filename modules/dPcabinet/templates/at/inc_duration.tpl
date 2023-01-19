{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CAccidentTravail-title-duration{{/tr}}
    </th>
  </tr>
  <tr>
    <th style="width: 30%">{{mb_label object=$at field=date_constatations}}</th>
    <td>{{mb_field object=$at field=date_constatations form="createAT$uid" register=true}}</td>
  </tr>
  <tr>
    <th style="width: 30%">{{mb_label object=$at field=date_debut_arret}}</th>
    <td>{{mb_field object=$at field=date_debut_arret form="createAT$uid" register=true}}</td>
  </tr>
  <tr>
    <th><label for="duree" title="{{tr}}Duration{{/tr}}">{{tr}}Duration{{/tr}}</label></th>
    <td>
      <input type="number" class="num" name="duree" size="4" min="1" max="1092" onchange="AccidentTravail.updateEndDate();" {{if $at->_duree}}value="{{$at->_duree}}"{{/if}}/>
      <select name="unite_duree" onchange="AccidentTravail.updateMaxDuree(); AccidentTravail.updateEndDate();">
        <option value="j" {{if $at->_unite_duree && $at->_unite_duree == 'j'}}selected{{/if}}>{{tr}}Day{{/tr}}</option>
        <option value="m" {{if $at->_unite_duree && $at->_unite_duree == 'm'}}selected{{/if}}>{{tr}}Month{{/tr}}</option>
        <option value="a" {{if $at->_unite_duree && $at->_unite_duree == 'a'}}selected{{/if}}>{{tr}}Year{{/tr}}</option>
      </select>
    </td>
  </tr>
  <tr>
    <th style="width: 30%">{{mb_label object=$at field=date_fin_arret}}</th>
    <td>{{mb_field object=$at field=date_fin_arret readonly=true}}</td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      {{assign var=next value="at_patient_situation$uid"}}
      {{mb_include module=cabinet template=at/inc_navigation actual='duration' previous="at_context$uid" next=$next}}
    </td>
  </tr>
</table>
