<?php
/**
 * The Memberships Reports admin page for Digital Members RFID
 */

global $dmrfid_reports;

/**
* Load the Digital Members RFID dashboard-area header
*/
require_once( dirname( __FILE__ ) . '/admin_header.php' );

// View a single report if requested.
if ( ! empty( $_REQUEST[ 'report' ] ) ) {
	//view a single report
	$report = sanitize_text_field( $_REQUEST[ 'report' ] );
	call_user_func( 'dmrfid_report_' . $report . '_page' ); ?>
	<hr />
	<a class="button button-primary" href="<?php echo admin_url("admin.php?page=dmrfid-reports");?>"><?php _e( 'Back to Reports Dashboard', 'digital-members-rfid' ); ?></a>
	<?php
} else {
	$pieces = array_chunk( $dmrfid_reports, ceil( count( $dmrfid_reports ) / 2 ), true );
	foreach ( $pieces[0] as $report => $title ) {
		add_meta_box(
			'dmrfid_report_' . $report,
			$title,
			'dmrfid_report_' . $report . '_widget',
			'memberships_page_dmrfid-reports',
			'advanced'
		);
	}
	
	foreach ( $pieces[1] as $report => $title ) {
		add_meta_box(
			'dmrfid_report_' . $report,
			$title,
			'dmrfid_report_' . $report . '_widget',
			'memberships_page_dmrfid-reports',
			'side'
		);
	}
	
	?>
	<form id="dmrfid-reports-form" method="post" action="admin-post.php">

		<div class="dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">

				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes( 'memberships_page_dmrfid-reports', 'advanced', '' ); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<?php do_meta_boxes( 'memberships_page_dmrfid-reports', 'side', '' ); ?>
				</div>

				<br class="clear">

			</div> <!-- end dashboard-widgets -->

			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>

		</div> <!-- end dashboard-widgets-wrap -->
	</form>
	<script type="text/javascript">
	  //<![CDATA[
	  jQuery(document).ready( function($) {
		  // close postboxes that should be closed
		  $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		  // postboxes setup
		  postboxes.add_postbox_toggles('memberships_page_dmrfid-reports');
	  });
	  //]]>
	</script>

	<?php
}

/**
* Load the Digital Members RFID dashboard-area footer
*/
require_once(dirname(__FILE__) . "/admin_footer.php");