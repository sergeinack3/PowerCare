{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  {{if $compte_rendu->valide ||$compte_rendu->_is_auto_locked}}
    <tr>
      <th class="title" colspan="2">{{tr}}Locked{{/tr}}</th>
    </tr>
    {{if $compte_rendu->valide}}
      <tr>
        <th>{{tr}}common-By{{/tr}}</th>
        <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$compte_rendu->_ref_locker}}</td>
      </tr>
      <tr>
        <th>{{tr}}the{{/tr}}</th>
        <td>{{mb_value object=$compte_rendu field=validation_date}}</td>
      </tr>
    {{/if}}
    {{if $compte_rendu->_is_auto_locked}}
      <tr>
        <td colspan="2">
          {{tr}}CCompteRendu-locked_auto{{/tr}}
        </td>
      </tr>
    {{/if}}
  {{else}}
    <tr>
      <th class="title" colspan="2">{{tr}}CCompteRendu-not_locked{{/tr}}</th>
    </tr>
  {{/if}}
</table>
