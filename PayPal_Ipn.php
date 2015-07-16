<?php


namespace c006\paypal_ipn;

use Yii;

/**
 * Class PayPalIpn
 * @package c006\paypal_ipn
 */

/**
 * Class PayPal_Ipn
 * @package c006\paypal_ipn
 */
class PayPal_Ipn
{
    /**
     * @var string
     */
    private $sandbox = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    /**
     * @var string
     */
    private $live = 'https://www.paypal.com/cgi-bin/webscr';
    /**
     * @var bool
     */
    private $is_live = FALSE;
    /**
     * @var array|null
     */
    private $post;
    /**
     * @var string
     */
    private $validate = 'cmd=_notify-validate';

    /**
     * @var bool
     */
    private $debug = FALSE;

    /**
     * @var string
     */
    private $log = 'paypal-ipn.log';

    /**
     * @var array
     */
    private $array = [];


    /**
     * @param bool $is_live
     * @param bool $debug
     */
    function __construct($is_live = FALSE, $debug = FALSE)
    {
        $this->is_live = $is_live;
        $this->debug = $debug;
        $this->log = Yii::getAlias('@frontend') . '/runtime/' . $this->log;
    }


    /**
     *
     */
    function init()
    {

        $this->array = [];
        $this->post = file_get_contents('php://input');
        $this->post = explode('&', $this->post);

        foreach ($this->post as $item) {
            list($key, $value) = explode('=', stripslashes($item));
            $this->array[ $key ] = urldecode($value);
            $this->validate .= '&' . $key . '=' . $value;
        }

        $ch = ($this->is_live) ? curl_init($this->live) : curl_init($this->sandbox);

        if ($ch == FALSE) {
            error_log(date('[Y-m-d H:i e] ') . "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, $this->log);

            return FALSE;
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->validate);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 4);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        if ($this->debug == TRUE) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        $return = curl_exec($ch);

        if ($this->debug == TRUE) {
            error_log(PHP_EOL . date('[Y-m-d H:i e] ') . "HTTP request of validation request:" . curl_getinfo($ch, CURLINFO_HEADER_OUT) . PHP_EOL, 3, $this->log);
            error_log(PHP_EOL . date('[Y-m-d H:i e] ') . "HTTP response of validation request: " . $this->validate . PHP_EOL, 3, $this->log);
        }

        curl_close($ch);

        if (stripos($return, 'VERIFIED') !== FALSE) {
            error_log(PHP_EOL . 'VERIFIED: ', 3, $this->log);
            error_log(PHP_EOL . print_r($this->validate, TRUE) . PHP_EOL, 3, $this->log);

            return TRUE;
        }

        error_log(PHP_EOL . date('[Y-m-d H:i e] ') . "Invalid IPN:" . $this->validate . PHP_EOL, 3, $this->log);

        return FALSE;
    }


    /**
     * @return array
     */
    public function getArray()
    {
        return $this->array;
    }


    /**
     * @param $key
     *
     * @return string
     */
    public function getKeyValue($key)
    {
        foreach ($this->array as $k => $v) {
            if (strtolower($key) == strtolower($k)) {
                return $v;
            }
        }

        return '';
    }
}