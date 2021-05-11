<?php

/*
 * Live edit
 * - Runs a wikipedia diff ID though the core and returns the result
 */

if (isset($_REQUEST['article']) && !empty($_REQUEST['article'])) {
    $article = $_REQUEST['article'];
} else {
    $data = array(
        "error" => "argument_error",
        "error_message" => "You must specify an article for this method.",
    );
    die(output_encoding($data));
}

if (isset($_REQUEST['diff']) && !empty($_REQUEST['diff'])) {
    $diff = $_REQUEST['diff'];
} else {
    $data = array(
        "error" => "argument_error",
        "error_message" => "You must specify a diff id for this method.",
    );
    die(output_encoding($data));
}

$api = unserialize(file_get_contents('https://en.wikipedia.org/w/api.php?action=query&prop=revisions&titles=' . $article . '&rvstartid=' . $diff . '&rvlimit=2&rvprop=flags|comment|timestamp|user|content|ids&format=php'));

$api = array_shift($api['query']['pages']);
$user = $api['revisions'][0]['user'];
$ns = $api['ns'];
$title = $api['title'];
$timestamp = $api['revisions'][0]['timestamp'];
;

$cb = unserialize(file_get_contents('https://tools.wmflabs.org/cluebot/cb.php?user=' . urlencode($user) . '&ns=' . $ns . '&title=' . urlencode($title) . '&timestamp=' . urlencode($timestamp)));

if (!isset($cb) || empty($cb) || !isset($api) || empty($api)) {
    $data = array(
        "error" => "internal_error",
        "error_message" => "Error occurred while talking to teh API.",
    );
    die(output_encoding($data));
}

$doc = new DOMDocument('1.0');
$root = $doc->createElement('WPEditSet');
$doc->appendChild($root);

$roote = $doc->createElement('WPEdit');
$root->appendChild($roote);

$element = $doc->createElement('EditType');
$element->appendChild(new DOMText('change'));
$roote->appendChild($element);

$element = $doc->createElement('EditID');
$element->appendChild(new DOMText($api['revisions'][0]['revid']));
$roote->appendChild($element);

$element = $doc->createElement('comment');
$element->appendChild(new DOMText($api['revisions'][0]['comment']));
$roote->appendChild($element);

$element = $doc->createElement('user');
$element->appendChild(new DOMText($api['revisions'][0]['user']));
$roote->appendChild($element);

$element = $doc->createElement('user_edit_count');
$element->appendChild(new DOMText($cb['user_edit_count']));
$roote->appendChild($element);

$element = $doc->createElement('user_distinct_pages');
$element->appendChild(new DOMText($cb['user_distinct_pages']));
$roote->appendChild($element);

$element = $doc->createElement('user_warns');
$element->appendChild(new DOMText($db['user_warns']));
$roote->appendChild($element);

$element = $doc->createElement('prev_user');
$element->appendChild(new DOMText($api['revisions'][1]['user']));
$roote->appendChild($element);

$element = $doc->createElement('user_reg_time');
$element->appendChild(new DOMText($cb['user_reg_time']));
$roote->appendChild($element);

$celement = $doc->createElement('common');
$roote->appendChild($celement);

$selement = $doc->createElement('page_made_time');
$selement->appendChild(new DOMText($cb['common']['page_made_time']));
$celement->appendChild($selement);

$selement = $doc->createElement('title');
$selement->appendChild(new DOMText($api['title']));
$celement->appendChild($selement);

$selement = $doc->createElement('namespace');
$selement->appendChild(new DOMText($api['namespace']));
$celement->appendChild($selement);

$selement = $doc->createElement('creator');
$selement->appendChild(new DOMText($cb['common']['creator']));
$celement->appendChild($selement);

$selement = $doc->createElement('num_recent_edits');
$selement->appendChild(new DOMText($cb['common']['num_recent_edits']));
$celement->appendChild($selement);

$selement = $doc->createElement('num_recent_reversions');
$selement->appendChild(new DOMText($cb['common']['num_recent_edits']));
$celement->appendChild($selement);

$cuelement = $doc->createElement('current');
$roote->appendChild($cuelement);

$selement = $doc->createElement('minor');
if ($api['revisions'][0]['minor'] === true) {
    $element->appendChild(new DOMText('True'));
} else {
    $element->appendChild(new DOMText('False'));
}
$cuelement->appendChild($selement);

$selement = $doc->createElement('timestamp');
$selement->appendChild(new DOMText($api['revisions'][0]['timestamp']));
$cuelement->appendChild($selement);

$selement = $doc->createElement('text');
$selement->appendChild(new DOMText($api['revisions'][0]['*']));
$cuelement->appendChild($selement);

$prelement = $doc->createElement('previous');
$roote->appendChild($prelement);

$selement = $doc->createElement('timestamp');
$selement->appendChild(new DOMText($api['revisions'][1]['timestamp']));
$prelement->appendChild($selement);

$selement = $doc->createElement('text');
$selement->appendChild(new DOMText($api['revisions'][1]['*']));
$prelement->appendChild($selement);

$xml = str_replace('</WPEditSet>', '', $doc->saveXML());

$fp = fsockopen($coreip, $coreport, $errno, $errstr, 15);
if (!$fp) {
    $data = array(
        "error" => "internal_error",
        "error_message" => "Error occurred opening a socket to the backend.",
    );
    die(output_encoding($data));
}

fwrite($fp, $xml);
fflush($fp);

$returnXML = '';
$endeditset = false;
while (!feof($fp)) {
    $returnXML .= fgets($fp, 4096);
    if (strpos($returnXML, '</WPEdit>') === false and !$endeditset) {
        fwrite($fp, '</WPEditSet>');
        fflush($fp);
        $endeditset = true;
    }
}
fclose($fp);

$data = simplexml_load_string($returnXML);
$score = (string)$data->WPEdit->score;
$isVandalism = (string)$data->WPEdit->think_vandalism;

$data = array(
    "score" => $score,
    "think_vandalism" => $isVandalism,
);
die(output_encoding($data));
