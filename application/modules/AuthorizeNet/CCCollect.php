<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    AuthorizeNet_CCCollect
 * @copyright  Copyright (c) 2021 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: CCCollect.php Thursday 21st of January 2021 11:47AM ayoola@ayoo.la $
 */

/**
 * @see PageCarton_Widget
 */

class AuthorizeNet_CCCollect extends AuthorizeNet_Pay
{
	
    /**
     * Access level for player. Defaults to everyone
     *
     * @var boolean
     */
	protected static $_accessLevel = array( 0 );
	
    /**
     * 
     * 
     * @var string 
     */
	protected static $_objectTitle = 'Payment Information'; 

    /**
     * Performs the whole widget running process
     * 
     */
	public function init()
    {    
		try
		{ 

            
            $class = new Application_Subscription_Cart( array( 'return_object_data' => true ));
            $data = $class->view();

            $parametersX = static::getDefaultParameters();
            if( ! $cart = self::getStorage()->retrieve() )
            { 
                $this->setViewContent( '<p class="badnews">Shopping Cart is empty</p>' );
                return false;
            }
            if( $data['total_price'] <= 0 )
            { 
                $this->setViewContent( '<p class="badnews">No amount to charge</p>' );
                return false;
            }
            $symbol = Application_Settings_Abstract::getSettings( 'Payments', 'default_currency' ) ? : '$';
            $this->_objectTemplateValues['amount'] = $symbol . $data['total_price'];
        //    var_export( $cart );
    
            //  Output demo content to screen
           

             // end of widget process
          
		}  
		catch( Exception $e )
        { 
            //  Alert! Clear the all other content and display whats below.
        //    $this->setViewContent( self::__( '<p class="badnews">' . $e->getMessage() . '</p>' ) ); 
            $this->setViewContent( self::__( '<p class="badnews">Theres an error in the code</p>' ) ); 
            return false; 
        }
	}
	// END OF CLASS
}
