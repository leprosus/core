<?php
namespace Core;

/**
 * Payment - Класс для отправки почты через PHP mail и SMTP
 *
 * @author  eVa laboratory <http://www.evalab.ru>
 * @package eVaCore
 * @version 0.3
 * @license Shared Code <http://evacore.evalab.ru/license.html>
 */
final class Mail {
    const METHOD_MAIL = 'mail';
    const METHOD_SMTP = 'smtp';
    const PRIORITY_HIGHEST = 'Highest';
    const PRIORITY_HIGH = 'High';
    const PRIORITY_NORMAL = 'Normal';
    const PRIORITY_LOW = 'Low';
    const PRIORITY_LOWEST = 'Lowest';

    private $method;
    private $encoding = 'UTF-8';
    private $address = '';
    private $login = '';
    private $password = '';
    private $port = 25;
    private $debug = array();
    private $emailTo;
    private $subject = '';
    private $text = '';
    private $emailFrom = '';
    private $emailReply = '';
    private $priority = self::PRIORITY_NORMAL;
    private $attach = array();

    function __construct($method = self::METHOD_MAIL) {
        $this->method = $method;
    }

    /**
     * Возвращает метод отправки
     * @access public
     * @return TYPE_MAIL|TYPE_SMTP
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Устанавливает метод отправки
     * @access public
     * @param TYPE_MAIL|TYPE_SMTP $method Метод отправки (<b>использовать только констатны класса</b>)
     * @return void
     * @return \Core\Mail
     */
    public function setMethod($method) {
        $this->method = $method;

        return $this;
    }

    /**
     * Возвращает кодировку отправки
     * @access public
     * @return string
     */
    public function getEncoding() {
        return $this->encoding;
    }

    /**
     * Устанавливает кодировку отправки
     * @access public
     * @param string $encoding Кодировка, используемая при отправки (<b>UTF-8 по-умолчанию</b>)
     * @return \Core\Mail
     */
    public function setEncoding($encoding) {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Возвращает адрес для SMTP отправки
     *
     * @access public
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Устанавливает адрес для SMTP отправки
     *
     * @access public
     * @param string $address Адрес smtp-сервера
     * @return \Core\Mail
     */
    public function setAddress($address) {
        $this->address = $address;

        return $this;
    }

    /**
     * Возвращает логин для SMTP авторизации
     *
     * @access public
     * @return string
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * Устанавливает логин для SMTP авторизации
     *
     * @access public
     * @param string $login Логин для авторизации на smtp-сервере
     * @return \Core\Mail
     */
    public function setLogin($login) {
        $this->login = $login;

        return $this;
    }

    /**
     * Возвращает пароль для SMTP авторизации
     *
     * @access public
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Устанавливает пароль для SMTP авторизации
     *
     * @access public
     * @param string $login Пароль для авторизации на smtp-сервере
     * @return \Core\Mail
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Возвращает номер порта для SMTP отправки
     *
     * @access public
     * @return int
     */
    public function getPort() {
        return (int)$this->port;
    }

    /**
     * Устанавливает порт для SMTP отправки
     *
     * @access public
     * @param string $port Порт smtp-сервера
     * @return \Core\Mail
     */
    public function setPort($port) {
        $this->port = (int)$port;

        return $this;
    }

    /**
     * Возращает Email получателя
     *
     * @access public
     * @return string
     */
    public function getEmailTo() {
        return $this->emailTo;
    }

    /**
     * Устанока Email получателя
     *
     * @access public
     * @param type $emailTo
     * @return \Core\Mail
     */
    public function setEmailTo($emailTo) {
        $this->emailTo = $emailTo;

        return $this;
    }

    /**
     * Возвращает тему письма
     * @access public
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Устанавливает тему письма
     * @access public
     * @param string $subjec Тема письма (необязательный параметр)
     * @return \Core\Mail
     */
    public function setSubject($subject) {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Возвращает текст письма
     * @access public
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Устанавливает текст письма
     * @access public
     * @param type $text Текст письма
     * @return \Core\Mail
     */
    public function setText($text) {
        $this->text = $text;

        return $this;
    }

    /**
     * Возаращает Email отправителя
     *
     * @access public
     * @return type
     */
    public function getEmailFrom() {
        return $this->emailFrom;
    }

    /**
     * Устанавливает Email отправителя
     *
     * @access public
     * @param string $emailFrom Email отправителя
     * @return \Core\Mail
     */
    public function setEmailFrom($emailFrom) {
        $this->emailFrom = $emailFrom;

        return $this;
    }

    /**
     * Возвращает Email для ответа
     *
     * @access public
     * @return string
     */
    public function getEmailReply() {
        return $this->emailReply;
    }

    /**
     * Устанавливает Email для ответа
     *
     * @access public
     * @param string $emailReply Email для ответа
     * @return \Core\Mail
     */
    public function setEmailReply($emailReply) {
        $this->emailReply = $emailReply;

        return $this;
    }

    /**
     * Возвращает приоритет письма
     * @access public
     * @see Смотри константы объекта
     * @return string
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * Устанавливает приоритет письма
     * @access public
     * @param string $priority Приоритет письма
     * @see Смотри константы объекта
     * @return string
     */
    public function setPriority($priority) {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Возвращает массив со ссылками на вложенные файлы
     * @access public
     * @return array
     */
    public function getAttach() {
        return $this->attach;
    }

    /**
     * Устанавливает массив со ссылками на файлы для вложения в письмо
     * @param array $attach Массив со ссылками на файлы
     * @return \Core\Mail
     */
    public function setAttach($attach) {
        $this->attach = $attach;

        return $this;
    }

    /**
     * Добавляет ссылку на файл для вложения в письмо
     * @param string $filePath Ссылка на файл
     * @return \Core\Mail
     */
    public function addAttach($filePath) {
        $this->attach[] = $filePath;

        return $this;
    }

    /**
     * Возвращает DEBUG-сообщение после SMTP отправки
     *
     * @access public
     * @return string
     */
    public function getSmtpDebug() {
        return implode("\n", $this->debug);
    }

    /**
     * Отправляет письмо
     * @access public
     * @return boolean Признак отправки письма. При отсутствии ошибок во время отправки - true.
     */
    public function send() {
        $result = false;

        if($this->method == self::METHOD_MAIL) {
            $result = $this->sendByMail($this->emailTo, $this->subject, $this->text, $this->emailFrom, $this->emailReply, $this->priority, $this->attach);
        } else if($this->method == self::METHOD_SMTP) {
            $result = $this->sendBySmtp($this->emailTo, $this->subject, $this->text, $this->emailFrom, $this->emailReply, $this->priority, $this->attach);
        }

        return $result;
    }

    /**
     * Отправка почты средствами php mail
     *
     * @param string $emailTo Адрес получателя
     * @param string $emailSubjec Тема письма (необязательный параметр)
     * @param string $text Текст письма (необязательный параметр)
     * @param string $emailFrom Адрес отправителя письма (необязательный параметр)
     * @param string $emailReply Адрес для ответа (необязательный параметр)
     * @param int $priority Приоритет письма (1 - наивысший, 2 - высокий, 3 - нормальный, 4 - по умолчанию, 5 - наименьший)
     * @param array $attach Вложенные в письмо файлы в виде массива (необязательный параметр)
     * @return boolean Признак отправки письма. При отсутствии ошибок во время отправки - true.
     */
    private function sendByMail($emailTo, $subject, $text, $emailFrom, $emailReply, $priority, $attach) {
        $this->generateBody($emailTo, $subject, $text, $emailFrom, $emailReply, $priority, $attach, $header, $sendText);

        return @mail($emailTo, '=?' . $this->encoding . '?Q?' . str_replace('+', '_', str_replace('%', '=', urlencode($subject))) . '?=', $sendText, $header);
    }

    /**
     * Отправка почты средствами SMTP
     *
     * @param string $emailTo Адрес получателя
     * @param string $emailSubjec Тема письма (необязательный параметр)
     * @param string $text Текст письма (необязательный параметр)
     * @param string $emailFrom Адрес отправителя письма (необязательный параметр)
     * @param string $emailReply Адрес для ответа (необязательный параметр)
     * @param int $priority Приоритет письма (1 - наивысший, 2 - высокий, 3 - нормальный, 4 - по умолчанию, 5 - наименьший)
     * @param array $attach Вложенные в письмо файлы в виде массива (необязательный параметр)
     * @return boolean Признак отправки письма. При отсутствии ошибок во время отправки - true.
     */
    private function sendBySmtp($emailTo, $subject, $text, $emailFrom, $emailReply, $priority, $attach) {
        $result = false;

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($socket >= 0) {
            $this->debug[] = 'Подключаюсь к ' . $this->address . ':' . $this->port . ' ... ';
            if(socket_connect($socket, $this->address, $this->port)) {
                if($this->readSmtp($socket)) {
                    $this->writeSmtp($socket, 'EHLO ' . $this->login);
                    if($this->readSmtp($socket)) {
                        $this->debug[] = 'Аутентификация ... ';
                        $this->writeSmtp($socket, 'AUTH LOGIN');
                        if($this->readSmtp($socket)) {
                            $this->writeSmtp($socket, base64_encode($this->login));
                            if($this->readSmtp($socket)) {
                                $this->writeSmtp($socket, base64_encode($this->password));
                                if($this->readSmtp($socket)) {
                                    $this->debug[] = 'Проверка адреса отправителя ... ';
                                    $this->writeSmtp($socket, 'MAIL FROM:<' . $emailFrom . '>');
                                    if($this->readSmtp($socket)) {
                                        $this->debug[] = 'Проверка адреса получателя ... ';
                                        $this->writeSmtp($socket, 'RCPT TO:<' . $emailTo . '>');
                                        if($this->readSmtp($socket)) {
                                            $this->debug[] = 'Отправка текста письма ... ';
                                            $this->writeSmtp($socket, 'DATA');
                                            if($this->readSmtp($socket)) {
                                                $this->generateBody($emailTo, $subject, $text, $emailFrom, $emailReply, $priority, $attach, $header, $sendText);
                                                $this->writeSmtp($socket, $header . $sendText . "\r\n.");
                                                if($this->readSmtp($socket)) {
                                                    $this->debug[] = 'Закрываем подключение ... ';
                                                    $this->writeSmtp($socket, 'QUIT');
                                                    if($this->readSmtp($socket)) {
                                                        $result = true;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $this->debug[] = 'Ошибка подключения к сокету: ' . socket_strerror(socket_last_error());
            }
        } else {
            $this->debug[] = 'Ошибка создания сокета: ' . socket_strerror(socket_last_error());
        }

        if(isset($socket)) {
            socket_close($socket);
        }

        return $result;
    }

    /**
     * Генерирует заголовок и содержание письма
     * @param string $emailTo Адрес получателя
     * @param string $emailSubjec Тема письма (необязательный параметр)
     * @param string $text Текст письма (необязательный параметр)
     * @param string $emailFrom Адрес отправителя письма (необязательный параметр)
     * @param string $emailReply Адрес для ответа (необязательный параметр)
     * @param int $priority Приоритет письма
     * @param array $attach Вложенные в письмо файлы в виде массива (необязательный параметр)
     * @param string $header По результату в эту переменную будет записан результат для заголовка
     * @param string $sendText По результату в эту переменную будет записан результат для содержания
     * @see Смотри константы объекта
     * @return void
     */
    private function generateBody($emailTo, $subject, $text, $emailFrom, $emailReply, $priority, $attach, &$header, &$sendText) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $boundary = '';
        $amount = strlen($chars) - 1;
        for($cycle = 1; $cycle < 15; $cycle++) {
            $boundary .= $chars[rand(0, $amount)];
        }

        $header = array();
        $sendText = array();
        $header[] = 'Date: ' . date('r');
        if(!empty($emailFrom)) {
            $header[] = 'From: <' . $emailFrom . '>';
        }
        if($this->method == self::METHOD_SMTP) {
            $header[] = 'To: <' . $emailTo . '>';
            if(!empty($subject)) {
                $header[] = 'Subject: ' . $subject;
            }
        }
        if(!empty($emailReply)) {
            $header[] = 'Reply-To: <' . $emailReply . '>';
        }
        $header[] = 'X-Mailer: eVa Mail-Sender';
        $header[] = 'X-Priority: ' . $priority . ' (' . $priority . ')';
        $header[] = 'MIME-Version: 1.0';
        $header[] = 'Content-Type: multipart/mixed; boundary="----------' . $boundary . '"';
        $header[] = '';
        $sendText[] = '------------' . $boundary;
        $sendText[] = 'Content-Type: text/html; charset=' . $this->encoding;
        $sendText[] = 'Content-Transfer-Encoding: 8bit';
        $sendText[] = '';
        $sendText[] = $text;
        $sendText[] = '';
        foreach($attach as $fileName) {
            $pathinfo = pathinfo($fileName);
            $file = $pathinfo['basename'];
            $sendText[] = '------------' . $boundary;
            $sendText[] = 'Content-Type: application/octet-stream; name="' . $file . '"';
            $sendText[] = 'Content-Transfer-Encoding: Base64';
            $sendText[] = 'Content-ID: <' . $file . '>';
            $sendText[] = 'Content-Disposition: attachment; filename="' . $file . '"';
            $sendText[] = '';
            $file = fopen($fileName, 'rb');
            $sendText[] = chunk_split(base64_encode(fread($file, filesize($fileName))));
            $sendText[] = '';
            fclose($file);
        }
        $sendText[] = '------------' . $boundary . '--';

        $header = implode("\n", $header);
        $sendText = implode("\n", $sendText);
    }

    /**
     * Чтение из сокета результата отправки комманды @see writeSmtp
     * @param resource $socket Обработчик сокета
     * @return boolean Результат работы
     */
    private function readSmtp($socket) {
        $result = true;
        $read = socket_read($socket, 1024);
        if($read{0} != '2' && $read{0} != '3') {
            $this->debug[] = empty($read) ? 'Неизвестная ошибка' : 'Ошибка SMTP: ' . $read;
            $result = false;
        }

        return $result;
    }

    /**
     * Запись комманды в сокет
     * @param resource $socket Обработчик сокета
     * @param string $text SMTP комманда
     * @return void
     */
    private function writeSmtp($socket, $text) {
        $text = $text . "\r\n";
        socket_write($socket, $text, strlen($text));
    }
}

?>