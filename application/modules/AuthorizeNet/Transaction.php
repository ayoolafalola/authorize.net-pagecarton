<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    AuthorizeNet_Transaction
 * @copyright  Copyright (c) 2021 PageCarton (http://www.pagecarton.org)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Transaction.php Friday 22nd of January 2021 01:19PM ayoola@ayoo.la $
 */

/**
 * @see PageCarton_Table
 */


class AuthorizeNet_Transaction extends PageCarton_Table
{

    /**
     * The table version (SVN COMPATIBLE)
     *
     * @param string
     */
    protected $_tableVersion = '0.2';  

    /**
     * Table data types and declaration
     * array( 'fieldname' => 'DATATYPE' )
     *
     * @param array
     */
	protected $_dataTypes = array (
  'user_id' => 'INPUTTEXT',
  'order_id' => 'INPUTTEXT',
  'email' => 'INPUTTEXT',
  'amount' => 'INPUTTEXT',
  'response_code' => 'INPUTTEXT',
  'response' => 'JSON',
  'authorize.net_trans_id' => 'INPUTTEXT',
);


	// END OF CLASS
}
