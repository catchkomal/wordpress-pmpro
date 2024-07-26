<?php
/*
Plugin Name: PMPro Customizations
Plugin URI: https://www.paidmembershipspro.com/wp/pmpro-customizations/
Description: Customizations for my Paid Memberships Pro Setup
Version: .1
Author: Paid Memberships Pro
Author URI: https://www.paidmembershipspro.com
*/

/**
 * This recipe will add a Restrict field to each discount code. You can then restrict the use of a discount code
 * when a user is logged in. 
 * 
 */

 function mypmpro_discount_code_restrict_field( $edit ){

	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top">
					<label for="uses">Approved Email Domain</label>
				</th>
				<td>
					<textarea name='discount_restrictions' style='width: 30%;' rows='4'><?php echo str_replace( "<br />", "", get_option( 'discount_code_restriction_'.$edit ) ); ?></textarea><br/>
					<small>One email address per line.</small>
				</td>
			</tr>
		</tbody>
	</table>
	<?php

}
add_action( 'pmpro_discount_code_after_settings', 'mypmpro_discount_code_restrict_field', 10, 1 );

function mypmpro_save_discount_code_restrict(){

	if( isset( $_REQUEST['discount_restrictions'] ) ){

		$save_id = intval( $_REQUEST['saveid'] );
		$description = nl2br( $_REQUEST['discount_restrictions'] );

		update_option( 'discount_code_restriction_'.$save_id, $description );

	}

}
add_action( 'admin_init', 'mypmpro_save_discount_code_restrict' );

/* add filter to check email domain of member is restricted or not */
function mypmpro_validate_discount_code_use_restrict( $okay, $dbcode, $level_id, $code ){

	$discount = new PMPro_Discount_Code( $code );
	
	$restrict = get_option( 'discount_code_restriction_'.$discount->id );	

	$restrictions = strip_tags( $restrict );

	$restrictions = str_replace( "\n", ",", $restrictions );

	if( $restrictions !== "" ){
		$allemails = explode( ",", $restrictions );
		$email = array_map('trim', $allemails);
		$emails = array_map('strtolower', $email);
		global $current_user; 
		if($current_user->ID || $_REQUEST['bemail']){
			$email_rest =  explode('@',$current_user->data->user_email);
			$bemail_rest =  explode('@',$_REQUEST['bemail']);
			
			if( in_array( trim(strtolower($email_rest[1])), $emails ) || in_array( trim(strtolower($bemail_rest[1])), $emails )){
				return true;
			} else {
				return 'The email used is not valid with this corporate membership.';
			}
		} 
		
	}

	return $okay;

}
add_filter( 'pmpro_check_discount_code', 'mypmpro_validate_discount_code_use_restrict', 10, 4 );