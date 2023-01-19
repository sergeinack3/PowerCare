/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Modified version of TreeView from https://github.com/kaiwren/treeview
 */
function TreeView(target, options) {
  this.options = Object.extend({
    titleClickable: true
  }, options);
  
  this.target = $(target);
  this.initialize();
}

Object.extend(TreeView, {
  classes: {
    treeview: 'treeview',
    expandable: 'expandable',
    collapsable: 'collapsable',
    last: 'last',
    lastCollapsable: 'lastCollapsable',
    lastExpandable: 'lastExpandable',
    hitarea: 'hitarea',
    collapsableHitArea: 'collapsable-hitarea',
    lastCollapsableHitArea: 'lastCollapsable-hitarea',
    expandableHitArea: 'expandable-hitarea',
    lastExpandableHitArea: 'lastExpandable-hitarea'
  },

  buildHitArea: function(){
    return new Element('div', {'class': TreeView.classes.hitarea});
  }
});

TreeView.prototype = {
  initialize: function() {
    this.render();
  },

  extractLastBranches: function(){
    return this.hasUL(this.target.select("li:last-child"));
  },
  
  hasUL: function(list, not) {
    return list.filter(function(elt) { 
      var n = elt.select('ul').length; 
      if (not) return n == 0;
      return n > 0;
    });
  },

  extractBranches: function() {
    return this.hasUL(this.target.select('li'));
  },

  extractLeaves: function(){
    return this.hasUL(this.target.select("li:last-child"), true);
  },

  render: function() {
    this.target.addClassName(TreeView.classes.treeview);

    this.extractBranches().each(function(element) {
      var hitArea = TreeView.buildHitArea();
      var children = element.childElements().filter(function (elt) {
        return elt.nodeName == "UL";
      });

      if(children.length != 1){ throw "Error: Branch node contains more than one ul tag"}

      element.addClassName(TreeView.classes.collapsable);
      element.store('hitArea', hitArea);
      element.store('children', children[0]);
      hitArea.store('node', element);
      element.insert({top: hitArea.addClassName(TreeView.classes.collapsableHitArea)});

      var clickEvent = this.toggle.bind(this).curry(element);
      
      hitArea.observe('click', clickEvent);
      
      /*if (this.options.titleClickable) {
        element.observe('click', function(e){
          clickEvent();
          Event.stop(e);
        });
      }*/
    }, this);

    this.extractLastBranches().each(function(element) {
      element.addClassName(TreeView.classes.lastCollapsable);
      element.retrieve('hitArea').addClassName(TreeView.classes.lastCollapsableHitArea);
    });

    this.extractLeaves().each(function(leaf){
      leaf.addClassName(TreeView.classes.last);
    });
  },

  toggle: function(node){
    var children = node.retrieve('children');
    
    if(children.visible()){
      this.collapse(node);
    }
    else {
      this.expand(node);
    }
  }, 
  
  collapse: function(node) {
    var children = node.retrieve('children');
    var hitArea = node.retrieve('hitArea');
    var classes = TreeView.classes;
    
    children.hide();
    
    if(node.hasClassName(classes.collapsable)){
      node.removeClassName(classes.collapsable);
      node.addClassName(classes.expandable);

      hitArea.removeClassName(classes.collapsableHitArea);
      hitArea.addClassName(classes.expandableHitArea);
    }
    if(node.hasClassName(classes.lastCollapsable)){
      node.removeClassName(classes.lastCollapsable);
      node.addClassName(classes.lastExpandable);

      hitArea.removeClassName(classes.lastCollapsableHitArea);
      hitArea.addClassName(classes.lastExpandableHitArea);
    }
  },
  
  expand: function(node) {
    var children = node.retrieve('children');
    var hitArea = node.retrieve('hitArea');
    var classes = TreeView.classes;
    
    children.show();
    
    if(node.hasClassName(classes.expandable)){
      node.removeClassName(classes.expandable);
      node.addClassName(classes.collapsable);

      hitArea.removeClassName(classes.expandableHitArea);
      hitArea.addClassName(classes.collapsableHitArea);
    }
    if(node.hasClassName(classes.lastExpandable)){
      node.removeClassName(classes.lastExpandable);
      node.addClassName(classes.lastCollapsable);

      hitArea.removeClassName(classes.lastExpandableHitArea);
      hitArea.addClassName(classes.lastCollapsableHitArea);
    }
  },
  
  collapseAll: function(){
    this.extractBranches().each(this.collapse);
  }
};

