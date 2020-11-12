/**
 * Block: DmRFID Login Form
 *
 * Add a login form to any page or post.
 *
 */

/**
 * Block dependencies
 */
import Inspector from "./inspector";

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
/**
 * Register block
 */
export default registerBlockType("dmrfid/login-form", {
	title: __("Log in Form", "paid-memberships-pro"),
	description: __(
		"Displays a Log In Form for Digital Members RFID.",
		"paid-memberships-pro"
	),
	category: "dmrfid",
	icon: {
		background: "#2997c8",
		foreground: "#ffffff",
		src: "unlock",
	},
	keywords: [
		__("dmrfid", "paid-memberships-pro"),
		__("login", "paid-memberships-pro"),
		__("form", "paid-memberships-pro"),
		__("log in", "paid-memberships-pro"),
	],
	supports: {},
	edit: (props) => {
		return [
			<Fragment>
				<Inspector {...props} />
				<div className="dmrfid-block-element">
					<span className="dmrfid-block-title">{__("Digital Members RFID", "paid-memberships-pro")}</span>
					<span className="dmrfid-block-subtitle">{__("Log in Form", "paid-memberships-pro")}</span>
				</div>
			</Fragment>,
		];
	},
	save() {
		return null;
	},
});
