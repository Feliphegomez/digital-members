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
         title: __( 'Membership Billing Page', 'digital-members-rfid' ),
         description: __( 'Displays the member\'s billing information and allows them to update the payment method.', 'digital-members-rfid' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'list-view',
        },
        keywords: [ __( 'dmrfid', 'digital-members-rfid' ) ],
        supports: {
        },
        attributes: {
        },
         edit() {
             return [
                 <div className="dmrfid-block-element">
                   <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'digital-members-rfid' ) }</span>
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Billing Page', 'digital-members-rfid' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
