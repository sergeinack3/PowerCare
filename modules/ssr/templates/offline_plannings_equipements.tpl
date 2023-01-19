{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning}}
{{mb_script module=ssr script=planification}}

<script>
  Main.add(function () {
    new Control.Tabs("tabs_plateaux", {afterChange: function(c) {
      (function() {
      window[c.id].first();
      }).defer();
    }});
  });


  showPlanningOffline = function(equipement_id, equipement_guid){
    $(equipement_guid).down('.week-container').setStyle({height: '600px' });
    (function(){
      window['tab-'+kine_id].setActiveTab('equipement_'+equipement_id);
      window['planning-'+equipement_guid].updateEventsDimensions();
    }).defer();
  }
</script>

<h1 style="text-align: center;">
  {{tr}}Week{{/tr}} {{$date|date_format:'%U'}},
  {{assign var=month_min value=$monday|date_format:'%B'}}
  {{assign var=month_max value=$sunday|date_format:'%B'}}
  {{$month_min}}{{if $month_min != $month_max}}-{{$month_max}}{{/if}}
  {{$date|date_format:'%Y'}}
</h1>

<ul id="tabs_plateaux" class="control_tabs">
  {{foreach from=$plateaux item=_plateau}}
    <li>
      <a href="#plateau-{{$_plateau->_id}}">{{$_plateau->_view}}</a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$plateaux item=_plateau}}
  <div id="plateau-{{$_plateau->_id}}" style="display: none;">
    <script>
      Main.add(function() {
        (function() {
          window['plateau-{{$_plateau->_id}}'] = new Control.Tabs("tabs_equipements_{{$_plateau->_id}}", { afterChange: function(c){ 
              var equipement_guid = c.down('.planning').id; 
              c.down('.planning').down('.week-container').setStyle({height: '600px' });
              window['planning-'+equipement_guid].updateEventsDimensions();
            } 
          });
        }).defer();
      });
     </script>

    <ul id="tabs_equipements_{{$_plateau->_id}}" class="control_tabs small">
      {{foreach from=$_plateau->_ref_equipements item=_equipement}}
        <li>
          <a href="#equipement-{{$_equipement->_id}}">{{$_equipement->_view}}</a>
        </li>
      {{/foreach}}
    </ul>

   {{foreach from=$_plateau->_ref_equipements item=_equipement}}
     {{assign var=equipement_id value=$_equipement->_id}}
     <div id="equipement-{{$equipement_id}}" style="display: none;" class="plateau">
       {{$plannings.$equipement_id|smarty:nodefaults}}
     </div>
   {{/foreach}}
  </div>
{{/foreach}}