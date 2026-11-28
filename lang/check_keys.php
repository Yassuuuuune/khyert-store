<?php
$files = ['affiliate','auth','cart','checkout','errors','footer','home','messages','nav','orders','pages','pagination','products','profile','reviews','roles','seller','site','status','tourism'];
$frBase = 'C:/Users/hp/Desktop/sahara-connect/lang/fr/';
$arBase = 'C:/Users/hp/Desktop/sahara-connect/lang/ar/';

function keysFlat($a, $p = '') {
    $r = [];
    if (is_array($a)) {
        foreach ($a as $k => $v) {
            $nk = $p === '' ? $k : $p . '.' . $k;
            if (is_array($v)) {
                $r = array_merge($r, keysFlat($v, $nk));
            } else {
                $r[] = $nk;
            }
        }
    }
    return $r;
}

$ok = true;
foreach ($files as $f) {
    $fr = require $frBase . $f . '.php';
    $ar = require $arBase . $f . '.php';
    $fk = keysFlat($fr);
    $ak = keysFlat($ar);
    $missing = array_diff($fk, $ak);
    $extra = array_diff($ak, $fk);
    if (count($missing) || count($extra)) {
        $ok = false;
        echo "MISMATCH $f: missing=" . json_encode(array_values($missing)) . " extra=" . json_encode(array_values($extra)) . PHP_EOL;
    } else {
        echo "OK $f (" . count($fk) . " keys)" . PHP_EOL;
    }
}
if ($ok) {
    echo PHP_EOL . 'ALL KEYS MATCH' . PHP_EOL;
}
