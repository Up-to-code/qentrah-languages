<?php
// Run after WordPress loads the active plugin: wp eval-file tests/language-tags.php
$valid = array('en','AR','pt-BR','zh-Hant-TW','es-419','sr-Latn','fil','haw','yue','de-CH-1901','en-u-ca-gregory','x-client','i-klingon','sgn-BE-FR','ar-a-extend1-x-private','abcd','abcde');
$invalid = array('','a','en_','en--US','en<script>','en-us-','x','en-u','en-u-ca-u-nu','en us',str_repeat('a',256));
foreach ($valid as $tag) if (!ql_valid_language_tag($tag)) throw new Exception('Rejected valid tag: '.$tag);
foreach ($invalid as $tag) if (ql_valid_language_tag($tag)) throw new Exception('Accepted invalid tag: '.$tag);
echo "BCP 47 syntax: ".count($valid)." valid and ".count($invalid)." invalid cases passed.\n";
