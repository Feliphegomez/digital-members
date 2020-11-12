/**
 * Block: DmRFID Membership Account
 *
 * Displays the Membership Account page.
 *
 */
 /**
  * Block dependencies
  */
 import Inspector from './inspector';
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
     'dmrfid/account-page',
     {
         title: __( 'Membership Account Page', 'paid-memberships-pro' ),
         description: __( 'Displays the sections of the Membership Account page as selected below.', 'paid-memberships-pro' ),
         category: 'dmrfid',
         icon: {
            background: '#2997c8',
            foreground: '#ffffff',
            src: 'admin-users',
         },
         keywords: [ __( 'dmrfid', 'paid-memberships-pro' ) ],
         supports: {
         },
         attributes: {
             membership: {
                 type: 'boolean',
                 default: false,
             },
             profile: {
                 type: 'boolean',
                 default: false,
             },
             invoices: {
                 type: 'boolean',
                 default: false,
             },
             links: {
                 type: 'boolean',
                 default: false,
             },
         },
         edit: props => {
             const { setAttributes, isSelected } = props;
             return [
                isSelected && <Inspector { ...{ setAttributes, ...props} } />,
                <div className="dmrfid-block-element">
                  <span className="dmrfid-block-title">{ __( 'Digital Members RFID', 'paid-memberships-pro' ) }</span>
                  <span className="dmrfid-block-subtitle">{ __( 'Membership Account Page', 'paid-memberships-pro' ) }</span>
                </div>
            ];
         },
         save() {
           return null;
         },
       }
 );
