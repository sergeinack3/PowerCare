{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form">
  <tr>
    <th colspan="4" class="title">{{$header.1}} {{$header.2}} &ndash; {{$header.6}} [{{$header.8}}]</th>
  </tr>
  <tr>
    <th>Expéditeur</th>
    <td>
      {{if array_key_exists(10, $header)}}
        <small>
          [{{$header.10.0}}]
        </small>
        {{$header.10.1}}
      {{/if}}
    </td>

    <th>Destinataire</th>

    <td>
      {{if array_key_exists(11, $header)}}
        <small>
          [{{$header.11.0}}]
        </small>
        {{$header.11.1}}
      {{/if}}
    </td>
  </tr>
  <tr>
    <td colspan="4"><hr /></td>
  </tr>
  <tr>
    <th>Adresse</th>
    <td>
    {{$header.3}}<br />
    {{$header.4}}<br />
    {{$header.5}}
    </td>

    <th>Numéro de sécurité sociale</th>
    <td>{{$header.7}}</td>
  </tr>
</table>
