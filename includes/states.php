<?php
	//US states for dropdowns, etc
	global $dmrfid_states, $dmrfid_states_abbreviations;

	$dmrfid_states_abbreviations = array('AL','AK','AZ','AR','CA','CO','CT','DE','DC','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY');

	$dmrfid_states_abbreviations = apply_filters("dmrfid_states_abbreviations", $dmrfid_states_abbreviations);

	$dmrfid_states = array('AL'=>"Alabama",
                'AK'=>"Alaska",
                'AZ'=>"Arizona",
                'AR'=>"Arkansas",
                'CA'=>"California",
                'CO'=>"Colorado",
                'CT'=>"Connecticut",
                'DE'=>"Delaware",
                'DC'=>"District Of Columbia",
                'FL'=>"Florida",
                'GA'=>"Georgia",
                'HI'=>"Hawaii",
                'ID'=>"Idaho",
                'IL'=>"Illinois",
                'IN'=>"Indiana",
                'IA'=>"Iowa",
                'KS'=>"Kansas",
                'KY'=>"Kentucky",
                'LA'=>"Louisiana",
                'ME'=>"Maine",
                'MD'=>"Maryland",
                'MA'=>"Massachusetts",
                'MI'=>"Michigan",
                'MN'=>"Minnesota",
                'MS'=>"Mississippi",
                'MO'=>"Missouri",
                'MT'=>"Montana",
                'NE'=>"Nebraska",
                'NV'=>"Nevada",
                'NH'=>"New Hampshire",
                'NJ'=>"New Jersey",
                'NM'=>"New Mexico",
                'NY'=>"New York",
                'NC'=>"North Carolina",
                'ND'=>"North Dakota",
                'OH'=>"Ohio",
                'OK'=>"Oklahoma",
                'OR'=>"Oregon",
                'PA'=>"Pennsylvania",
                'RI'=>"Rhode Island",
                'SC'=>"South Carolina",
                'SD'=>"South Dakota",
                'TN'=>"Tennessee",
                'TX'=>"Texas",
                'UT'=>"Utah",
                'VT'=>"Vermont",
                'VA'=>"Virginia",
                'WA'=>"Washington",
                'WV'=>"West Virginia",
                'WI'=>"Wisconsin",
                'WY'=>"Wyoming");

	$dmrfid_states = apply_filters("dmrfid_states", $dmrfid_states);
?>
