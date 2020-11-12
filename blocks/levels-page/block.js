/**
 * Block: DmRFID Membership Levels
 *
 * Displays the Membership Levels template.
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
     'dmrfid/levels-page',
     {
         title: __( 'Membership Levels List', 'digital-members-rfid' ),
         description: __( 'Displays a list of Membership Levels. To change the order, go to Memberships > Settings > Levels.', 'digital-members-rfid' ),
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
                   <span className="dmrfid-block-subtitle">{ __( 'Membership Levels List', 'digital-members-rfid' ) }</span>
                 </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
