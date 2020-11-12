<?php
/**
 * Template for Print Invoices
 *
 * @since 1.8.6
 */
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<style>
		.main, .header {
			display: block;
		}
		.right {
			display: inline-block;
			float: right;
		}
		.alignright {
			text-align: right;
		}
		.aligncenter {
			text-align: center;
		}
		.invoice, .invoice tr, .invoice th, .invoice td {
			border: 1px solid;
			border-collapse: collapse;
			padding: 4px;
		}
		.invoice {
			width: 100%;
		}
		@media screen {
			body {
				max-width: 50%;
				margin: 0 auto;
			}
		}
	</style>
</head>
<body>
	<header class="header">
		<div>
			<h2><?php bloginfo( 'sitename' ); ?></h2>
		</div>
		<div class="right">
			<table>
				<tr>
					<td><?php echo __('Invoice #: ', 'digital-members-rfid' ) . '&nbsp;' . $order->code; ?></td>
				</tr>
				<tr>
					<td>
						<?php echo __( 'Date:', 'digital-members-rfid' ) . '&nbsp;' . date_i18n( get_option( 'date_format' ), $order->getTimestamp() ); ?>
					</td>
				</tr>
			</table>
		</div>
	</header>
	<main class="main">
		<p>
			<?php echo dmrfid_formatAddress(
				$order->billing->name,
				$order->billing->street,
				'',
				$order->billing->city,
				$order->billing->state,
				$order->billing->zip,
				$order->billing->country,
				$order->billing->phone
			); ?>
		</p>
		<table class="invoice">
			<tr>
				<th><?php _e('ID', 'digital-members-rfid' ); ?></th>
				<th><?php _e('Item', 'digital-members-rfid' ); ?></th>
				<th><?php _e('Price', 'digital-members-rfid' ); ?></th>
			</tr>
			<tr>
				<td class="aligncenter"><?php echo $level->id; ?></td>
				<td><?php echo $level->name; ?></td>
				<td class="alignright"><?php echo $order->subtotal; ?></td>
			</tr>
			<tr>
				<th colspan="2" class="alignright"><?php _e('Subtotal', 'digital-members-rfid' ); ?></th>
				<td class="alignright"><?php echo $order->subtotal; ?></td>
			</tr>
			<tr>
				<th colspan="2" class="alignright"><?php _e('Tax', 'digital-members-rfid' ); ?></th>
				<td class="alignright"><?php echo $order->tax; ?></td>
			</tr>
			<tr>
				<th colspan="2" class="alignright"><?php _e('Total', 'digital-members-rfid' ); ?></th>
				<th class="alignright"><?php echo dmrfid_formatPrice( $order->total ); ?></th>
			</tr>
		</table>
	</main>
</body>
</html>
