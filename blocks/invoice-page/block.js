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
         title: __( 'Membership Invoice Page', 'digital-members-rfid' ),
         description: __( 'Displays the member\'s  Membership Invoices.', 'digital-members-rfid' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'archive',
         },
         keywords: [ __( 'dmrfid', 'digital-members-rfid' ) ],
         supports: {
         },
         attributes: {
         },
         edit(){
             return [
                 <div className="dmrfid-block-element">
                   <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'digital-members-rfid' ) }</span>
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Invoices', 'digital-members-rfid' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
