{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span class="view" data-surnom="{{$match->surnom}}"
      data-nom="{{$match->nom}}"
      data-nom_jeune_fille="{{$match->nom_jeune_fille}}"
      data-prenom="{{$match->prenom}}"
      data-adresse="{{$match->adresse}}"
      data-cp="{{$match->cp}}"
      data-ville="{{$match->ville}}"
      data-tel="{{$match->tel}}"
      data-mob="{{$match->mob}}"
      data-fax="{{$match->fax}}"
      data-ean="{{$match->ean}}"
      data-ean_base="{{$match->ean_base}}"
      data-ean_id="{{$match->ean_id}}"
      data-type_pec="{{$match->type_pec}}"
      data-urssaf="{{$match->urssaf}}"
      data-parente="{{$match->parente}}"
      data-email="{{$match->email}}"
      data-remarques="{{$match->remarques}}">
  {{if $show_view}}{{$match->_view}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}
</span>
{{if $match->surnom}}
  <div class="compact">{{$match->surnom}}</div>{{/if}}
<div class="compact">{{$match->cp}} {{$match->ville}}</div>