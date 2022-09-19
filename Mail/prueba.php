<?php
//$stream = imap_open("{outlook.office365.com:993/imap/ssl}", 'jce@wowperu.pe', 'Danna1811*');


$mbox=imap_open("{outlook.office365.com:993/imap/ssl/authuser=jce@wowperu.pe}","jce@wowperu.pe","Danna1811*", 0, 10, array('DISABLE_AUTHENTICATOR' => 'GSSAPI')) or die("Can't connect: " . imap_last_error());


//{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX

$mails = imap_search($mbox, 'UNSEEN');

rsort($mails);
foreach ($mails as $mailId) {
  imap_fetch_overview($stream, $mailId, 0);
} 
