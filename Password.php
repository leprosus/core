<?php
namespace Core;

/**
 * Password - Класс работы с хещами паролей
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Password {
    const ALPHA = 1;
    const UPPER = 2;
    const SYMBOLS = 4;
    const DIGITS = 8;

    private static $all
        = array(
            'ALPHA' => 'abcdefghijklmnopqrstuvwxyz',
            'UPPER' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'SYMBOLS' => '~`!@#$%^&*()-_+=',
            'DIGITS' => '1234567890'
        );

    final public static function getHash($password) {
        $salt = '$2a$10$' . substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22) . '$';

        return crypt($password, $salt);
    }

    final public static function generate($length, $flags = null) {
        $base = '';
        if(!Type::isNull($flags)) {
            $reflection = new \ReflectionClass(get_called_class());
            $constants = $reflection->getConstants();

            foreach($constants as $name => $value) {
                if($flags & $value) {
                    $base .= self::$all[$name];
                }
            }
        }
        $base = str_split(empty($base) ? implode('', self::$all) : $base);

        $amount = count($base);
        for($index = 0; $index < $amount; $index++) {
            $rnd = rand(0, $amount - 1);

            list($base[$index], $base[$rnd]) = array($base[$rnd], $base[$index]);
        }

        $password = '';
        for($index = 0; $index < $length; $index++) {
            $rnd = rand(0, $amount - 1);

            $password .= $base[$rnd];
        }

        return $password;
    }
}

?>