{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit value=0}}

{{assign var="children" value=$_catalogue->_ref_catalogues_labo|@count}}
<div 
  id="catalogue-{{$_catalogue->_id}}-header" 
  class="tree-header {{if $_catalogue->_id == $catalogue_id}}selected{{/if}}">
  <a href="#1" onclick="Catalogue.edit('{{$_catalogue->_id}}');" style="float: right;">
    {{$_catalogue->_count_examens_labo}}
    {{if $_catalogue->_count_examens_labo != $_catalogue->_total_examens_labo}}
    / {{$_catalogue->_total_examens_labo}} 
    {{/if}}
    Analyses
  </a>
  <div class="tree-trigger" id="catalogue-{{$_catalogue->_id}}-trigger">showHide</div>  
  <a href="#" onclick="Catalogue.{{if $edit}}edit{{else}}select{{/if}}('{{$_catalogue->_id}}');">{{$_catalogue}}</a>
</div>
{{if $children}}
<div class="tree-content" id="catalogue-{{$_catalogue->_id}}" style="display: block;">
  {{foreach from=$_catalogue->_ref_catalogues_labo item="_catalogue"}}
  {{mb_include module=labo template=tree_catalogues}}
  {{/foreach}}
</div>
{{/if}}