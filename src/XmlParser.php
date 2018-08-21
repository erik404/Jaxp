<?php

/**
 * Erik-Jan van de Wal <ejvandewal@gmail.com> - Energy Global B.V.
 */

namespace Energyglobal\Libraries;

/**
 * Class XmlParser
 *
 * @package Energyglobal\Libraries
 *
 * Expects an instanced DOMDocument loaded with the XML file and an instanced object (entity).
 * The object and possible child objects will be hydrated with the XML information in DOMDocument and may be returned
 * by returnHydratedObjects().
 *
 * The object must have a constant property called XML_MAPPING which holds an associative array containing the
 * mapping configuration.
 *
 *      [REQUIRED] XmlParser::KEY_MAP Must hold an associative array containing the expected node name as key and the
 *      object's function as value. It may contain a associative array containing the XML path the program must follow.
 *
 *      [OPTIONAL] XmlParser::KEY_PARENT_NODE Must hold the name of the node from which the program must start.
 *
 *      [OPTIONAL] XmlParser::KEY_CHILDREN Must contain a (or multiple) associative array(s) with the expected node name
 *      as key and an associative array as value. The array must contain a key named XmlParser::KEY_CLASS holding the
 *      (namespaced) class which needs to be created as value and a key named XmlParser::KEY_SETTER holding the function
 *      that must be called when setting the parent of the (child)-object. The array may contain an optional key named
 *      XmlParser::KEY_PARENT_NODE which must hold a different parent node. (This may be needed for complex and/or deep
 *      documents.)
 *
 * When attributes must be parsed the program will look for the 'nodeNameAttributeName' syntax in the map,
 * this because it is common practice to use ambiguous attribute names in XML.
 *
 * e.g., Take the following XML node
 *
 *     <identification codingScheme="codingSchemeValue">identificationValue</identification>
 *
 * The program takes the node name (identification) and the attribute name (codingScheme).
 * The first letter of the attribute name will be changed to uppercase which results in "identificationCodingScheme".
 * The program then tests if "identificationCodingScheme" exists in the XmlParser::KEY_MAP and if so, calls the
 * defined function and passes the attribute value.
 *
 *
 * # Example using all options:
 *
 *  const XML_MAPPING = [
 *      XmlParser::KEY_PARENT_NODE => 'document_header_name',
 *      XmlParser::KEY_CHILDREN => [
 *          'childNode' => [
 *              XmlParser::KEY_CLASS => 'name\space\classForChildNode',
 *              XmlParser::KEY_SETTER => 'setParentFunction'
 *              XmlParser::KEY_PARENT_NODE => 'aDifferentParentNode' // OPTIONAL
 *          ]
 *      ],
 *      XmlParser::KEY_MAP => [
 *          'nodeName' => 'nodeNameFunction',
 *          'nodeNameAttribute' => 'nodeNameAttributeFunction'
 *          'childNode' => [
 *              'anotherChildNode' => [
 *                  'someNodeName' => 'someNodeFunction'
 *              ]
 *          ]
 *      ]
 *  ]
 */

class XmlParser
{
    /** constant definitions which are used in the mapping array */
    const KEY_CHILDREN = 'children';
    const KEY_PARENT_NODE = 'parent_node';
    const KEY_MAP = 'map';
    const KEY_CLASS = 'class';
    const KEY_SETTER = 'setter';

    /** @var \DOMDocument $domDoc */
    private $domDoc;
    /** @var string $headerName */
    private $headerName;
    /** @var array $mapping */
    private $mapping;
    /** @var object $object */
    private $object;
    /** @var array $result */
    private $result = [];

    /**
     * XmlParser constructor.
     * No type hinting for $object, if the type is asserted other possible valid types will throw an exception.
     *
     * XmlParser constructor.
     * @param \DOMDocument $domDoc
     * @param $object
     */
    public function __construct(\DOMDocument $domDoc, $object)
    {
        $this->mapping = $object::XML_MAPPING; // sets value to the XML_MAPPING constant of the passed object
        $this->object = $object;
        $this->domDoc = $domDoc;
    }

    /**
     * Call the main functions and returns the hydrated parent object.
     *
     * @return object
     */
    public function hydrateParent()
    {
        // parse the parent node.
        $this->parseParentNode();

        // returns the hydrated object.
        return $this->object;
    }

    /**
     * Parses the parent node, tests for attributes and start the iteration of the document.
     */
    private function parseParentNode()
    {
        // get and set the document name from the document root element.
        $this->headerName = $this->domDoc->documentElement->localName;

        // check if the optional KEY_PARENT_NODE is set, else get it from the DOMDocument
        if (!isset($this->mapping[self::KEY_PARENT_NODE])) {
            $this->mapping[self::KEY_PARENT_NODE] = $this->headerName;
        }

        if ($this->domDoc->documentElement->hasAttributes()) {
            // document has attributes in the header.
            $this->parseAttributes($this->mapping[self::KEY_MAP], $this->domDoc->documentElement);
        }

        // iterates through the DOMDocument.
        $this->iterateNodes($this->domDoc->childNodes, $this->mapping[self::KEY_MAP]);

        // add the hydrated parent object to the result array.
        $this->result[] = $this->object;

        return; // void
    }

    /**
     * When a node attribute is found the attribute name will be concatenated with the node name ($key).
     * The first letter of the attribute name is capitalized.
     *
     * If $key exists in the map the defined function will be called.
     *
     * @param array $map
     * @param \DOMNode $node
     */
    private function parseAttributes(array $map, \DOMNode $node)
    {
        foreach ($node->attributes as $attribute) {
            // The 'v' attribute is often used to encode the main value of a node, if that node only contains a single value.
            $attributeName = ($attribute->localName == "v") ? "" : ucwords($attribute->localName);
            //The nodeName may include a namespace which has to be filtered out.
            $key = $node->localName . $attributeName;
            if (key_exists($key, $map) && !is_array($map[$key])) {
                // the attribute is defined in the map.
                $this->callFunction($map[$key], $attribute);
            }
        }

        return; // void
    }

    /**
     * Calls the object's function, passing the value of the node.
     *
     * @param string $function
     * @param \DOMNode $node
     */
    private function callFunction(string $function, \DOMNode $node)
    {
        //Assumption: Only leaf nodes have values that need to be set. This avoids naming conflicts with nodes higher
        // up the tree.
        // Check that the node has only one child and that the child is not another internal node.
        if ($node->childNodes->length == 1 && $node->firstChild->nodeType != XML_ELEMENT_NODE) {
            $this->object->{$function}($node->nodeValue);
        } else {
            // a tag with the same name, but a different place in the tree triggered callFunction.
        }

        return; // void
    }

    /**
     * Iterates recursively through all the XML nodes and parses each XML node.
     *
     * @param \DOMNodeList $nodes
     * @param array $localMapping
     */
    private function iterateNodes(\DOMNodeList $nodes, array $localMapping)
    {
        foreach ($nodes as $node) {
            // always parse node, testing is done in this function.
            //if node->localName  not in
            $isKeyChild = $this->parseNode($node, $localMapping);

            /** @var \DOMNode $node */
            if (!$isKeyChild && $node->hasChildNodes()) {
                $newMapping = key_exists($node->localName, $localMapping) && is_array($localMapping[$node->localName])
                    ? $localMapping[$node->localName] : $localMapping;
                // the node contains childNodes, call the function again.
                $this->iterateNodes($node->childNodes, $newMapping);
            }
        }
        return; // void
    }

    /**
     * Parses the node and determines the appropriate action.
     *
     * @param \DOMNode $node
     * @param array $localMapping
     * @return bool
     */
    private function parseNode(\DOMNode $node, array $localMapping): bool
    {
        // test if the node is truly related to the defined parent, this because elements are not guaranteed to have a unique name.
        if (!$node->parentNode->localName === $this->mapping[self::KEY_PARENT_NODE]) {
            // node not related to parent, no need to continue.
            return false; // void
        }
        // test if the node is specified as a child-object.

        if (isset($this->mapping[self::KEY_CHILDREN]) && array_key_exists($node->localName, $this->mapping[self::KEY_CHILDREN])) {
            // check if there is an optional parent node specified, if not take the generic one.
            $parentNode = $this->mapping[self::KEY_CHILDREN][$node->localName][self::KEY_PARENT_NODE] ?? $this->mapping[self::KEY_PARENT_NODE];
            // test if the node is related to the parent
            if ($node->parentNode->localName === $parentNode) {
                // create a new child as specified in the config
                $this->createChild($this->mapping[self::KEY_CHILDREN][$node->localName], $node);

                return true; // void
            }
        }

        // test if the node exists in the map.
        if (!array_key_exists($node->localName, $this->mapping[self::KEY_MAP])) {
            // the nodeName does not exist, no need to continue.
            return false; // void
        }

        // if the node has attributes call the parseAttributes function
        //TODO add handling multiple matches
        if ($node->hasAttributes()) $this->parseAttributes($this->mapping[self::KEY_MAP], $node);


        // test if the value contains an array, this specifies a childnode set.
        if (is_array($this->mapping[self::KEY_MAP][$node->localName])) {
            // the value contains a child node, call the traverseArray function.
            $this->traverseArray($this->mapping[self::KEY_MAP][$node->localName], $node);

            //TODO solve bug with same name on multiple levels, perhaps return true?
            return false; // void
        }

        // pass the function name and value to the callFunction function.
        if (count($localMapping) > 0) {
            $this->callFunction($localMapping[$node->localName], $node);
        }

        return false; // void
    }

    /**
     * Creates a new instance of the class defined in the $objInfo array, runs the XmlParser for that object,
     * runs the parentSetter for that specific object and adds all hydrated object to the $result array.
     *
     * @param array $objInfo
     * @param \DOMNode $childNode
     */
    private function createChild(array $objInfo, \DOMNode $childNode)
    {
        // instantiate the childObject from the class definition.
        $childObject = new $objInfo[self::KEY_CLASS]();

        // create a new DOMDocument with the childnode
        $childDomDoc = new \DOMDocument();
        $childDomDoc->appendChild(
            $childDomDoc->importNode($childNode, true)
        );

        // instantiate a new XmlParser and pass childNode and the childObject.
        $xmlParser = new XmlParser($childDomDoc, $childObject);
        $hydratedChildObject = $xmlParser->hydrateParent(); // hydrate the object

        // call the setter function and pass the parent object.
        $hydratedChildObject->{$objInfo[self::KEY_SETTER]}($this->object);

        // merge the global result with the hydrated objects from the childObject
        $this->result = array_merge(
            $this->result,
            $xmlParser->returnHydratedObjects()
        );

        return; // void
    }

    /**
     * Returns the array holding all the hydrated objects.
     *
     * @return array
     */
    public
    function returnHydratedObjects(): array
    {
        return $this->result;
    }

    /**
     * Loops through the childnode array and determines if it needs to call itself or send the information to callFunction
     *
     * @param array $map
     * @param \DOMNode $node
     */
    private
    function traverseArray(array $map, \DOMNode $node)
    {
        foreach ($map as $key => $value) {
            // traverse through array and loop through XML document for each iteration to match nodeName and array key.
            foreach ($node->childNodes as $childNode) {
                /** @var \DOMNode $childNode */
                if ($childNode->hasAttributes()) {
                    $this->parseAttributes($map, $childNode);
                }

                if ($key === $childNode->localName) {
                    if (is_array($value)) {
                        // the value contains a childNode, call the function again.
                        $this->traverseArray($value, $childNode);
                        continue;
                    }
                    // the value contains a function which must be called.
                    $this->callFunction($value, $childNode);
                }
            }
        }

        return; // void
    }
}

