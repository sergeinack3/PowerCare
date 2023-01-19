{{*
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td colspan="2">
      <form name="employeSelector" action="?" method="get">
      <input type="hidden" name="m" value="{{$m}}" />
      <label for="employecab_id" title="Veuillez sélectionner l'utilisateur concerné">Employé Concerné</label>
      <select name="employecab_id" onchange="this.form.submit()">
        <option value="">&mdash; Nouvel employé</option>
      {{foreach from=$listEmployes item=curr_emp}}
        <option value="{{$curr_emp->employecab_id}}" {{if $curr_emp->employecab_id == $employe->employecab_id}}selected="selected"{{/if}}>
          {{$curr_emp->_view}}
        </option>
      {{/foreach}}
      </select>
      </form>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
      <form name="editEmploye" action="?m={{$m}}" method="post" onSubmit="return checkForm(this)">
      {{mb_class object=$employe}}
      {{mb_key   object=$employe}}
      <input type="hidden" name="function_id" value="{{$employe->function_id}}" />
      <input type="hidden" name="del" value="0" />
      <table class="form">
        <tr>
          {{if $employe->employecab_id}}
          <th class="title" colspan="2">Modification de {{$employe->_view}}</th>
          {{else}}
          <th class="title" colspan="2">Création d'un employé</th>
          {{/if}}
        </tr>
        <tr>
          <th>{{mb_label object=$employe field="nom"}}</th>
          <td>{{mb_field object=$employe field="nom"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$employe field="prenom"}}</th>
          <td>{{mb_field object=$employe field="prenom"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$employe field="function"}}</th>
          <td>{{mb_field object=$employe field="function"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$employe field="adresse"}}</th>
          <td>{{mb_field object=$employe field="adresse"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$employe field="cp"}}</th>
          <td>{{mb_field object=$employe field="cp"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$employe field="ville"}}</th>
          <td>{{mb_field object=$employe field="ville"}}</td>
        </tr>
        <tr>
          <td class="button" colspan="2">
            <button class="submit" type="submit">Sauver</button>
          </td>
        </tr>
      </table>
      </form>
    </td>
    <td class="halfPane">
      {{if $employe->employecab_id}}
      <form name="params" action="?m={{$m}}" method="post" onSubmit="return checkForm(this)">
        {{mb_class object=$paramsPaie}}
        {{mb_key object=$paramsPaie}}
        <input type="hidden" name="employecab_id" value="{{$employe->employecab_id}}" />
        <table class="form">
          <tr>
            <th class="title" colspan="3">Employé</th>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="matricule"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="matricule"}}</td>
          </tr>
          <tr>
            <th class="title" colspan="3">Employeur</th>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="nom"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="nom"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="adresse"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="adresse"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="cp"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="cp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="ville"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="ville"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="siret"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="siret"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="ape"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="ape"}}</td>
          </tr>
          <tr>
            <th class="title" colspan="3">Paramètres fiscaux</th>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="smic"}}</th>
            <td colspan="2">{{mb_field object=$paramsPaie field="smic"}}</td>
          </tr>
          <tr>
            <th class="category">Cotisations</th>
            <th class="category">salariales</th>
            <th class="category">patronnales</th>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="csgnis"}}</th>
            <td>{{mb_field object=$paramsPaie field="csgnis"}}</td>
            <td>-</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="csgds"}}</th>
            <td>{{mb_field object=$paramsPaie field="csgds"}}</td>
            <td>-</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="csgnds"}}</th>
            <td>{{mb_field object=$paramsPaie field="csgnds"}}</td>
            <td>-</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="ssms"}}</th>
            <td>{{mb_field object=$paramsPaie field="ssms"}}</td>
            <td>{{mb_field object=$paramsPaie field="ssmp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="ssvs"}}</th>
            <td>{{mb_field object=$paramsPaie field="ssvs"}}</td>
            <td>{{mb_field object=$paramsPaie field="ssvp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="rcs"}}</th>
            <td>{{mb_field object=$paramsPaie field="rcs"}}</td>
            <td>{{mb_field object=$paramsPaie field="rcp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="agffs"}}</th>
            <td>{{mb_field object=$paramsPaie field="agffs"}}</td>
            <td>{{mb_field object=$paramsPaie field="agffp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="aps"}}</th>
            <td>{{mb_field object=$paramsPaie field="aps"}}</td>
            <td>{{mb_field object=$paramsPaie field="app"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="ms"}}</th>
            <td>{{mb_field object=$paramsPaie field="ms"}}</td>
            <td>{{mb_field object=$paramsPaie field="mp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="acs"}}</th>
            <td>{{mb_field object=$paramsPaie field="acs"}}</td>
            <td>{{mb_field object=$paramsPaie field="acp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="aatp"}}</th>
            <td>-</td>
            <td>{{mb_field object=$paramsPaie field="aatp"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$paramsPaie field="csp"}}</th>
            <td>-</td>
            <td>{{mb_field object=$paramsPaie field="csp"}}</td>
          </tr>
          <tr>
            <td class="button" colspan="3">
              <button class="submit" type="submit">Sauver</button>
            </td>
          </tr>
        </table>
      </form>
      {{else}}
      <table class="form">
        <tr>
          <th class="title">
            Veuillez sélectionner ou créer un employé
          </th>
        </tr>
      </table>
      {{/if}}
    </td>
  </tr>
</table>