/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

dataModel = window.dataModel || {
    /**
     * Fonction pour afficher la modale de d�tail d'une classe
     *
     * @param classe Nom de la classe
     */
    modal_details: function (classe) {
      var url = new Url('dPdeveloppement', 'ajax_pop_details');
      url.addParam('class', classe);
      url.requestModal('80%', '90%', {
        title: classe
      });
    },
    /**
     * Fonction qui pr�pare les donn�es n�cessaires � la cr�ation du graph. Appelle dataModel.drawGraph et lui passe
     * les donn�es n�cessaires
     *
     * @param graph      Un objet contenant toutes les informations pour cr�er le graph
     * @param backs      Un object contenant toutes les backprops � afficher dans le graph
     * @param backs_name Noms des backsrefs
     * @param inv_class  Noms des attributs des classes vers lequelles ont pointe
     * @param opt        Options du graphe (object sous forme JSON)
     */
    prepareGraph: function (graph, backs, backs_name, inv_class, opt) {
      // Cr�ation des noeuds du graph
      var nodes = dataModel.makeNodes(graph, opt.hierarchy_sort);
      var id = nodes.length;

      // cr�ation des backprops
      var backNodesEdges = dataModel.makeBacks(graph, backs, backs_name, id, opt.hierarchy_sort);
      // Cr�ation des noeuds des backsprops
      for (var idx in backNodesEdges.nodes) {
        if (typeof(backNodesEdges.nodes[idx]) == "object") {
          nodes.push(backNodesEdges.nodes[idx]);
        }
      }
      // Cr�ation des arr�tes des backprops
      var edges = [];
      for (idx in backNodesEdges.edges) {
        if (typeof(backNodesEdges.edges[idx]) == "object") {
          edges.push(backNodesEdges.edges[idx]);
        }
      }
      // Cr�ation des arr�tes du graph
      var dataEdges = dataModel.makeEdges(graph, inv_class);
      for (idx in dataEdges) {
        // V�rification du type pour ne pas it�rer sur les propri�t�s _proto_ qui sont des fonctions
        if (typeof(dataEdges[idx]) == "object") {
          edges.push(dataEdges[idx]);
        }
      }
      dataModel.drawGraph(nodes, edges, opt.hierarchy_sort, opt.show_hover);
    },
    /**
     * Fonction qui cr�e les options du graph puis cr�e le graph. Appelle dataModel.setEventsGraph
     *
     * @param nodes           Conteneur de type vis.DataSet qui contient les noeuds du graph
     * @param edges           Conteneur de type vis.DataSet qui contient les arr�tes du graph
     * @param hierarchy_sort  Algorithme de placement des noeuds
     * @param show_hover      D�sactive les �v�nements au survol du graph si n�cessaire
     */
    drawGraph: function (nodes, edges, hierarchy_sort, show_hover) {
      App.loadJS(['lib/visjs/vis'], function (vis) {
        // Cr�ation des DataSet pour cr�er le graph
        var edge_set = new vis.DataSet(edges);
        var node_set = new vis.DataSet(nodes);
        // Options de base du graphe
        var options = {
          physics: {
            enabled: false
          },
          edges: {
            smooth: false,
            arrowStrikethrough: false,
            arrows: {
              to: {
                enabled: true,
                scaleFactor: 1
              },
              middle: {
                enabled: false,
                scaleFactor: 1
              },
              from: {
                enabled: false,
                scaleFactor: 1
              }
            },
            hoverWidth: 0.5,
            font: {
              face: "Calibri, sans-serif",
              align: 'horizontal',
              size: 10
            }
          },
          nodes: {
            shapeProperties: {
              useImageSize: true
            }
          },
          layout: {
            hierarchical: {
              enabled: true,
              levelSeparation: 200,
              nodeSpacing: 400,
              edgeMinimization: true,
              blockShifting: true
            }
          },
          // Permet les �v�nements de type "on mouse hover"
          interaction: {
            hover: true
          }
        };

        // Changement des options en fonction des choix de l'utilisateur
        if (show_hover == 0) {
          options.interaction.hover = false;
        }
        switch (hierarchy_sort) {
          case "hubsize":
            options.layout.hierarchical.sortMethod = "hubsize";
            break;
          case "directed":
            options.layout.hierarchical.sortMethod = "directed";
            break;
          case "none":
            // Active la physique pour le placement inital des noeuds
            options.physics = {
              enabled: true,
              barnesHut: {
                avoidOverlap: 1
              }
            };
            options.layout = {};
            break;
          case "basic":
            options.layout.hierarchical.direction = "LR";
            options.layout.hierarchical.levelSeparation = 300;
            options.layout.hierarchical.nodeSpacing = 100;
            break;
          default:
            break;
        }
        // Element dans lequel on veut afficher le graph
        var container = document.getElementById('graph_draw');
        var data = {
          nodes: node_set,
          edges: edge_set
        };
        // Cr�ation du graph
        var network = new vis.Network(container, data, options);
        // Appel de la fonction pour cr�er les �v�nements
        dataModel.setEventsGraph(network, edge_set, node_set);
      });
    },
    /**
     * Fonction qui configure les diff�rents �v�nements qui peuvent avoir lieu sur le graph
     *
     * @param network  Le graph cr�� auquel on souhaite ajouter des �v�nements
     * @param edge_set Conteneur de type vis.DataSet qui contient les arr�tes du graph
     * @param node_set Conteneur de type vis.DataSet qui contient les noeuds du graph
     */
    setEventsGraph: function (network, edge_set, node_set) {
      // Gestion des �v�nements
      network.on('click', onClick);
      network.on('doubleClick', onDoubleClick);
      // Action lorsque la souris passe sur un noeud
      network.on('hoverNode', function (params) {
        onHoverNode(params.node);
      });
      // Action lorsque la souris passe sur une arr�te
      network.on('hoverEdge', function (params) {
        onHoverEdge(params.edge);
      });
      // Action lorsque la souris part d'un noeud
      network.on('blurNode', clearEdges);
      // Action lorsque la souris part d'une arr�te
      network.on('blurEdge', clearEdges);
      // D�sactive la physique apr�s que les noeuds aient �t� plac�s
      network.once('afterDrawing', disablePhysic);

      function disablePhysic() {
        var options = {
          physics: {enabled: false}
        };
        var pos = network.getPositions();
        var move = {
          position: {x: pos[0].x, y: pos[0].y},
          scale: 1
        };
        network.moveTo(move);
        network.setOptions(options);
      }

      // Supprime les labels des arr�tes
      function clearEdges() {
        edge_set.forEach(function (edge) {
          edge_set.update({
            id: edge.id,
            label: ''
          });
        })
      }

      // Affiche les labels des arr�tes li�es au noeud
      function onHoverNode(nodeId) {
        var connectedEdges = network.getConnectedEdges(nodeId);
        for (var edge in connectedEdges) {
          // V�rification du type pour ne pas it�rer sur les propri�t�s _proto_ qui sont des fonctions
          if (typeof connectedEdges[edge] == "string") {
            onHoverEdge(connectedEdges[edge]);
          }
        }
      }

      // Affiche le label de l'arr�te
      function onHoverEdge(nodeId) {
        var edge_hover = edge_set.get(nodeId);
        if (edge_hover.labelC) {
          edge_set.update({
            id: edge_hover.id,
            label: edge_hover.labelC
          });
        }
      }

      // Le double click appel deux fois la fonction click
      // Permet de v�rifier si on ex�cute un seul clique ou un double clique
      var doubleClickTime = 0;
      var threshold = 200;

      function onClick(properties) {
        var t0 = new Date();
        if (t0 - doubleClickTime > threshold) {
          setTimeout(function () {
            if (t0 - doubleClickTime > threshold) {
              doOnClick(properties, node_set);
            }
          }, threshold);
        }
      }

      // Si un seul click on affiche les d�tails de la classe
      function doOnClick(properties, node_set) {
        if (properties.nodes[0] || properties.nodes[0] === 0) {
          var node = node_set.get(properties.nodes[0]);
          dataModel.modal_details(node.title);
        }
      }

      // Si un double click on recentre sur la nouvelle classe
      function onDoubleClick(properties) {
        doubleClickTime = new Date();
        if (properties.nodes[0]) {

          var node = node_set.get(properties.nodes[0]);
          var form = getForm("filter_class");
          $V(form.object_class, node.title);
        }
      }
    },
    /**
     * Fonction qui renvoie une chaine de caract�res correspondant au SVG qui sera afficher pour un noeud
     *
     * @param classe Nom de la classe pour laquelle on cr�e le SVG
     * @param props  Propri�t�s de la classe pour laquelle on cr�e le SVG
     * @param id     Permet de savoir s'il s'agit de la classe s�lectionn�e ou non
     * @param back   Permet de savoir s'il s'agit d'une backprop ou non
     *
     * @returns {string}
     */
    makeSVG: function (classe, props, id, back) {
      var height = 25;
      if (props) {
        height += props.length * 12;
      }
      // D�but du SVG
      var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200px" height="' + height + 'px">';
      // Le noeud d'id 0 est la classe principale
      if (id === 0) {
        svg += '<rect x="0" y="0" width="100%" height="100%" fill="#eeeeee" stroke-width="1" stroke="#000000" ></rect>';
      }
      else {
        // Noeud �tant une backprop
        if (back) {
          svg += '<rect x="0" y="0" width="100%" height="100%" fill="#bbbbbb" stroke-width="1" stroke="#000000" ></rect>';
        }
        // Noeud normal
        else {
          svg += '<rect x="0" y="0" width="100%" height="100%" fill="#ffffff" stroke-width="1" stroke="#000000" ></rect>';
        }
      }
      // Cr�ation du conteneur du texte
      svg += '<foreignObject x="5" y="5" width="100%" height="100%">' +
        '<div xmlns="http://www.w3.org/1999/xhtml" style="font-size:10px; font-family: Calibri, sans-serif; white-space: nowrap; width: 190px; overflow: hidden; text-overflow: ellipsis;">' +
        '<strong>' + classe + '</strong><br/>';

      // Cr�ation des propri�t�s de la classe
      var i = 0;
      if (props) {
        for (var text in props) {
          // V�rification du type pour ne pas it�rer sur les propri�t�s _proto_ qui sont des fonctions
          if (typeof(props[text]) != "string") {
            continue;
          }
          svg += props[text] + '<br/>';
          i++;
        }
      }
      svg += '</div></foreignObject></svg>';
      return svg;
    },
    /**
     * Cr�e les noeuds du graph sous forme d'images SVG et les renvoie sous forme de tableau
     *
     * @param graph     Objet contenant les informations des noeuds et liens du graphe
     * @param hierarchy Algorithme de placement des noeuds
     *
     * @returns {Array} Tableau de noeuds
     */
    makeNodes: function (graph, hierarchy) {
      var id = 0;
      var nodes = [];
      // Cr�ation des noeuds sous forme d'une image SVG contenant les informations � afficher
      for (var object in graph) {
        if (!graph.hasOwnProperty(object)) {
          continue;
        }
        var name = graph[object].class + ' (' + graph[object].class_db + ')';
        var node = dataModel.makeSVG(name, graph[object].props, id, false);
        var url = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(node);
        var node_props = {};

        node_props = {
          id: id,
          title: graph[object].class,
          image: url,
          shape: 'image'
        };


        if (hierarchy == "hierarchy" || "basic") {
          node_props.level = graph[object].options.level;
        }
        nodes.push(node_props);
        graph[object].options.id = id;
        id++;
      }
      return nodes;
    },
    /**
     * Cr�e les noeuds et arr�tes des backprops et les renvoie sous forme de tableau
     *
     * @param graph       Objet contenant les informations des noeuds et liens du graphe
     * @param backs       Objet contenant les informations des backprops � afficher
     * @param backs_name  Noms des backprops
     * @param id          Identifiant � partir duquel commencer pour les noeuds
     * @param hierarchy   Algorithme de placement des noeuds utilis�
     *
     * @returns {Object}  Object contenant les noeuds et leurs arr�tes
     */
    makeBacks: function (graph, backs, backs_name, id, hierarchy) {
      var nodes = [];
      var edges = [];
      var class_per_level = 0;
      var level = 0;
      for (var back in backs) {
        // V�rification du type pour ne pas it�rer sur les propri�t�s _proto_ qui sont des fonctions
        if (typeof(backs[back]) != "string") {
          continue;
        }
        var node = dataModel.makeSVG(backs[back], null, id, true);
        var url = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(node);
        var node_props = {
          id: id,
          title: backs[back],
          image: url,
          shape: 'image'
        };
        if (class_per_level > 5 && hierarchy != "basic") {
          class_per_level = 0;
          level++;
        }
        if (hierarchy == 'hierarchy') {
          node_props.level = level;
        }
        if (hierarchy == "basic") {
          node_props.level = 1;
        }

        nodes.push(node_props);
        edges.push({
          from: id,
          to: 0,
          length: 400,
          title: backs_name[backs[back]] + " ref " + graph[0].class,
          color: {color: '#bbbb00', highlight: '#bbbb00', hover: '#bbbb00'},
          arrows: {to: {enabled: true, scaleFactor: 1, type: 'umlcomposition'}},
          arrowStrikethrough: true
        });
        id++;
        class_per_level++;
      }
      return {nodes: nodes, edges: edges, level: level};
    },
    /**
     * Cr�e les arr�tes du graph et les renvoie sous forme de tableau
     *
     * @param graph     Objet contenant les informations n�cessaires � la cr�ation des noeuds et des arr�tes
     * @param inv_class Objet contenant les backprops des classes li�es � la classe principale
     *
     * @returns {Array} Tableau contenant toutes les arr�tes cr��es
     */
    makeEdges: function (graph, inv_class) {
      var edges = [];
      for (var i = 0; i < graph.length; i++) {
        // H�ritage : cr�ation du lien vers le parent
        if (graph[i].options.parent) {
          edges.push({
            from: 0,
            to: graph[i].options.id,
            length: 400,
            labelC: graph[0].class + " h�rite de " + graph[i].class,
            color: {color: '#cc0000', highlight: '#cc0000', hover: '#cc0000'},
            arrows: {to: {enabled: true, scaleFactor: 1, type: 'umlinheritance'}}
          })
        }
        // Cr�ation des autres liens
        for (var lien in graph[i].links) {
          // V�rification du type pour ne pas it�rer sur les propri�t�s _proto_ qui sont des fonctions
          if (typeof(graph[i].links[lien]) != "string") {
            continue;
          }
          var to = null;
          for (var j = 0; j < graph.length; j++) {
            if (graph[j].class == graph[i].links[lien]) {
              to = graph[j];
            }
          }
          if (!to) {
            continue;
          }
          var link_titles = [];
          if (inv_class[graph[i].links[lien]]) {
            inv_class[graph[i].links[lien]].split("/").each(function(link) {
              link_titles.push(graph[i].links[lien] + "." + link + " ref " + graph[0]["class"]);
            }) ;
          }

          var edge = {
            from: graph[i].options.id,
            to: to.options.id,
            length: 400,
            title:  link_titles.join("<br/>"),
            labelC: lien + " ref",
            font: {
              align: 'horizontal'
            },
            color: {color: '#0055ff', highlight: '#0055ff', hover: '#0055ff'},
            arrows: {to: {enabled: true, scaleFactor: 1, type: 'umlaggregation'}},
            arrowStrikethrough: true
          };

          if (graph[i].options.id === 0) {
            edge.color = {color: '#006600', highlight: '#006600', hover: '#006600'};
          }
          for (var t = 0; t < edges.length; t++) {
            if (edges[t].from == edge.to && edges[t].to == edge.from) {
              edge.font.align = 'horizontal';
            }
          }
          edges.push(edge);
        }
      }
      return edges;
    }
  };