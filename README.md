 
 Jaxp needs to be constructed with a (full) path to a XML file and an instanced parent object (entity).
 The object and possible child objects will be hydrated with the XML information in DOMDocument and returned
 by returnHydratedObjects().
 
 The referenced and/or passed entities/objects must have a constant property called XML_MAPPING which holds an associative array containing the
 following mapping configuration.

    [REQUIRED] XmlParser::KEY_MAP Must hold an associative array containing the expected node name as key and the
     object's function as value. It may contain a associative array containing the XML path the program must follow.
        
    [OPTIONAL] XmlParser::KEY_PARENT_NODE Must hold the name of the node from which the program must start.

    [OPTIONAL] XmlParser::KEY_CHILDREN Must contain an (or multiple) associative array(s) with the expected node name
     as key and an associative array as value. The array must contain a key named XmlParser::KEY_CLASS holding the
     (namespaced) class which needs to be created as value and a key named XmlParser::KEY_SETTER holding the function
     that must be called when setting the parent of the (child)-object. The array may contain an optional key named
     XmlParser::KEY_PARENT_NODE which must hold a different parent node.
     (This may be needed for complex and/or deepdocuments.)
 
When attributes must be parsed the program will look for the 'nodeNameAttributeName' syntax in the map,
this because it is common practice to use ambiguous attribute names in XML.

e.g., Take the following XML node

    <identification codingScheme="codingSchemeValue">identificationValue</identification>

The program takes the node name (identification) and the attribute name (codingScheme).
The first letter of the attribute name will be changed to uppercase which results in "identificationCodingScheme".
The program then tests if "identificationCodingScheme" exists in the XmlParser::KEY_MAP and if so, calls the
defined function and passes the attribute value.


#### Example using all options:

    const XML_MAPPING = [
      XmlParser::KEY_PARENT_NODE => 'document_header_name',
       XmlParser::KEY_CHILDREN => [
           'childNode' => [
               XmlParser::KEY_CLASS => 'name\space\classForChildNode',
               XmlParser::KEY_SETTER => 'setParentFunction'
               XmlParser::KEY_PARENT_NODE => 'aDifferentParentNode' // OPTIONAL
           ]
       ],
       XmlParser::KEY_MAP => [
           'nodeName' => 'nodeNameFunction',
           'nodeNameAttribute' => 'nodeNameAttributeFunction'
           'childNode' => [
               'anotherChildNode' => [
                   'someNodeName' => 'someNodeFunction'
               ]
            ]
         ]
      ]
 