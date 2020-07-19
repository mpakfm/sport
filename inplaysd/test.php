<?php
/**
 * Created by PhpStorm.
 * User: mpak
 * Date: 19.07.2020
 * Time: 15:11
 */

use Mpakfm\Printu;

include_once __DIR__ . '/../vendor/autoload.php';

Printu::obj(__DIR__)->title('__DIR__')->response('text');

$emailList = [
    'info@mpakfm.com',
    'nvhgv56gfc@mail.ru',
    'fsg79@yandex.ru',
    'mpakfm@gmail.com',
];

$msg = <<<THIS
    <b>Имя:</b> test <br>
    <b>Телефон:</b> test <br>
    <b>Email:</b> test
THIS;
$dt = new \DateTimeImmutable();
$headers  = "Content-type: text/html; charset=utf-8 \r\n";
$headers .= "From: Parser INPLAY <noreply@mpakfmtest.ru>\r\n";
$to = implode(', ', $emailList);
$subject = 'Тест кодировок '.$dt->format('H:i:s').' на mpakfmtest.ru';
Printu::obj($subject)->title('$subject')->response('text');
$subjectBase64 = "=?utf-8?B?" . base64_encode($subject) . "?=";

$res = mail($to, $subjectBase64, $msg, $headers);
Printu::obj($res)->title('send_mail $subjectBase64: "'.$subjectBase64.'", to: ' . $to)->response('text');

$subject . ' original';

$res2 = mail($to, $subject, $msg, $headers);
Printu::obj($res2)->title('send_mail $subject: "'.$subject.'", to: ' . $to)->response('text');
