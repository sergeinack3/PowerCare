<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    assignedEntity.sch :
    Contenu :
        Contrôle d'un élément assignedEntity  
    Paramètres d'appel :
        Néant
    Historique :
        08/05/11 : FMY ASIP/PRAS : Création
        28/07/11 : FMY ASIP/PRAS : nullFlavor autorisé pour assignedPerson et name
        10/08/12 : FMY : les elts addr et telecom deviennent optionnels. Donc présence plus exigée
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="assignedEntity">
    <rule context="cda:assignedEntity">
        <assert test="./cda:id"> Erreur de conformité CI-SIS : L'élément "id" doit être présent sous
            assignedEntity. </assert>
        <assert test="cda:assignedPerson"> Erreur de conformité CI-SIS : L'élément
            "assignedPerson" doit être présent sous assignedEntity (nullFlavor autorisé). </assert>
        <assert test="cda:assignedPerson/cda:name or cda:assignedPerson/@nullFlavor"> 
            Erreur de conformité CI-SIS : 
            Si l'élément assignedPerson n'est pas vide avec un nullFlavor, alors il 
            doit comporter un élément fils "name" (nullFlavor autorisé). </assert>
    </rule>
</pattern>
