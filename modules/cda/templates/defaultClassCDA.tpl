<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * {{$name}} Class
 */
class CCDA{{$name}} {

{{foreach from=$variables key=variableName item=_variable}}
{{if $_variable.max}}
  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDA{{$_variable.type}} $inst CCDA{{$_variable.type}}
   *
   * @return void
   */
  function append{{$variableName|ucfirst}}(CCDA{{$_variable.type}} $inst) {
    array_push($this->{{$variableName}}, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetList{{$variableName|ucfirst}}() {
    $this->{{$variableName}} = array();
  }

  /**
   * Getter {{$variableName}}
   *
   * @return CCDA{{$_variable.type}}[]
   */
  function get{{$variableName|ucfirst}}() {
    return $this->{{$variableName}};
  }

{{else}}
  /**
   * Setter {{$variableName}}
   *
   * @param CCDA{{$_variable.type}} $inst CCDA{{$_variable.type}}
   *
   * @return void
   */
  function set{{$variableName|ucfirst}}(CCDA{{$_variable.type}} $inst) {
    $this->{{$variableName}} = $inst;
  }

  /**
   * Getter {{$variableName}}
   *
   * @return CCDA{{$_variable.type}}
   */
  function get{{$variableName|ucfirst}}() {
    return $this->{{$variableName}};
  }

{{/if}}
{{/foreach}}

  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
{{foreach from=$props key=propName item=_prop}}
    $props["{{$propName}}"] = "{{$_prop}}";
{{/foreach}}
    return $props;
  }
}