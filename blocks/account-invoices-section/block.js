/**
 * Block: DmRFID Membership Account: Invoices
 *
 * Displays the Membership Account > Invoices page section.
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
     'dmrfid/account-invoices-section',
     {
         title: __( 'Membership Account: Invoices', 'paid-memberships-pro' ),
         description: __( 'Displays the member\'s invoices.', 'paid-memberships-pro' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'archive',
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
                  <span className="dmrfid-block-subtitle"> { __( 'Membership Account: Invoices', 'paid-memberships-pro' ) }</span>
                </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
