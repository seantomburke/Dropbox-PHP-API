<?php


/**
 * Example of getting a file
 * 
 * description
 *
 * @author Sean Thomas Burke <http://www.seantburke.com>
 * @tag hawaiianchimp
 */

require_once 'Dropbox.class.php'; //require the dropbox class

$dropbox = new Dropbox(); //create new dropbox object
$root = 'sandbox'; //set to the root either: 'sandbox' or 'dropbox'
$path = $_GET['path']; //path from the form

//using the GET method
echo 'Contents of: https://api-content.dropbox.com/1/files/'.$root.'/'.$path.'<br>';
echo $dropbox->get('https://api-content.dropbox.com/1/files/sandbox/test.txt'); // cUrl GET call from dropbox object


//this code uses the getFile method
echo '<br><br>'; //line space
echo 'Contents of: '.$path.'<br>';
echo $dropbox->getFile($root,'test.txt');// getFile method from Dropbox object

?>