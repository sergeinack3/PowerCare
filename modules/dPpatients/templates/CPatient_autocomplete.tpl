{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span class="view" data-sexe="{{$match->sexe}}" data-tutelle="{{$match->tutelle}}" data-ald="{{$match->ald}}">{{$match->_view}}</span>
<div style="color: #999; font-size: 0.9em;">
  {{$match->adresse|replace:"\n":" &ndash; "}}
  {{if $match->cp || $match->ville}}
    &ndash;
    {{mb_value object=$match field=cp}}
    {{mb_value object=$match field=ville}}
  {{/if}}
</div>
<div style="color: #999; font-size: 0.9em; float: right;">{{mb_value object=$match field=naissance}}</div>
<div class="patientNotif" style="color: #999; font-size: 0.9em; float: left;" data-tel="{{$match->tel}}" data-tel2="{{$match->tel2}}"
     data-email="{{$match->email}}">{{mb_value object=$match field=tel}}</div>