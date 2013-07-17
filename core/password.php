<?php
namespace Core;

/**
 * Password - Класс работы с хещами паролей
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.1
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Password{

    final public static function getHash($password){
        $salt = '$2a$10$'.substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22).'$';

        return crypt($password, $salt);
    }
}

?>