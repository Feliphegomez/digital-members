var dmrfid_require_billing;

// Wire up the form for Stripe.
jQuery( document ).ready( function( $ ) {

	var stripe, elements, cardNumber, cardExpiry, cardCvc;

	// Identify with Stripe.
	stripe = Stripe( dmrfidStripe.publishableKey );
	elements = stripe.elements();

	// Create Elements.
	cardNumber = elements.create('cardNumber');
	cardExpiry = elements.create('cardExpiry');
	cardCvc = elements.create('cardCvc');

	// Mount Elements. Ensure CC field is present before loading Stripe.
	if ( $( '#AccountNumber' ).length > 0 ) { 
		cardNumber.mount('#AccountNumber');
	}
	if ( $( '#Expiry' ).length > 0 ) { 
		cardExpiry.mount('#Expiry');
	}
	if ( $( '#CVV' ).length > 0 ) { 
		cardCvc.mount('#CVV');
	}
	
	// Handle authentication for charge if required.
	if ( 'undefined' !== typeof( dmrfidStripe.paymentIntent ) ) {
		if ( 'requires_action' === dmrfidStripe.paymentIntent.status ) {
			// On submit disable its submit button
			$('input[type=submit]', this).attr('disabled', 'disabled');
			$('input[type=image]', this).attr('disabled', 'disabled');
			$('#dmrfid_processing_message').css('visibility', 'visible');
			stripe.handleCardAction( dmrfidStripe.paymentIntent.client_secret )
				.then( stripeResponseHandler );
		}
	}
	
	// Handle authentication for subscription if required.
	if ( 'undefined' !== typeof( dmrfidStripe.setupIntent ) ) {
		if ( 'requires_action' === dmrfidStripe.setupIntent.status ) {
			// On submit disable its submit button
			$('input[type=submit]', this).attr('disabled', 'disabled');
			$('input[type=image]', this).attr('disabled', 'disabled');
			$('#dmrfid_processing_message').css('visibility', 'visible');
			stripe.handleCardSetup( dmrfidStripe.setupIntent.client_secret )
				.then( stripeResponseHandler );
		}
	}

	// Set require billing var if not set yet.
	if ( typeof dmrfid_require_billing === 'undefined' ) {
		dmrfid_require_billing = dmrfidStripe.dmrfid_require_billing;
	}

	$( '.dmrfid_form' ).submit( function( event ) {
		var name, address;

		// Prevent the form from submitting with the default action.
		event.preventDefault();

		// Double check in case a discount code made the level free.
		if ( typeof dmrfid_require_billing === 'undefined' || dmrfid_require_billing ) {

			if ( dmrfidStripe.verifyAddress ) {
				address = {
					line1: $( '#baddress1' ).val(),
					line2: $( '#baddress2' ).val(),
					city: $( '#bcity' ).val(),
					state: $( '#bstate' ).val(),
					postal_code: $( '#bzipcode' ).val(),
					country: $( '#bcountry' ).val(),
				}
			}

			//add first and last name if not blank
			if ( $( '#bfirstname' ).length && $( '#blastname' ).length ) {
				name = $.trim( $( '#bfirstname' ).val() + ' ' + $( '#blastname' ).val() );
			}
			
			stripe.createPaymentMethod( 'card', cardNumber, {
				billing_details: {
					address: address,
					name: name,
				}
			}).then( stripeResponseHandler );

			// Prevent the form from submitting with the default action.
			return false;
		} else {
			this.submit();
			return true;	//not using Stripe anymore
		}
	});

	// Check if Payment Request Button is enabled.
	if ( $('#payment-request-button').length ) {
		var paymentRequest = null;

		// Create payment request
		jQuery.noConflict().ajax({
			url: dmrfidStripe.restUrl + 'dmrfid/v1/checkout_levels',
			dataType: 'json',
			data: jQuery( "#dmrfid_form" ).serialize(),
			success: function(data) {
				if ( data.hasOwnProperty('initial_payment') ) {
					paymentRequest = stripe.paymentRequest({
						country: 'US',
						currency: 'usd',
						total: {
							label: dmrfidStripe.siteName,
							amount: data.initial_payment * 100,
						},
						requestPayerName: true,
						requestPayerEmail: true,
					});
					var prButton = elements.create('paymentRequestButton', {
						paymentRequest: paymentRequest,
					});
					// Mount payment request button.
					paymentRequest.canMakePayment().then(function(result) {
					if (result) {
						prButton.mount('#payment-request-button');
					} else {
						$('#payment-request-button').hide();
					}
					});
					// Handle payment request button confirmation.
					paymentRequest.on('paymentmethod', function( event ) {
						stripeResponseHandler( event );
					});
				}
			}
		});

		function stripeUpdatePaymentRequestButton() {
			jQuery.noConflict().ajax({
				url: dmrfidStripe.restUrl + 'dmrfid/v1/checkout_levels',
				dataType: 'json',
				data: jQuery( "#dmrfid_form" ).serialize(),
				success: function(data) {
					if ( data.hasOwnProperty('initial_payment') ) {
						paymentRequest.update({
							total: {
								label: dmrfidStripe.siteName,
								amount: data.initial_payment * 100,
							},
						});
					}
				}
			});
		}

		if ( dmrfidStripe.updatePaymentRequestButton ) {
			$(".dmrfid_alter_price").change(function(){
				stripeUpdatePaymentRequestButton();
			});
		}
	}

	// Handle the response from Stripe.
	function stripeResponseHandler( response ) {

		var form, data, card, paymentMethodId, customerId;

		form = $('#dmrfid_form, .dmrfid_form');

		if (response.error) {

			// Re-enable the submit button.
			$('.dmrfid_btn-submit-checkout,.dmrfid_btn-submit').removeAttr('disabled');

			// Hide processing message.
			$('#dmrfid_processing_message').css('visibility', 'hidden');

			// error message
			$( '#dmrfid_message' ).text( response.error.message ).addClass( 'dmrfid_error' ).removeClass( 'dmrfid_alert' ).removeClass( 'dmrfid_success' ).show();
			
		} else if ( response.paymentMethod ) {			
			
			paymentMethodId = response.paymentMethod.id;
			card = response.paymentMethod.card;			
			
			// Insert the Source ID into the form so it gets submitted to the server.
			form.append( '<input type="hidden" name="payment_method_id" value="' + paymentMethodId + '" />' );

			// We need this for now to make sure user meta gets updated.
			// Insert fields for other card fields.
			if( $( '#CardType[name=CardType]' ).length ) {
				$( '#CardType' ).val( card.brand );
			} else {
				form.append( '<input type="hidden" name="CardType" value="' + card.brand + '"/>' );
			}
			
			form.append( '<input type="hidden" name="AccountNumber" value="XXXXXXXXXXXX' + card.last4 + '"/>' );
			form.append( '<input type="hidden" name="ExpirationMonth" value="' + ( '0' + card.exp_month ).slice( -2 ) + '"/>' );
			form.append( '<input type="hidden" name="ExpirationYear" value="' + card.exp_year + '"/>' );

			// and submit
			form.get(0).submit();			
			
		} else if ( response.paymentIntent || response.setupIntent ) {
			
			// success message
			$( '#dmrfid_message' ).text( dmrfidStripe.msgAuthenticationValidated ).addClass( 'dmrfid_success' ).removeClass( 'dmrfid_alert' ).removeClass( 'dmrfid_error' ).show();
			
			customerId = dmrfidStripe.paymentIntent 
				? dmrfidStripe.paymentIntent.customer
				: dmrfidStripe.setupIntent.customer;
			
			paymentMethodId = dmrfidStripe.paymentIntent
				? dmrfidStripe.paymentIntent.payment_method.id
				: dmrfidStripe.setupIntent.payment_method.id;
				
			card = dmrfidStripe.paymentIntent
				? dmrfidStripe.paymentIntent.payment_method.card
				: dmrfidStripe.setupIntent.payment_method.card;

		    	if ( dmrfidStripe.paymentIntent ) {
				form.append( '<input type="hidden" name="payment_intent_id" value="' + dmrfidStripe.paymentIntent.id + '" />' );
			}
			if ( dmrfidStripe.setupIntent ) {
				form.append( '<input type="hidden" name="setup_intent_id" value="' + dmrfidStripe.setupIntent.id + '" />' );
				form.append( '<input type="hidden" name="subscription_id" value="' + dmrfidStripe.subscription.id + '" />' );
			}

			// Insert the Customer ID into the form so it gets submitted to the server.
			form.append( '<input type="hidden" name="customer_id" value="' + customerId + '" />' );

			// Insert the PaymentMethod ID into the form so it gets submitted to the server.
			form.append( '<input type="hidden" name="payment_method_id" value="' + paymentMethodId + '" />' );

			// We need this for now to make sure user meta gets updated.
			// Insert fields for other card fields.
			if( $( '#CardType[name=CardType]' ).length ) {
				$( '#CardType' ).val( card.brand );
			} else {
				form.append( '<input type="hidden" name="CardType" value="' + card.brand + '"/>' );
			}

			form.append( '<input type="hidden" name="AccountNumber" value="XXXXXXXXXXXX' + card.last4 + '"/>' );
			form.append( '<input type="hidden" name="ExpirationMonth" value="' + ( '0' + card.exp_month ).slice( -2 ) + '"/>' );
			form.append( '<input type="hidden" name="ExpirationYear" value="' + card.exp_year + '"/>' );
			form.get(0).submit();
			return true;
		}
	}
});
