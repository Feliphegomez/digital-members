jQuery(document).ready(function() {
    //set up braintree encryption
    var braintree = Braintree.create( dmrfid_braintree.encryptionkey );
    braintree.onSubmitEncryptForm('dmrfid_form');

    //pass expiration dates in original format
    function dmrfid_updateBraintreeCardExp()
    {
        jQuery('#credit_card_exp').val(jQuery('#ExpirationMonth').val() + "/" + jQuery('#ExpirationYear').val());
    }
    jQuery('#ExpirationMonth, #ExpirationYear').change(function() {
        dmrfid_updateBraintreeCardExp();
    });
    dmrfid_updateBraintreeCardExp();

    //pass last 4 of credit card
    function dmrfid_updateBraintreeAccountNumber()
    {
        jQuery('#BraintreeAccountNumber').val('XXXXXXXXXXXXX' + jQuery('#AccountNumber').val().substr(jQuery('#AccountNumber').val().length - 4));
    }
    jQuery('#AccountNumber').change(function() {
        dmrfid_updateBraintreeAccountNumber();
    });
    dmrfid_updateBraintreeAccountNumber();
});