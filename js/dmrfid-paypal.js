// Used by plugns that hide/show the billing fields.
dmrfid_require_billing = true;

jQuery(document).ready(function() {
    //choosing payment method
    jQuery('input[name=gateway]').click(function() {
        if(jQuery(this).val() == 'paypal') {
            jQuery('#dmrfid_paypalexpress_checkout').hide();
            jQuery('#dmrfid_billing_address_fields').show();
            jQuery('#dmrfid_payment_information_fields').show();
            jQuery('#dmrfid_submit_span').show();
        } else {
            jQuery('#dmrfid_billing_address_fields').hide();
            jQuery('#dmrfid_payment_information_fields').hide();
            jQuery('#dmrfid_submit_span').hide();
            jQuery('#dmrfid_paypalexpress_checkout').show();
        }
    });

    //select the radio button if the label is clicked on
    jQuery('a.dmrfid_radio').click(function() {
        jQuery(this).prev().click();
    });
});