{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CInclusionProgrammeLine-msg-List of prescription line|pl{{/tr}}</legend>

  <table class="main tbl">
    <tr>
      <th>{{tr}}CPrescription{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <ul>
          {{foreach from=$inclusion_lines item=_inclusion_line}}
            {{assign var=prescription_line value=$_inclusion_line->_ref_object}}
            {{if $prescription_line->_id}}
              {{mb_include module=oxCabinet template="inc_vw_medicament" med=$prescription_line}}
            {{/if}}
            {{foreachelse}}
            <li>{{tr}}CInclusionProgrammeLine-msg-No prescription lines on the program{{/tr}}</li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
  </table>
</fieldset>
