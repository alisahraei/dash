<?php
namespace dash\utility\payment\pay;


class mellat
{

    public static function mellat($_token)
    {
        if(!\dash\option::config('mellat', 'status'))
        {
            \dash\db\logs::set('pay:mellat:status:false');
            \dash\notif::error(T_("The mellat payment on this service is locked"));
            return false;
        }

        if(!\dash\option::config('mellat', 'TerminalId'))
        {
            \dash\db\logs::set('pay:mellat:TerminalId:null');
            \dash\notif::error(T_("The mellat payment TerminalId not set"));
            return false;
        }

        if(!\dash\option::config('mellat', 'UserName'))
        {
            \dash\db\logs::set('pay:mellat:UserName:null');
            \dash\notif::error(T_("The mellat payment UserName not set"));
            return false;
        }

        $mellat                   = [];
        $mellat['terminalId']     = \dash\option::config('mellat', 'TerminalId');
        $mellat['userName']       = \dash\option::config('mellat', 'UserName');
        $mellat['userPassword']   = \dash\option::config('mellat', 'UserPassword');
        $mellat['localDate']      = date("Ymd");
        $mellat['localTime']      = date("His");
        $mellat['additionalData'] = null;
        $mellat['payerId']        = \dash\user::id();


        if(\dash\option::config('mellat', 'callBackUrl'))
        {
            $mellat['callBackUrl'] = \dash\option::config('mellat', 'callBackUrl');
        }
        else
        {
            $mellat['callBackUrl'] = \dash\utility\pay\setting::get_callbck_url('mellat');
        }


        $amount = \dash\utility\pay\setting::get_plus();
        $amount = floatval($amount) * 10;

        // change rial to toman
        // but the plus is toman
        // need less to *10 the plus
        $mellat['amount'] = (string) $amount;

        //START TRANSACTION BY CONDITION REQUEST
        $transaction_id = \dash\utility\pay\setting::get_id();

        if(!$transaction_id)
        {
            return false;
        }

        // set in this step and check in other step
        // $mellat['specialPaymentId'] = $transaction_id;
        $mellat['orderId'] = $transaction_id;

        $RefId = \dash\utility\payment\payment\mellat::pay($mellat);

        \dash\utility\pay\setting::set_payment_response1(\dash\utility\pay\api\mellat\bank::$payment_response);

        if($RefId)
        {
            \dash\utility\pay\setting::set_condition('redirect');

            \dash\utility\pay\setting::set_banktoken($RefId);

            \dash\utility\pay\setting::save();


            // redirect to enter/redirect
            \dash\session::set('redirect_page_url', 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat');
            \dash\session::set('redirect_page_method', 'post');
            \dash\session::set('redirect_page_args', ['RefId' => $RefId]);
            \dash\session::set('redirect_page_title', T_("Redirect to mellat payment"));
            \dash\session::set('redirect_page_button', T_("Redirect"));
            \dash\notif::direct();
            \dash\redirect::to(\dash\utility\pay\setting::get_callbck_url('redirect_page'));
            return true;
        }
        else
        {
            \dash\utility\pay\setting::save();
            return false;
        }
    }
}
?>
