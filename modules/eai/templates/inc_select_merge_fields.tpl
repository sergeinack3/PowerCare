{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $checkMerge}}
  <div class="big-warning">
    <p>
      La fusion de ces deux objets <strong>n'est pas possible</strong> à cause des problèmes suivants :
      {{foreach from=$checkMerge item=_checkMerge}}
      <ul>
        <li> {{$_checkMerge}}</li>
      </ul>
      {{/foreach}}
    </p>
  </div>
{{else}}
  {{assign var=domain1 value=$domains.0}}
  {{assign var=domain2 value=$domains.1}}
  
  <form name="form-merge" action="?m=eai" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
    <input type="hidden" name="domain_1_id" value="{{$domain1->_id}}" />
    <input type="hidden" name="domain_2_id" value="{{$domain2->_id}}" />
    <input type="hidden" name="actor_class" value="" />
          
    <table class="form merger">
      <tr>
        <th class="category"></th>
        <th class="category">{{$domain1->_view}}</th>
        <th class="category">{{$domain2->_view}}</th>
      </tr>
      
      <tr>
        <th>{{mb_label object=$domain1 field="tag"}}</th>
        <td>
          <label>
            <input type="radio" name="tag" value="{{$domain1->tag}}" checked="checked" />
            {{$domain1->tag}}
          </label>  
        </td>
        <td>
          <label>
            <input type="radio" name="tag" value="{{$domain2->tag}}" />
            {{$domain2->tag}}
          </label>  
        </td>
      </tr>
      
      {{if !$domain1->derived_from_idex && !$domain2->derived_from_idex}}
      <tr>
        <th>{{mb_label object=$domain1 field="incrementer_id"}}</th>
        <td>
          <label>
            <input type="radio" name="incrementer_id" value="{{$domain1->incrementer_id}}" checked="checked" 
              {{if $domain1->incrementer_id}}onclick="$V(this.form.actor_id, '')"{{/if}} />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$domain1->_ref_incrementer->_guid}}')">
              {{$domain1->_ref_incrementer->_view}}
            </span>
          </label>
        </td>
        <td>
          <label>
            <input type="radio" name="incrementer_id" value="{{$domain2->incrementer_id}}" 
              {{if $domain2->incrementer_id}}onclick="$V(this.form.actor_id, '')"{{/if}} />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$domain2->_ref_incrementer->_guid}}')">
              {{$domain2->_ref_incrementer->_view}}
            </span>
          </label>
        </td>
      </tr>
      
      <tr>
        <th>{{mb_label object=$domain1 field="actor_id"}}</th>
        <td>
          <label>
            <input type="radio" name="actor_id" value="{{$domain1->actor_id}}" checked="checked" 
              onclick="$V(this.form.actor_class, '{{$domain1->actor_class}}'); 
              {{if $domain1->actor_id}}$V(this.form.incrementer_id, ''){{/if}} " />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$domain1->_ref_actor->_guid}}')">
              {{$domain1->_ref_actor->_view}}
            </span>
          </label>
        </td>
        <td>
          <label>
            <input type="radio" name="actor_id" value="{{$domain2->actor_id}}"  
              onclick="$V(this.form.actor_class, '{{$domain2->actor_class}}'); 
              {{if $domain2->actor_id}}$V(this.form.incrementer_id, ''){{/if}} " />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$domain2->_ref_actor->_guid}}')">
              {{$domain2->_ref_actor->_view}}
            </span>
          </label>
        </td>
      </tr>
      
      <tr>
        <th>{{mb_label object=$domain1 field="libelle"}}</th>
        <td>
          <label>
            <input type="radio" name="libelle" value="{{$domain1->libelle}}" checked="checked" />
            {{$domain1->libelle}}
          </label>  
        </td>
        <td>
          <label>
            <input type="radio" name="libelle" value="{{$domain2->libelle}}" />
            {{$domain2->libelle}}
          </label>  
        </td>
      </tr>
      {{/if}}
            
      <tr>
        <td colspan="100" class="button">
          <button type="submit" class="merge singleclick" onclick="return Domain.confirm()">
            {{tr}}Merge{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
{{/if}}
