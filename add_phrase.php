<?php


include_once "/var/www/private/utils/config.php";
include_once SCRIPTS_PATH . "utils/config.php";
include_once SCRIPTS_PATH . "phrases/forbidden_words.php";


function addPhrase($phrase, $author)
{
  $conn = new mysqli(PHRASES_DB_SERVER, PHRASES_DB_USERNAME, PHRASES_DB_PASSWORD, PHRASES_DB_NAME);


if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (containsForbiddenWords($phrase, $conn)) {
  echo "Phrase contains forbidden words";
  exit();
}

//Make an sql statement that will insert the phrase into the database assuming the table is called valid_phrases and the column is called phrase and another column called author stored the author of the phrase
$sql = "INSERT INTO `valid_phrases` (`phrase`, `author`) VALUES ('$phrase', 'Unknown')";

if ($conn->query($sql) === TRUE) {
  echo "New record created successfully: " . $phrase;
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

}



?>