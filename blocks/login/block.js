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
	title: __("Log in Form", "digital-members-rfid"),
	description: __(
		"Displays a Log In Form for Digital Members RFID.",
		"digital-members-rfid"
	),
	category: "dmrfid",
	icon: {
		background: "#2997c8",
		foreground: "#ffffff",
		src: "unlock",
	},
	keywords: [
		__("dmrfid", "digital-members-rfid"),
		__("login", "digital-members-rfid"),
		__("form", "digital-members-rfid"),
		__("log in", "digital-members-rfid"),
	],
	supports: {},
	edit: (props) => {
		return [
			<Fragment>
				<Inspector {...props} />
				<div className="dmrfid-block-element">
					<span className="dmrfid-block-title">{__("Digital Members RFID", "digital-members-rfid")}</span>
					<span className="dmrfid-block-subtitle">{__("Log in Form", "digital-members-rfid")}</span>
				</div>
			</Fragment>,
		];
	},
	save() {
		return null;
	},
});
