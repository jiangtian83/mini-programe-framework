<?php
 goto gaRD_; A6gVE: function createSharePng($gData, $codeName, $fileName = '', $sourceurl) { goto wmPkw; VqFRw: $font_color_red = ImageColorAllocate($im, 217, 45, 32); goto LHP4B; dDdAe: $codeImg = createImageFromFile($codeName); goto J37hd; ak1kF: if ($fileName) { goto fAh2A; } goto qKpzt; ZzKms: imagedestroy($codeImg); goto b9rqB; JjvQR: imagedestroy($goodImg); goto ZzKms; OGpUz: $font_file = $sourceurl . "\x2f\x53\x54\x48\145\x69\x74\151\x2d\x4c\x69\147\150\164\x2e\x74\164\x63"; goto ddF0B; AqgD2: IbetM: goto sKdSs; ZCDZr: imagepng($im); goto aHbWT; ddF0B: $font_color_1 = ImageColorAllocate($im, 140, 140, 140); goto by2aA; jb15S: $goodImg = createImageFromFile($gData["\x70\x69\143"]); goto tK7J7; FR8aH: imagepng($im, $fileName); goto AqgD2; LHP4B: $fang_bg_color = ImageColorAllocate($im, 254, 216, 217); goto Aa0OC; aHbWT: goto IbetM; goto LZ2F2; ZBgZv: imagefill($im, 0, 0, $color); goto OGpUz; Aa0OC: list($g_w, $g_h) = getimagesize($gData["\x70\151\143"]); goto jb15S; by2aA: $font_color_2 = ImageColorAllocate($im, 28, 28, 28); goto MoWY7; XtGk_: $theTitle = cn_row_substr($gData["\164\151\x74\x6c\145"], 2, 17); goto LGCLA; J37hd: imagecopyresized($im, $codeImg, 440, 520, 0, 0, 170, 170, $code_w, $code_h); goto XtGk_; y4EQ2: imagettftext($im, 16, 0, 8, 630, $font_color_red, $font_file, "\351\225\277\346\x8c\211\345\x9b\276\xe7\211\207\350\257\206\345\x88\xab\344\272\214\347\xbb\264\347\xa0\201\xe5\215\xb3\xe5\x8f\257\xe8\277\233\345\x85\xa5"); goto ak1kF; v650I: list($code_w, $code_h) = getimagesize($codeName); goto dDdAe; LGCLA: imagettftext($im, 16, 0, 8, 550, $font_color_2, $font_file, $theTitle[1]); goto xR1Gy; LZ2F2: fAh2A: goto FR8aH; MoWY7: $font_color_3 = ImageColorAllocate($im, 129, 129, 129); goto VqFRw; wmPkw: $im = imagecreatetruecolor(618, 700); goto LU5Du; xR1Gy: imagettftext($im, 16, 0, 8, 590, $font_color_2, $font_file, $theTitle[2]); goto y4EQ2; tK7J7: imagecopyresized($im, $goodImg, 0, 0, 0, 0, 618, 500, $g_w, $g_h); goto v650I; sKdSs: imagedestroy($im); goto JjvQR; qKpzt: Header("\103\x6f\x6e\x74\145\156\x74\x2d\124\x79\160\145\x3a\x20\151\155\x61\147\145\57\160\x6e\147"); goto ZCDZr; LU5Du: $color = imagecolorallocate($im, 255, 255, 255); goto ZBgZv; b9rqB: } goto iuJ6y; Bg1J0: $title = $newsData["\x73\150\x61\x72\x65\x74\151\x74\154\145"]; goto K3AeR; mgG46: $gData = ["\160\151\x63" => $newsimg, "\x74\x69\164\x6c\x65" => $title]; goto Vo3HN; YMy5j: LOwp3: goto TsEaO; ab6Ha: D115z: goto IADLs; f9vr8: createSharePng($gData, $codeimg, ATTACHMENT_ROOT . "\151\x6d\x61\147\x65\x73\x2f\x79\171\x66\142\141\151\x64\x75\x2f" . $haibao . "\56\160\x6e\147", MODULE_ROOT . "\x2f\x69\155\141\147\145\163"); goto pQekf; C7xNm: uYZXe: goto yYpf0; gP_oV: $title = $newsData["\x74\151\164\x6c\145"]; goto Hx5w1; rLXOG: $errno = 1; goto sasMe; iuJ6y: function createImageFromFile($file) { goto wvg3q; msETo: xvvT1: goto iZPv_; eVkPg: switch ($fileSuffix) { case "\152\160\145\x67": $theImage = @imagecreatefromjpeg($file); goto EUGHq; case "\x6a\x70\x67": $theImage = @imagecreatefromjpeg($file); goto EUGHq; case "\x70\x6e\147": $theImage = @imagecreatefrompng($file); goto EUGHq; case "\x67\151\x66": $theImage = @imagecreatefromgif($file); goto EUGHq; default: $theImage = @imagecreatefromstring(file_get_contents($file)); goto EUGHq; } goto yVgS0; wvg3q: if (preg_match("\57\150\x74\x74\160\50\163\51\x3f\72\x5c\x2f\x5c\x2f\x2f", $file)) { goto IzpT6; } goto Su9qk; OB8ue: IzpT6: goto TW42i; yVgS0: x0jVN: goto PJw7N; NwM1e: goto xvvT1; goto OB8ue; iZPv_: if ($fileSuffix) { goto zbQ9m; } goto Ggxop; Su9qk: $fileSuffix = pathinfo($file, PATHINFO_EXTENSION); goto NwM1e; Ggxop: return false; goto hRsMm; TW42i: $fileSuffix = getNetworkImgType($file); goto msETo; PJw7N: EUGHq: goto oXEMO; hRsMm: zbQ9m: goto eVkPg; oXEMO: return $theImage; goto Pr4ud; Pr4ud: } goto Uy7zH; mETtH: $message = ''; goto mgG46; PhO6g: $newsimg = tomedia($sysshare); goto Ta3Kw; pQekf: $data["\x69\x6d\147"] = tomedia("\151\x6d\141\x67\x65\163\57\x79\x79\x66\142\141\151\x64\x75\57" . $haibao . "\56\160\x6e\x67"); goto lCk5M; Tsl6v: $scene = $newsData["\151\144"]; goto C7xNm; Pd4NU: if (empty($videosrc)) { goto T9Yt5; } goto T7snS; Hx5w1: $sysshare = pdo_getcolumn("\171\x79\x66\x5f\142\141\151\144\165\137\163\171\x73\151\156\x66\157", array("\165\x6e\x69\141\x63\151\144" => $uniacid), "\163\171\x73\x73\x68\141\x72\145"); goto PhO6g; TsEaO: $codeimg = ATTACHMENT_ROOT . "\151\x6d\x61\147\x65\163\x2f\x79\x79\146\x62\141\151\x64\x75\x2f" . $newsData["\x69\x64"] . "\56\x70\x6e\x67"; goto KVfu8; y6p4C: goto h5gkd; goto N2bGE; Fpd7o: die; goto jYZC9; tpnlu: $aid = $_GPC["\141\x69\x64"]; goto sAW7P; sAW7P: $APPID = $_W["\x61\x63\x63\157\165\156\164"]["\x6b\145\171"]; goto lAnao; kDmxi: if (is_dir($todir)) { goto LOwp3; } goto FoOGg; TFBaW: if (!$resultStr["\145\162\162\x63\x6f\x64\x65"]) { goto jSPnp; } goto P6NIr; MOWXg: $result = postData($post_data, $url); goto I7tYy; iK6wN: h5gkd: goto A6gVE; kWiS5: if ($newsData["\x74\x68\165\x6d\142"] != '') { goto eOP_k; } goto gP_oV; Y2sNP: if ($newsData["\144\151\x79\163\150\141\162\145"] == 1) { goto mqTDv; } goto kWiS5; yYpf0: $post_data = array("\163\143\145\156\145" => $scene, "\160\141\164\x68" => "\x79\171\146\x5f\x62\x61\x69\x64\x75\x2f\160\141\147\x65\x73\x2f\151\x6e\x64\145\170\x2f\151\x6e\x64\x65\x78", "\x77\151\144\164\150" => "\x32\70\60", "\x69\x73\137\150\171\141\x6c\x69\x6e\x65" => true); goto ZTzsd; qnnIc: $token = $resultStr["\x61\x63\x63\145\x73\x73\137\x74\x6f\x6b\x65\156"]; goto OFh_e; jYZC9: jSPnp: goto qnnIc; y5s_t: $openid = $_GPC["\x6f\160\145\x6e\x69\144"]; goto EM6H4; AZ0hh: eOP_k: goto ISrdr; N2bGE: qPh8m: goto LzGPi; kzyhf: $resultStr = json_decode(httpGet($url), true); goto TFBaW; KVfu8: $res = file_put_contents($codeimg, $result); goto Y2sNP; Vo3HN: $haibao = "\150\x61\151\142\141\x6f" . $newsData["\151\x64"]; goto f9vr8; LzGPi: $errno = 0; goto mETtH; Ta3Kw: goto D115z; goto o7v0x; bhFwl: function mg_cn_substr($str, $len, $start = 0) { goto EXpjQ; B04bR: n4q9J: goto Z4RhD; PheF3: nYVYe: goto oPRZ6; FfzCF: return $q_str; goto Zr7dR; aBSFH: IFqRj: goto FfzCF; dA8pv: $i += 2; goto lcQ0O; pAuxn: goto n4q9J; goto aBSFH; sHIZA: $i++; goto pAuxn; PcNPp: if (ord(substr($str, $i, 1)) > 0xa0) { goto uTXgm; } goto Yj6fR; qr0ww: $a = 0; goto BPQVb; ncOz6: $m_str = substr($str, $new_start, 3); goto IApvg; Y4X5x: if (!($start and json_encode(substr($str, $start, 1)) === false)) { goto nYVYe; } goto qr0ww; pC6_9: $new_start = $start + $a; goto ncOz6; IApvg: if (!(json_encode($m_str) !== false)) { goto HT4S8; } goto m4Iq4; BPQVb: o6Ebp: goto KNtZO; m4Iq4: $start = $new_start; goto MI14a; zNigP: HT4S8: goto v1xCB; XK3YR: rLcFc: goto sHIZA; oPRZ6: $i = $start; goto B04bR; MI14a: goto YI3m3; goto zNigP; v1xCB: SdHmH: goto dzHpa; BjdXk: YI3m3: goto PheF3; u3aJm: goto o6Ebp; goto BjdXk; KNtZO: if (!($a < 3)) { goto YI3m3; } goto pC6_9; Z4RhD: if (!($i < $q_strlen)) { goto IFqRj; } goto PcNPp; Yj6fR: $q_str .= substr($str, $i, 1); goto eQZ3A; HRNf5: $q_str .= substr($str, $i, 3); goto dA8pv; dzHpa: $a++; goto u3aJm; lcQ0O: u4DMh: goto XK3YR; EXpjQ: $q_str = ''; goto MDFMO; eQZ3A: goto u4DMh; goto CUfPf; MDFMO: $q_strlen = $start + $len > strlen($str) ? strlen($str) : $start + $len; goto Y4X5x; CUfPf: uTXgm: goto HRNf5; Zr7dR: } goto acToJ; lAnao: $SECRET = $_W["\x61\143\143\157\165\156\164"]["\163\145\143\x72\145\x74"]; goto dklBN; ZTzsd: $url = "\150\164\164\x70\163\x3a\x2f\x2f\x61\x70\151\x2e\x77\x65\x69\170\x69\x6e\x2e\161\x71\56\143\157\155\x2f\x77\170\x61\57\147\145\x74\167\x78\x61\143\157\144\145\x75\156\154\x69\155\151\164\x3f\x61\x63\x63\145\x73\163\x5f\164\x6f\x6b\145\x6e\75" . $token; goto MOWXg; IADLs: if ($res) { goto qPh8m; } goto rLXOG; YJN4x: goto D115z; goto AZ0hh; DdZQu: $videosrc = trim($newsData["\166\151\x64\x65\x6f\163\162\143"]); goto Pd4NU; o7v0x: mqTDv: goto Bg1J0; D46kU: goto uYZXe; goto Fbs35; z6Bcf: function cn_row_substr($str, $row = 1, $number = 10, $suffix = true) { goto AtznS; Oi8_Z: CfJDP: goto qWL5l; gZOEU: FdhpZ: goto Lu9Mn; lYd4l: $r = 1; goto PpFem; gdQnW: $result[$r] = mg_cn_substr($str, $oneRowNum, ($r - 1) * $oneRowNum); goto nRxrT; cd5Aq: return $result; goto QwbQr; mqs7O: XTcIe: goto pbQqo; lm88_: if (!($r <= $row)) { goto CfJDP; } goto qQe2p; QLeN_: $r = 1; goto sPbbc; PpFem: mVXCh: goto ny0p3; QwbQr: Y1sDd: goto LTpPp; GqtLm: goto mVXCh; goto mqs7O; ny0p3: if (!($r <= $row)) { goto XTcIe; } goto FWiXF; qQe2p: if ($r == $row and $theStrlen > $r * $oneRowNum and $suffix) { goto FdhpZ; } goto gdQnW; bJZGJ: if (!($theStrlen < $r * $oneRowNum)) { goto nNrKY; } goto EHi1b; LTpPp: $theStrlen = strlen($str); goto S5q3V; pbQqo: $str = trim($str); goto pPmHU; pPmHU: if ($str) { goto Y1sDd; } goto cd5Aq; pY0c9: d1zqQ: goto ZpiK0; nRxrT: goto T_Arl; goto gZOEU; FWiXF: $result[$r] = ''; goto pY0c9; EHi1b: goto CfJDP; goto Ih8tQ; qWL5l: return $result; goto xb7nX; ZpiK0: $r++; goto GqtLm; Lu9Mn: $result[$r] = mg_cn_substr($str, $oneRowNum - 6, ($r - 1) * $oneRowNum) . "\56\56\x2e"; goto pcWaM; EjRI2: i7uCi: goto tzyGC; pcWaM: T_Arl: goto bJZGJ; tzyGC: $r++; goto P7P88; P7P88: goto KAgZW; goto Oi8_Z; Ih8tQ: nNrKY: goto EjRI2; sPbbc: KAgZW: goto lm88_; S5q3V: $oneRowNum = $number * 3; goto QLeN_; AtznS: $result = array(); goto lYd4l; xb7nX: } goto bhFwl; gaRD_: global $_GPC, $_W; goto y5s_t; Uy7zH: function getNetworkImgType($url) { goto cw65e; kh4Vo: N2yiW: goto sLTZ0; Ud41L: goto J7HUi; goto kh4Vo; rPVka: return false; goto Nmbcp; plRTn: curl_setopt($ch, CURLOPT_TIMEOUT, 3); goto YQh13; bqPfq: curl_close($ch); goto NH2Dc; YQh13: curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); goto Kz3IE; NH2Dc: if ($http_code["\150\164\x74\160\137\x63\157\144\x65"] == 200) { goto N2yiW; } goto YDbch; tfqJ5: curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); goto plRTn; sLTZ0: $theImgType = explode("\57", $http_code["\143\157\x6e\164\145\x6e\x74\137\x74\171\x70\x65"]); goto w0sv8; ro5R3: y25L_: goto kjtmd; iaJWM: J7HUi: goto CztLu; MTnQZ: curl_setopt($ch, CURLOPT_URL, $url); goto YerA6; D3VFN: aMNO2: goto iaJWM; kjtmd: return $theImgType[1]; goto D3VFN; cw65e: $ch = curl_init(); goto MTnQZ; u1GUV: $http_code = curl_getinfo($ch); goto bqPfq; Nmbcp: goto aMNO2; goto ro5R3; YerA6: curl_setopt($ch, CURLOPT_NOBODY, 1); goto tfqJ5; w0sv8: if ($theImgType[0] == "\151\155\x61\x67\145") { goto y25L_; } goto rPVka; YDbch: return false; goto Ud41L; Kz3IE: curl_exec($ch); goto u1GUV; CztLu: } goto z6Bcf; Fbs35: T9Yt5: goto Tsl6v; acToJ: function httpGet($url) { goto abhi3; f10v5: curl_setopt($curl, CURLOPT_TIMEOUT, 500); goto JSNCj; abhi3: $curl = curl_init(); goto eHyTe; JSNCj: curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); goto xtWp6; AtmUk: $res = curl_exec($curl); goto DERcV; eHyTe: curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); goto f10v5; DERcV: curl_close($curl); goto wQkSJ; wQkSJ: return $res; goto aIcBq; xtWp6: curl_setopt($curl, CURLOPT_URL, $url); goto AtmUk; aIcBq: } goto jLgu8; Fz1uc: return $this->result($errno, $message); goto y6p4C; lCk5M: return $this->result($errno, $message, $data["\x69\x6d\x67"]); goto iK6wN; OFh_e: $newsData = pdo_get("\171\x79\x66\137\142\x61\x69\x64\165\x5f\x6e\145\x77\x73", array("\151\x64" => $aid)); goto DdZQu; w5xnB: $newsimg = tomedia($newsData["\x74\x68\165\155\142"]); goto ab6Ha; ISrdr: $title = $newsData["\164\x69\164\x6c\145"]; goto w5xnB; EM6H4: $uniacid = $_GPC["\165\x6e\151\141\x63\151\144"]; goto tpnlu; K3AeR: $newsimg = tomedia($newsData["\163\x68\x61\162\145\x69\x6d\147"]); goto YJN4x; sasMe: $message = "\xe7\224\237\346\x88\220\xe5\xa4\261\xe8\264\245\357\xbc\x8c\350\257\267\xe9\207\215\350\xaf\225\xe4\270\200\346\xac\241\346\x88\x96\xe8\200\205\345\x87\xba\347\xa4\272\xe8\256\242\345\215\x95\xe5\217\xb7\xe8\256\xa9\xe5\xae\xa2\346\x9c\215\xe6\x93\x8d\xe4\xbd\x9c"; goto Fz1uc; P6NIr: return $this->result(1, "\xe5\xb0\x8f\xe7\250\213\xe5\272\217\x53\105\103\x52\x45\x54\xe8\256\276\347\275\xae\xe9\224\x99\xe8\xaf\xaf"); goto Fpd7o; I7tYy: $todir = ATTACHMENT_ROOT . "\x69\x6d\141\147\145\x73\x2f\x79\171\146\142\x61\x69\x64\165\57"; goto kDmxi; FoOGg: mkdir($todir); goto YMy5j; dklBN: $url = "\x68\164\164\160\163\x3a\57\57\x61\160\x69\x2e\167\145\151\170\151\156\x2e\x71\161\x2e\143\157\155\57\143\x67\151\55\142\151\156\x2f\x74\x6f\x6b\x65\156\x3f\147\162\x61\x6e\164\x5f\164\x79\x70\x65\x3d\x63\154\x69\145\x6e\164\137\143\162\145\x64\x65\x6e\164\151\141\154\x26\141\x70\x70\151\x64\75" . $APPID . "\x26\x73\x65\x63\162\x65\164\x3d" . $SECRET; goto kzyhf; T7snS: $scene = $newsData["\x69\x64"] . "\x2d"; goto D46kU; jLgu8: function postData($post_data, $sendUrl) { goto XXXb8; lDgzq: curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); goto WWAzD; XXXb8: $post_data = json_encode($post_data); goto MH_1q; WWAzD: $result = curl_exec($curl); goto Cu6Oa; MH_1q: $curl = curl_init(); goto NUC4i; TL7eX: return $result; goto qgwNB; XmRAk: curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); goto ENRBu; NUC4i: curl_setopt($curl, CURLOPT_URL, $sendUrl); goto XmRAk; w3Ujf: curl_setopt($curl, CURLOPT_POST, 1); goto BNN8G; Cu6Oa: if (!curl_errno($curl)) { goto fzl3t; } goto dqhgQ; BNN8G: curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); goto lDgzq; dqhgQ: return "\105\x72\162\x6e\157" . curl_error($curl); goto rTFUH; ENRBu: curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); goto w3Ujf; VfAVs: curl_close($curl); goto TL7eX; rTFUH: fzl3t: goto VfAVs; qgwNB: }