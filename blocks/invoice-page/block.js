/**
 * Block: DmRFID Membership Invoices
 *
 * Displays the Membership Invoices template.
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
     'dmrfid/invoice-page',
     {
         title: __( 'Membership Invoice Page', 'paid-memberships-pro' ),
         description: __( 'Displays the member\'s  Membership Invoices.', 'paid-memberships-pro' ),
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
         edit(){
             return [
                 <div className="dmrfid-block-element">
                   <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'paid-memberships-pro' ) }</span>
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Invoices', 'paid-memberships-pro' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
