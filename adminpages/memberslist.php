<?php

global $user_list_table;
// Query, filter, and sort the data.
$user_list_table = new DmRFID_Members_List_Table();
$user_list_table->prepare_items();
require_once dirname( __DIR__ ) . '/adminpages/admin_header.php';

// Build CSV export link.
$csv_export_link = admin_url( 'admin-ajax.php' ) . '?action=memberslist_csv';
if ( isset( $_REQUEST['s'] ) ) {
	$csv_export_link .= '&s=' . esc_attr( sanitize_text_field( trim( $_REQUEST['s'] ) ) );
}
if ( isset( $_REQUEST['l'] ) ) {
	$csv_export_link .= '&l=' . sanitize_text_field( trim( $_REQUEST['l'] ) );
}

// Render the List Table.
?>
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Members List', 'digital-members-rfid' ); ?></h1>
	<a target="_blank" href="<?php echo esc_url( $csv_export_link ); ?>" class="page-title-action"><?php esc_html_e( 'Export to CSV', 'digital-members-rfid' ); ?></a>
	<hr class="wp-header-end">

	<?php do_action( 'dmrfid_memberslist_before_table' ); ?>			
	<form id="member-list-form" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
		<?php
			$user_list_table->search_box( __( 'Search Members', 'digital-members-rfid' ), 'digital-members-rfid' );
			$user_list_table->display();
		?>
	</form>

	<?php if ( ! function_exists( 'dmrfidrh_add_registration_field' ) ) {
		$allowed_dmrfidrh_html = array (
			'a' => array (
				'href' => array(),
				'target' => array(),
				'title' => array(),
			),
		);
		echo '<p class="description">' . sprintf( wp_kses( __( 'Optional: Capture additional member profile fields using the <a href="%s" title="Digital Members RFID - Register Helper Add On" target="_blank">Register Helper Add On</a>.', 'digital-members-rfid' ), $allowed_dmrfidrh_html ), 'https://www.paidmembershipspro.com/add-ons/dmrfid-register-helper-add-checkout-and-profile-fields/?utm_source=plugin&utm_medium=dmrfid-memberslist&utm_campaign=add-ons&utm_content=dmrfid-register-helper-add-checkout-and-profile-fields' ) . '</p>';
	} ?>
	
<?php
	require_once dirname( __DIR__ ) . '/adminpages/admin_footer.php';
?>
