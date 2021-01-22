<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    PageCarton_Table_Sample
 * @copyright  Copyright (c) 2020 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Settings.php Monday 6th of April 2020 04:44PM kayzenk@gmail.com $
 */

/**
 * @see PageCarton_Table
 */


class AuthorizeNet_Settings extends PageCarton_Settings
{

    /**
     * creates the form for creating and editing
     *
     * param string The Value of the Submit Button
     * param string Value of the Legend
     * param array Default Values
     */
	public function createForm( $submitValue = null, $legend = null, Array $values = null )
    {
		if( ! $settings = unserialize( @$values['settings'] ) )
		{
			if( is_array( $values['data'] ) )
			{
				$settings = $values['data'];
			}
			elseif( is_array( $values['settings'] ) )
			{
				$settings = $values['settings'];
			}
			else
			{
				$settings = $values;
			}
		}
        $form = new Ayoola_Form( array( 'name' => $this->getObjectName() ) );
		$form->submitValue = $submitValue ;
		$form->oneFieldSetAtATime = true;
		$fieldset = new Ayoola_Form_Element;



		$fieldset->addElement( array( 'name' => 'mode', 'label' => 'API Mode', 'value' => @$settings['mode'], 'type' => 'Select' ), array( 'Sandbox', 'Production' ) );
		$fieldset->addElement( array( 'name' => 'api_login_id', 'placeholder' => 'e.g. 5KP3u95bQpv' ,  'label' => 'API Login ID', 'value' => @$settings['api_login_id'], 'type' => 'InputText' ) );
		$fieldset->addElement( array( 'name' => 'transaction_key', 'placeholder' => 'e.g. 346HZ32z3fP4hTG2', 'label' => 'AuthorizeNet Transaction Key', 'value' => @$settings['transaction_key'], 'type' => 'InputText' ) );
		$fieldset->addElement( array( 'name' => 'currency', 'placeholder' => 'USD' , 'label' => 'Default currency', 'value' => @$settings['currency'], 'type' => 'InputText' ) );
		$fieldset->addLegend( 'AuthorizeNet Settings' );

		$form->addFieldset( $fieldset );
		$this->setForm( $form );
		//		$form->addFieldset( $fieldset );
	//	$this->setForm( $form );
    }
	// END OF CLASS
}
