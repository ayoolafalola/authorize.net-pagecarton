<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    AuthorizeNet_Pay
 * @copyright  Copyright (c) 2020 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Pay.php Monday 6th of April 2020 05:24PM kayzenk@gmail.com $
 */

/**
 * @see PageCarton_Widget
 */

class AuthorizeNet_Pay extends AuthorizeNet_AuthorizeNet
{

	/**
	 * Whitelist and blacklist of currencies
	 *
	 * @var array
	 */
	protected static $_currency= array( 'whitelist' => '₦,NGN', 'blacklist' => 'ALL' );

	/**
	 * Form Action
	 *
	 * @var string
	 */
	protected static $_formAction = '';

	/**
	 * The method does the whole Class Process
	 *
	 */
	protected function init()
	{
        
        if( $this->getParameter( 'checkoutoption_name' ) )
        {
            self::$_apiName = $this->getParameter( 'checkoutoption_name' );
        }
        else
        {
            if( $api = Application_Subscription_Checkout_CheckoutOption::getInstance()->selectOne( null, array( 'object_name' => 'AuthorizeNet_CCCollect' ) ) )
            {
                self::$_apiName = $api['checkoutoption_name'];
            }
        }



        if( ! $cart = self::getStorage()->retrieve() )
        { 
            $this->_objectData = array( 'badnews' => 'Your shopping cart is empty' );
            return false;
        }
        $parametersX = static::getDefaultParameters();
        
        if( empty( $_POST['cc'] ) )
        {
            $this->_objectData = array( 'badnews' => 'Please enter a card number' );
            return false;
        }
        if( empty( $_POST['exp'] ) )
        {
            $this->_objectData = array( 'badnews' => 'Please enter card expiry date' );
            return false;
        }
        if( empty( $_POST['cvv'] ) )
        {
            $this->_objectData = array( 'badnews' => 'Please enter card CVV2' );
            return false;
        }
        $class = new Application_Subscription_Cart( array( 'return_object_data' => true ));
        $data = $class->view();
		$parametersX['email'] = Ayoola_Form::getGlobalValue( 'email' ) ? : ( Ayoola_Form::getGlobalValue( 'email_address' ) ? : Ayoola_Application::getUserInfo( 'email' ) );

        $record = AuthorizeNet_Transaction::getInstance()->insert( array( 'user_id' => Ayoola_Application::getUserInfo( 'user_id' ),'email' => $parametersX['email'], 'amount' => $data['total_price'], 'order_id' => $parametersX['order_number'] ) );

		$counter = 1;
        $parametersX['price'] = 0.00;
        if( $values = $cart['cart'] )
		foreach( $values as $name => $value )
		{
			if( ! isset( $value['price'] ) )
			{
				$value = array_merge( self::getPriceInfo( $value['price_id'] ), $value );
			}
			@@$parametersX['prod'] .= ' ' . $value['multiple'] . ' x ' . $value['subscription_label'];
			@$parametersX['price'] += floatval( $value['price'] * $value['multiple'] );
			$counter++;
		}
        
        $parameters = array();
        $parameters['createTransactionRequest'] = array();
        $parameters['createTransactionRequest']['merchantAuthentication'] = array();
        $parameters['createTransactionRequest']['merchantAuthentication']['name'] = AuthorizeNet_Settings::retrieve( 'api_login_id' );
        $parameters['createTransactionRequest']['merchantAuthentication']['transactionKey'] = AuthorizeNet_Settings::retrieve( 'transaction_key' );
        $parameters['createTransactionRequest']['refId'] = $parametersX['order_number'];
        $parameters['createTransactionRequest']['transactionRequest'] = array();
        $parameters['createTransactionRequest']['transactionRequest']['transactionType'] = 'authCaptureTransaction';
        $parameters['createTransactionRequest']['transactionRequest']['amount'] = $data['total_price'] ? : ( $this->getParameter( 'amount' ) ? : $parametersX['price'] );
        $parameters['createTransactionRequest']['transactionRequest']['currencyCode'] = AuthorizeNet_Settings::retrieve( 'currency' ) ? : 'USD';
        $parameters['createTransactionRequest']['transactionRequest']['payment'] = array();
        $parameters['createTransactionRequest']['transactionRequest']['payment']['creditCard'] = array();
        $parameters['createTransactionRequest']['transactionRequest']['payment']['creditCard']['cardNumber'] = str_replace( ' ', '', $_POST['cc'] );
        $parameters['createTransactionRequest']['transactionRequest']['payment']['creditCard']['expirationDate'] = $_POST['exp'];
        $parameters['createTransactionRequest']['transactionRequest']['payment']['creditCard']['cardCode'] = $_POST['cvv'];

        $json = json_encode( $parameters );

        $url  = 'https://apitest.authorize.net/xml/v1/request.api';
        if( AuthorizeNet_Settings::retrieve( 'mode' ) )
        {
            $url = 'https://api.authorize.net/xml/v1/request.api';
        }

        $settings['http_header'] = array(
            'Content-Type' => 'application/json'
        );
        $settings['post_fields'] = $json;

        $response = self::fetchLink( $url, $settings );
        
        //  fix bom in response
        if( $response[0] !== '{' )
        {
            $tmp_bom = pack('H*','EFBBBF');
            $response = preg_replace("/^$tmp_bom/", '', $response);
            unset($tmp_bom);
        }

        $result = json_decode( trim( $response ), true );
        $this->_objectData = $result;

        AuthorizeNet_Transaction::getInstance()->update( array( 'response_code' => $result['transactionResponse']['responseCode'], 'response' => $result, 'authorize.net_trans_id' => $result['transactionResponse']['transId'] ), array( 'transaction_id' => $record['transaction_id'] ) );

        if( 
            $result['transactionResponse']['responseCode'] === '1'
            && $result['messages']['resultCode'] === 'Ok'
        )
        {
            $this->_objectData += $parametersX;
            $this->setViewContent( '<p class="goodnews">' . $result['transactionResponse']['messages'][0]['description'] . '</p>' );
            $ex = self::checkStatus( $parametersX['order_number'] );
        }
        else
        {

        }
	}



    public static function checkStatus( $orderNumber )
    {
        $table = new Application_Subscription_Checkout_Order();
        if( ! $orderInfo = $table->selectOne( null, array( 'order_id' => $orderNumber ) ) )
        {
            return false;
        }
        if( ! is_array( $orderInfo['order'] ) )
        {
            //	compatibility
            $orderInfo['order'] = unserialize( $orderInfo['order'] );
        }
        $orderInfo['total'] = 0;

        foreach( $orderInfo['order']['cart'] as $name => $value )
        {
            if( ! isset( $value['price'] ) )
            {
                $value = array_merge( self::getPriceInfo( $value['price_id'] ), $value );
            }
            $orderInfo['total'] += $value['price'] * $value['multiple'];
        }

        $result = AuthorizeNet_Transaction::getInstance()->selectOne( null, array( 'order_id' => $orderNumber ) );
        if( $result['response_code'] === '1' )
		{
			//	Payment was not successful.
			$orderInfo['order_status'] = 99;
		}
		else
		{
			$orderInfo['order_status'] = 0;
		}

        $orderInfo['order_random_code'] = $result['authorize.net_trans_id'];
        $orderInfo['gateway_response'] = $result;
        self::changeStatus( $orderInfo );
        return $orderInfo;
    }
	// END OF CLASS
}
