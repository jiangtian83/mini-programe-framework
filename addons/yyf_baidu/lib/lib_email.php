<?php
 function sendMail($to, $title, $content, $email_title, $smtp_email, $smtp_key) { goto Tgj4H; yOJsg: $mail->isHTML(true); goto gYvtf; oG0YP: YPlIJ: goto NZk70; ljpzm: $mail->Host = "\x73\155\164\x70\56\161\161\56\143\157\155"; goto lCxg7; NZk70: $arr = explode("\54", $to); goto R10C6; T2Lmb: $mail->CharSet = "\x55\x54\106\x2d\x38"; goto TxGYM; JSeoE: $mail->Username = $smtp_email; goto l5nc1; Tgj4H: require_once MODULE_ROOT . "\x2f\155\141\151\154\x2f\143\154\141\163\163\56\160\150\x70\x6d\141\x69\154\145\162\56\x70\150\x70"; goto D2871; gYvtf: if (strpos($to, "\x2c")) { goto YPlIJ; } goto XFLd3; qaf79: t7BSf: goto tVlOq; TxGYM: $mail->FromName = $email_title; goto JSeoE; eqfZU: $mail->From = $smtp_email; goto yOJsg; XFLd3: $mail->addAddress($to, "\xe9\205\x92\345\xba\x97\351\200\x9a\347\237\245"); goto enoV2; KZC9E: $mail->Port = 465; goto uoQtr; J2HJ2: $mail->isSMTP(); goto ciWHy; lCxg7: $mail->SMTPSecure = "\x73\x73\154"; goto KZC9E; D2871: require_once MODULE_ROOT . "\57\155\x61\151\x6c\57\x63\154\141\163\x73\x2e\x73\155\164\x70\56\x70\150\x70"; goto utQz0; Z_kyd: $mail->SMTPDebug = 0; goto J2HJ2; uoQtr: $mail->Hostname = "\150\164\x74\x70\x3a\57\x2f\167\x77\167\x2e\x77\x6c\x78\147\152\x2e\143\156"; goto T2Lmb; ciWHy: $mail->SMTPAuth = true; goto ljpzm; U1mP1: MTPmb: goto qaf79; enoV2: goto t7BSf; goto oG0YP; utQz0: $mail = new PHPMailer(); goto Z_kyd; R10C6: foreach ($arr as $key => $value) { goto T8Ha9; JdD6f: $mail->addAddress($value, "\xe9\205\x92\345\xba\227\351\200\232\347\237\xa5"); goto KH5YS; T8Ha9: if (!$value) { goto IEOWs; } goto JdD6f; KH5YS: IEOWs: goto TQBw3; TQBw3: gLQbN: goto CUGxv; CUGxv: } goto U1mP1; l5nc1: $mail->Password = $smtp_key; goto eqfZU; e493E: $status = $mail->send(); goto qqmoe; tVlOq: $mail->Subject = $title; goto AVoky; AVoky: $mail->Body = $content; goto e493E; qqmoe: }