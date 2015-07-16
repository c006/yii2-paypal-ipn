<?php
namespace frontend\controllers;

use c006\paypal_ipn\PayPal_Ipn;
use Yii;
use yii\web\Controller;

class PaypalController extends Controller
{
    function init()
    {

        if (isset($_POST)) {
            /* Turn off CSRF from PayPal */
            Yii::$app->request->enableCsrfValidation = FALSE;
        }
    }


    public function actionIpn()
    {
        if (isset($_POST)) {
            $ipn = new PayPal_Ipn(TRUE);
            if ($ipn->init()) {

                /* Get any key/value */
                $custom = $ipn->getKeyValue('custom');

                /*

                Add your code here

                */

            }
        }

        /* Enable again if you use it */
        Yii::$app->request->enableCsrfValidation = TRUE;
    }


}

