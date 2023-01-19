<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    authorSpecialty.sch :
    Contenu :
        Contrôle du code de spécialité d'un auteur dans l'en-tête CDA  (nullFlavor autorisé)
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        31/05/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="authorSpecialty" is-a="dansJeuDeValeurs">
    <p>Conformité de la spécialité de l'auteur au CI-SIS</p>
    <param name="path_jdv" value="$jdv_authorSpecialty"/>
    <param name="vue_elt" value="'ClinicalDocument/author/assignedAuthor/code'"/>
    <param name="xpath_elt" value="cda:ClinicalDocument/cda:author/cda:assignedAuthor/cda:code"/>
    <param name="nullFlavor" value="1"/>
</pattern>   
