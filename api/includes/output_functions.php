<?php

/*
 * Output functions
 * - Functions used for output
 */
function xmlizePart($doc, $key, $data)
{
    $element = $doc->createElement($key);

    if (is_array($data)) {
        foreach ($data as $skey => $svalue) {
            $element->appendChild(xmlizePart($doc, $skey, $svalue));
        }
    } else {
        $element->appendChild($doc->createTextNode($data));
    }

    return $element;
}

function output_xml($data)
{
    $doc = new DOMDocument('1.0');
    $root = $doc->createElement('CBNGData');
    $doc->appendChild($root);

    foreach ($data as $key => $value) {
        $root->appendChild(xmlizePart($doc, $key, $value));
    }
    return $doc->saveXML();
}

function output_encoding($data)
{
    global $output_format;
    switch ($output_format) {
        case "xml":
            return output_xml($data);
            break;

        case "json":
            return json_encode($data);
            break;

        case "php":
            return serialize($data);
            break;

        case "debug":
            return print_r($data, true);
            break;

        default:
            $data = array(
                "error" => "invalid_format",
                "error_message" => "You have requested and invalid format.",
            );
            return output_xml($data);
            break;
    }
}
