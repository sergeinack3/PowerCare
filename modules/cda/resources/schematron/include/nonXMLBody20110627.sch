<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    nonXMLBody.sch :
    Contenu :
        Règles de contrôle d'un document CDA contenant un corps non structuré encapsulé en base64 
    Paramètres d'appel :
        Néant
    Historique :
    31/05/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="nonXMLBody">
    <p>Conformité d'un document CDA avec nonXMLBody au profil IHE XDS-SD et vérification des formats et encodage autorisés</p>
    <rule context="cda:nonXMLBody">
        <assert test="../../cda:templateId[@root=$XDS-SD]">
            Erreur de conformité XDS-SD : Un document avec un 
            corps non xml doit comporter l'élément ClinicalDocument/templateId avec @root = 
            "<value-of select="$XDS-SD"/>".
        </assert>
        <assert test="./cda:text[@representation=&quot;B64&quot;]">
            Erreur de conformité CDAr2 : Un document avec un corps non xml
            doit encapsuler en format base64 son contenu dans l'élément text, avec @representation = "B64"
        </assert>
        <assert test="./cda:text[@mediaType='application/pdf' or 
            @mediaType='image/jpeg' or 
            @mediaType='image/tiff' or 
            @mediaType='text/plain' or 
            @mediaType='text/rtf']">
            Erreur de conformité CI-SIS : Un document avec un corps non xml
            doit encapsuler en format base64 son contenu dans l'élément text, avec @mediaType devant prendre 
            l'une de ces valeurs : {"text/plain", "application/pdf", "image/jpeg", "image/tiff", "text/rtf"}.
        </assert>
    </rule>
</pattern>