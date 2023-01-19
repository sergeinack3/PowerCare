{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

</td>
</tr>
</table>
<table class="print">
  <tr>
    <td>
      <div style="float:right;">
        {{$fiche->_view}}
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" style="font-size: 110%;padding-bottom: 10px;">
        <tr>
          <th class="title">
            <a href="#" onclick="window.print()">
              {{tr}}_CFicheEi-titleFiche{{/tr}}
            </a>
          </th>
        </tr>
      </table>
      <table width="100%" style="font-size: 110%;padding-bottom: 10px;">
        <tr>
          <td style="text-align:center;">
            <strong>
              {{tr}}CFicheEi.type_incident.{{$fiche->type_incident}}-long{{/tr}}
              <br />{{$fiche->date_incident|date_format:"%A %d %B %Y à %Hh%M"}}
            </strong>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<table class="print">
  <tr>
    <td class="halfPane">
      <table width="100%" style="font-size: 100%;padding-bottom: 10px;">
        <tr>
          <th class="category" colspan="2">{{tr}}CFicheEi-user_id{{/tr}}</th>
        </tr>
        <tr>
          <th>{{tr}}Identity{{/tr}}</th>
          <td>{{$fiche->_ref_user->_view}}</td>
        </tr>
        <tr>
          <th>{{tr}}Function{{/tr}}</th>
          <td>{{$fiche->_ref_user->_ref_function->_view}}</td>
        </tr>
      </table>
    </td>
    <td class="halfPane">
      <table width="100%" style="font-size: 100%;padding-bottom: 10px;">
        <tr>
          <th class="category" colspan="2">{{tr}}CFicheEi-elem_concerne-court{{/tr}}</th>
        </tr>
        <tr>
          <th>
            {{tr}}CFicheEi.elem_concerne.{{$fiche->elem_concerne}}{{/tr}}
          </th>
          <td>{{$fiche->elem_concerne_detail|nl2br}}</td>
        </tr>
        <tr>
          <th>
            {{tr}}CFicheEi-lieu{{/tr}}
          </th>
          <td>{{$fiche->lieu}}</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table width="100%" style="font-size: 100%;padding-bottom: 10px;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}CFicheEi-evenements{{/tr}}
          </th>
        </tr>
        {{foreach from=$catFiche item=currEven key=keyEven}}
          <tr>
            <th>{{$keyEven}}</th>
            <td>
              <ul>
                {{foreach from=$currEven item=currItem}}
                  <li>{{$currItem->nom}}</li>
                {{/foreach}}
              </ul>
            </td>
          </tr>
        {{/foreach}}
      </table>
      <table width="100%" style="font-size: 100%;padding-bottom: 10px;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}_CFicheEi-infoscompl{{/tr}}
          </th>
        </tr>
        {{if $fiche->autre}}
          <tr>
            <th>{{tr}}CFicheEi-autre{{/tr}}</th>
            <td class="text">{{$fiche->autre|nl2br}}</td>
          </tr>
        {{/if}}
        <tr>
          <th>{{tr}}CFicheEi-descr_faits{{/tr}}</th>
          <td class="text">{{$fiche->descr_faits|nl2br}}</td>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-mesures{{/tr}}</th>
          <td class="text">{{$fiche->mesures|nl2br}}</td>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-descr_consequences{{/tr}}</th>
          <td class="text">{{$fiche->descr_consequences|nl2br}}</td>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-suite_even{{/tr}}</th>
          <td class="text">
            {{tr}}CFicheEi.suite_even.{{$fiche->suite_even}}{{/tr}}
            {{if $fiche->suite_even=="autre"}}
              <br />
              {{$fiche->suite_even_descr|nl2br}}
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-deja_survenu-court{{/tr}}</th>
          <td>
            {{tr}}CFicheEi.deja_survenu.{{$fiche->deja_survenu}}{{/tr}}
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<br style="page-break-after: always;" />
<table class="print">
  <tr>
    <td>
      <div style="float:right;">
        {{$fiche->_view}}
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" style="font-size: 100%;padding-bottom:20px;">
        <tr>
          <th class="category" colspan="4">
            {{tr}}_CFicheEi_validqualite{{/tr}}
          </th>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-degre_urgence{{/tr}}</th>
          <td>{{tr}}CFicheEi.degre_urgence.{{$fiche->degre_urgence}}{{/tr}}</td>
          <th>{{tr}}_CFicheEi_validBy{{/tr}}</th>
          <td class="text">
            {{$fiche->_ref_user_valid->_view}}
            <br />{{$fiche->date_validation|date_format:"%d %b %Y à %Hh%M"}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-gravite{{/tr}}</th>
          <td>
            {{tr}}CFicheEi.gravite.{{$fiche->gravite}}{{/tr}}
          </td>
          <th>{{tr}}_CFicheEi_sendTo{{/tr}}</th>
          <td class="text">
            {{$fiche->_ref_service_valid->_view}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-vraissemblance{{/tr}}</th>
          <td>
            {{tr}}CFicheEi.vraissemblance.{{$fiche->vraissemblance}}{{/tr}}
          </td>
          <th>{{tr}}CFicheEi-plainte{{/tr}}</th>
          <td>
            {{tr}}CFicheEi.plainte.{{$fiche->plainte}}{{/tr}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}CFicheEi-_criticite{{/tr}}</th>
          <td>
            {{tr}}CFicheEi._criticite.{{$fiche->_criticite}}{{/tr}}
          </td>
          <th>{{tr}}CFicheEi-commission{{/tr}}</th>
          <td>
            {{tr}}CFicheEi.commission.{{$fiche->commission}}{{/tr}}
          </td>
        </tr>
      </table>
      
      <table width="100%" style="font-size: 100%;">
        <tr>
          <th class="category" colspan="2">
            {{tr}}_CFicheEi_validchefserv{{/tr}}
          </th>
        </tr>
        {{if $fiche->service_date_validation}}
          <tr>
            <th>{{tr}}CFicheEi-service_valid_user_id-court{{/tr}}</th>
            <td>
              {{$fiche->_ref_service_valid->_view}}
              <br />{{$fiche->service_date_validation|date_format:"%d %b %Y à %Hh%M"}}
            </td>
          </tr>
          <tr>
            <th>{{tr}}CFicheEi-service_actions-court{{/tr}}</th>
            <td class="text">{{$fiche->service_actions|nl2br}}</td>
          </tr>
          <tr>
            <th>{{tr}}CFicheEi-service_descr_consequences{{/tr}}</th>
            <td class="text">{{$fiche->service_descr_consequences|nl2br}}</td>
          </tr>
        {{else}}
          <tr>
            <td colspan="2">-</td>
          </tr>
        {{/if}}

        {{if $fiche->service_date_validation}}
          <tr>
            <td colspan="2" style="padding-bottom:20px;"></td>
          </tr>
          <tr>
            <th class="category" colspan="2">
              {{tr}}_CFicheEi_validservqualite{{/tr}}
            </th>
          </tr>
          {{if $fiche->qualite_date_validation}}
            <tr>
              <th>{{tr}}_CFicheEi_validBy{{/tr}}</th>
              <td>
                {{$fiche->_ref_qualite_valid->_view}}
                <br />{{$fiche->qualite_date_validation|date_format:"%d %b %Y à %Hh%M"}}
              </td>
            </tr>
            <tr>
              <th>{{tr}}CFicheEi-qualite_date_verification-court{{/tr}}</th>
              <td>
                {{if $fiche->qualite_date_verification}}
                  {{$fiche->qualite_date_verification|date_format:"%d %B %Y"}}
                {{else}}-{{/if}}
              </td>
            </tr>
            <tr>
              <th>{{tr}}CFicheEi-qualite_date_controle-court{{/tr}}</th>
              <td>
                {{if $fiche->qualite_date_controle}}
                  {{$fiche->qualite_date_controle|date_format:"%d %B %Y"}}
                {{else}}-{{/if}}
              </td>
            </tr>
          {{else}}
            <tr>
              <td colspan="2">
                {{tr}}_CFicheEi_actionnotvalid{{/tr}}
              </td>
            </tr>
          {{/if}}
        {{/if}}
      </table>
    </td>
  </tr>
</table>
<table class="main">
  <tr>
    <td>