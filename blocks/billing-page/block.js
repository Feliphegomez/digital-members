/**
 * Block: DmRFID Membership Billing
 *
 * Displays the Membership Billing page and form.
 *
 */

 /**
  * Internal block libraries
  */
 const { __ } = wp.i18n;
 const {
    registerBlockType
} = wp.blocks;

 /**
  * Register block
  */
 export default registerBlockType(
     'dmrfid/billing-page',
     {
         title: __( 'Membership Billing Page', 'paid-memberships-pro' ),
         description: __( 'Displays the member\'s billing information and allows them to update the payment method.', 'paid-memberships-pro' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'list-view',
        },
        keywords: [ __( 'dmrfid', 'paid-memberships-pro' ) ],
        supports: {
        },
        attributes: {
        },
         edit() {
             return [
                 <div className="dmrfid-block-element">
                   <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'paid-memberships-pro' ) }</span>
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Billing Page', 'paid-memberships-pro' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
