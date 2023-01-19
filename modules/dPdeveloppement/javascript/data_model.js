/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

dataModel = window.dataModel || {
    /**
     * Fonction pour afficher la modale de détail d'une classe
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
     * Fonction qui prépare les données nécessaires à la création du graph. Appelle dataModel.drawGraph et lui passe
     * les données nécessaires
     *
     * @param graph      Un objet contenant toutes les informations pour créer le graph
     * @param backs      Un object contenant toutes les backprops à afficher dans le graph
     * @param backs_name Noms des backsrefs
     * @param inv_class  Noms des attributs des classes vers lequelles ont pointe
     * @param opt        Options du graphe (object sous forme JSON)
     */
    prepareGraph: function (graph, backs, backs_name, inv_class, opt) {
      // Création des noeuds du graph
      var nodes = dataModel.makeNodes(graph, opt.hierarchy_sort);
      var id = nodes.length;

      // création des backprops
      var backNodesEdges = dataModel.makeBacks(graph, backs, backs_name, id, opt.hierarchy_sort);
      // Création des noeuds des backsprops
      for (var idx in backNodesEdges.nodes) {
        if (typeof(backNodesEdges.nodes[idx]) == "object") {
          nodes.push(backNodesEdges.nodes[idx]);
        }
      }
      // Création des arrêtes des backprops
      var edges = [];
      for (idx in backNodesEdges.edges) {
        if (typeof(backNodesEdges.edges[idx]) == "object") {
          edges.push(backNodesEdges.edges[idx]);
        }
      }
      // Création des arrêtes du graph
      var dataEdges = dataModel.makeEdges(graph, inv_class);
      for (idx in dataEdges) {
        // Vérification du type pour ne pas itérer sur les propriétés _proto_ qui sont des fonctions
        if (typeof(dataEdges[idx]) == "object") {
          edges.push(dataEdges[idx]);
        }
      }
      dataModel.drawGraph(nodes, edges, opt.hierarchy_sort, opt.show_hover);
    },
    /**
     * Fonction qui crée les options du graph puis crée le graph. Appelle dataModel.setEventsGraph
     *
     * @param nodes           Conteneur de type vis.DataSet qui contient les noeuds du graph
     * @param edges           Conteneur de type vis.DataSet qui contient les arrêtes du graph
     * @param hierarchy_sort  Algorithme de placement des noeuds
     * @param show_hover      Désactive les évènements au survol du graph si nécessaire
     */
    drawGraph: function (nodes, edges, hierarchy_sort, show_hover) {
      App.loadJS(['lib/visjs/vis'], function (vis) {
        // Création des DataSet pour créer le graph
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
          // Permet les évènements de type "on mouse hover"
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
        // Création du graph
        var network = new vis.Network(container, data, options);
        // Appel de la fonction pour créer les évènements
        dataModel.setEventsGraph(network, edge_set, node_set);
      });
    },
    /**
     * Fonction qui configure les différents évènements qui peuvent avoir lieu sur le graph
     *
     * @param network  Le graph créé auquel on souhaite ajouter des évènements
     * @param edge_set Conteneur de type vis.DataSet qui contient les arrêtes du graph
     * @param node_set Conteneur de type vis.DataSet qui contient les noeuds du graph
     */
    setEventsGraph: function (network, edge_set, node_set) {
      // Gestion des évènements
      network.on('click', onClick);
      network.on('doubleClick', onDoubleClick);
      // Action lorsque la souris passe sur un noeud
      network.on('hoverNode', function (params) {
        onHoverNode(params.node);
      });
      // Action lorsque la souris passe sur une arrête
      network.on('hoverEdge', function (params) {
        onHoverEdge(params.edge);
      });
      // Action lorsque la souris part d'un noeud
      network.on('blurNode', clearEdges);
      // Action lorsque la souris part d'une arrête
      network.on('blurEdge', clearEdges);
      // Désactive la physique après que les noeuds aient été placés
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

      // Supprime les labels des arrêtes
      function clearEdges() {
        edge_set.forEach(function (edge) {
          edge_set.update({
            id: edge.id,
            label: ''
          });
        })
      }

      // Affiche les labels des arrêtes liées au noeud
      function onHoverNode(nodeId) {
        var connectedEdges = network.getConnectedEdges(nodeId);
        for (var edge in connectedEdges) {
          // Vérification du type pour ne pas itérer sur les propriétés _proto_ qui sont des fonctions
          if (typeof connectedEdges[edge] == "string") {
            onHoverEdge(connectedEdges[edge]);
          }
        }
      }

      // Affiche le label de l'arrête
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
      // Permet de vérifier si on exécute un seul clique ou un double clique
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

      // Si un seul click on affiche les détails de la classe
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
     * Fonction qui renvoie une chaine de caractères correspondant au SVG qui sera afficher pour un noeud
     *
     * @param classe Nom de la classe pour laquelle on crée le SVG
     * @param props  Propriétés de la classe pour laquelle on crée le SVG
     * @param id     Permet de savoir s'il s'agit de la classe sélectionnée ou non
     * @param back   Permet de savoir s'il s'agit d'une backprop ou non
     *
     * @returns {string}
     */
    makeSVG: function (classe, props, id, back) {
      var height = 25;
      if (props) {
        height += props.length * 12;
      }
      // Début du SVG
      var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200px" height="' + height + 'px">';
      // Le noeud d'id 0 est la classe principale
      if (id === 0) {
        svg += '<rect x="0" y="0" width="100%" height="100%" fill="#eeeeee" stroke-width="1" stroke="#000000" ></rect>';
      }
      else {
        // Noeud étant une backprop
        if (back) {
          svg += '<rect x="0" y="0" width="100%" height="100%" fill="#bbbbbb" stroke-width="1" stroke="#000000" ></rect>';
        }
        // Noeud normal
        else {
          svg += '<rect x="0" y="0" width="100%" height="100%" fill="#ffffff" stroke-width="1" stroke="#000000" ></rect>';
        }
      }
      // Création du conteneur du texte
      svg += '<foreignObject x="5" y="5" width="100%" height="100%">' +
        '<div xmlns="http://www.w3.org/1999/xhtml" style="font-size:10px; font-family: Calibri, sans-serif; white-space: nowrap; width: 190px; overflow: hidden; text-overflow: ellipsis;">' +
        '<strong>' + classe + '</strong><br/>';

      // Création des propriétés de la classe
      var i = 0;
      if (props) {
        for (var text in props) {
          // Vérification du type pour ne pas itérer sur les propriétés _proto_ qui sont des fonctions
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
     * Crée les noeuds du graph sous forme d'images SVG et les renvoie sous forme de tableau
     *
     * @param graph     Objet contenant les informations des noeuds et liens du graphe
     * @param hierarchy Algorithme de placement des noeuds
     *
     * @returns {Array} Tableau de noeuds
     */
    makeNodes: function (graph, hierarchy) {
      var id = 0;
      var nodes = [];
      // Création des noeuds sous forme d'une image SVG contenant les informations à afficher
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
     * Crée les noeuds et arrêtes des backprops et les renvoie sous forme de tableau
     *
     * @param graph       Objet contenant les informations des noeuds et liens du graphe
     * @param backs       Objet contenant les informations des backprops à afficher
     * @param backs_name  Noms des backprops
     * @param id          Identifiant à partir duquel commencer pour les noeuds
     * @param hierarchy   Algorithme de placement des noeuds utilisé
     *
     * @returns {Object}  Object contenant les noeuds et leurs arrêtes
     */
    makeBacks: function (graph, backs, backs_name, id, hierarchy) {
      var nodes = [];
      var edges = [];
      var class_per_level = 0;
      var level = 0;
      for (var back in backs) {
        // Vérification du type pour ne pas itérer sur les propriétés _proto_ qui sont des fonctions
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
     * Crée les arrêtes du graph et les renvoie sous forme de tableau
     *
     * @param graph     Objet contenant les informations nécessaires à la création des noeuds et des arrêtes
     * @param inv_class Objet contenant les backprops des classes liées à la classe principale
     *
     * @returns {Array} Tableau contenant toutes les arrêtes créées
     */
    makeEdges: function (graph, inv_class) {
      var edges = [];
      for (var i = 0; i < graph.length; i++) {
        // Héritage : création du lien vers le parent
        if (graph[i].options.parent) {
          edges.push({
            from: 0,
            to: graph[i].options.id,
            length: 400,
            labelC: graph[0].class + " hérite de " + graph[i].class,
            color: {color: '#cc0000', highlight: '#cc0000', hover: '#cc0000'},
            arrows: {to: {enabled: true, scaleFactor: 1, type: 'umlinheritance'}}
          })
        }
        // Création des autres liens
        for (var lien in graph[i].links) {
          // Vérification du type pour ne pas itérer sur les propriétés _proto_ qui sont des fonctions
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