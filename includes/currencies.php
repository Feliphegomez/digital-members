<?php
	global $dmrfid_currencies, $dmrfid_default_currency;
	
	$dmrfid_default_currency = apply_filters("dmrfid_default_currency", "USD");
	
	$dmrfid_currencies = array( 
		'COP' => __('Peso Colombiano (&#36;)', 'digital-members-rfid' ),
		'USD' => __('US Dollars (&#36;)', 'digital-members-rfid' ),
		'EUR' => array(
			'name' => __('Euros (&euro;)', 'digital-members-rfid' ),
			'symbol' => '&euro;',
			'position' => apply_filters("dmrfid_euro_position", dmrfid_euro_position_from_locale())
			),				
		'GBP' => array(
			'name' => __('Pounds Sterling (&pound;)', 'digital-members-rfid' ),
			'symbol' => '&pound;',
			'position' => 'left'
			),
		'ARS' => __('Argentine Peso (&#36;)', 'digital-members-rfid' ),
		'AUD' => __('Australian Dollars (&#36;)', 'digital-members-rfid' ),
		'BRL' => array(
			'name' => __('Brazilian Real (R&#36;)', 'digital-members-rfid' ),
			'symbol' => 'R&#36;',
			'position' => 'left'
			),
		'CAD' => __('Canadian Dollars (&#36;)', 'digital-members-rfid' ),
		'CNY' => __('Chinese Yuan', 'digital-members-rfid' ),
		'CZK' => array(
			'name' => __('Czech Koruna', 'digital-members-rfid' ),
	    			'decimals' => '0',
	    			'thousands_separator' => '&nbsp;',
	    			'decimal_separator' => ',',
	    			'symbol' => '&nbsp;Kč',
	    			'position' => 'right',
			),
		'DKK' => array(
			'name' =>__('Danish Krone', 'digital-members-rfid' ),
			'decimals' => '2',
			'thousands_separator' => '&nbsp;',
			'decimal_separator' => ',',
			'symbol' => 'DKK&nbsp;',
			'position' => 'left',
			),
		'GHS' => array(
			'name' => __('Ghanaian Cedi (&#8373;)', 'digital-members-rfid' ),
			'symbol' => '&#8373;',
			'position' => 'left',
			),
		'HKD' => __('Hong Kong Dollar (&#36;)', 'digital-members-rfid' ),
		'HUF' => __('Hungarian Forint', 'digital-members-rfid' ),
		'INR' => __('Indian Rupee', 'digital-members-rfid' ),
		'IDR' => __('Indonesia Rupiah', 'digital-members-rfid' ),
		'ILS' => __('Israeli Shekel', 'digital-members-rfid' ),
		'JPY' => array(
			'name' => __('Japanese Yen (&yen;)', 'digital-members-rfid' ),
			'symbol' => '&yen;',
			'position' => 'left',
			'decimals' => 0,
			),
		'KES' => __('Kenyan Shilling', 'digital-members-rfid' ),
		'MYR' => __('Malaysian Ringgits', 'digital-members-rfid' ),
		'MXN' => __('Mexican Peso (&#36;)', 'digital-members-rfid' ),
		'NGN' => __('Nigerian Naira (&#8358;)', 'digital-members-rfid' ),
		'NZD' => __('New Zealand Dollar (&#36;)', 'digital-members-rfid' ),
		'NOK' => __('Norwegian Krone', 'digital-members-rfid' ),
		'PHP' => __('Philippine Pesos', 'digital-members-rfid' ),
		'PLN' => __('Polish Zloty', 'digital-members-rfid' ),
		'RON' => array(	
				'name' => __( 'Romanian Leu', 'digital-members-rfid' ),
				'decimals' => '2',
				'thousands_separator' => '.',
				'decimal_separator' => ',',
				'symbol' => '&nbsp;Lei',
				'position' => 'right'
		),
		'RUB' => array(
			'name' => __('Russian Ruble (&#8381;)', 'digital-members-rfid'),
			'decimals' => '2',
			'thousands_separator' => '&nbsp;',
			'decimal_separator' => ',',
			'symbol' => '&#8381;',
			'position' => 'right'
		),
		'SGD' => array(
			'name' => __('Singapore Dollar (&#36;)', 'digital-members-rfid' ),
			'symbol' => '&#36;',
			'position' => 'right'
			),
		'ZAR' => array(
			'name' => __('South African Rand (R)', 'digital-members-rfid' ),
			'symbol' => 'R ',
			'position' => 'left'
		),			
		'KRW' => array(
			'name' => __('South Korean Won', 'digital-members-rfid' ),
			'decimals' => 0,
			),
		'SEK' => __('Swedish Krona', 'digital-members-rfid' ),
		'CHF' => __('Swiss Franc', 'digital-members-rfid' ),
		'TWD' => __('Taiwan New Dollars', 'digital-members-rfid' ),
		'THB' => __('Thai Baht', 'digital-members-rfid' ),
		'TRY' => __('Turkish Lira', 'digital-members-rfid' ),
		'VND' => array(
			'name' => __('Vietnamese Dong', 'digital-members-rfid' ),
			'decimals' => 0,
			),
		);
	
	$dmrfid_currencies = apply_filters("dmrfid_currencies", $dmrfid_currencies);
	
	//stripe only supports a few (not using this anymore since 1.7.4)
	global $dmrfid_stripe_currencies;
	$dmrfid_stripe_currencies = array(
			'COP' => __('Peso Colombiano (&#36;)', 'digital-members-rfid' ),			
			'USD' => __('US Dollars (&#36;)', 'digital-members-rfid' ),			
			'CAD' => __('Canadian Dollars (&#36;)', 'digital-members-rfid' ),
			'GBP' => __('Pounds Sterling (&pound;)', 'digital-members-rfid' ),
			'EUR' => __('Euros (&euro;)', 'digital-members-rfid' )
	);
	
	/**
	 * Get the Euro position based on locale.
	 * English uses left, others use right.
	 */
	function dmrfid_euro_position_from_locale($position = 'right') {
		$locale = get_locale();
		if(strpos($locale, 'en_') === 0) {
			$position = 'left';
		}
		return $position;
	}
	
	/**
	 * Get an array of data for a specified currency.
	 * Defaults to the current currency set in the global.
	 */
	function dmrfid_get_currency( $currency = null ) {
		global $dmrfid_currency, $dmrfid_currencies;
		
		// Defaults
		$currency_array = array(
			'name' =>__('Peso Colombiano (&#36;)', 'digital-members-rfid' ),
			'decimals' => '0',
			'thousands_separator' => ',',
			'decimal_separator' => '.',
			'symbol' => '&#36;',
			'position' => 'left',
		);
		
		if ( ! empty( $dmrfid_currency ) ) {
			if ( is_array( $dmrfid_currencies[$dmrfid_currency] ) ) {
				$currency_array = array_merge( $currency_array, $dmrfid_currencies[$dmrfid_currency] );
			} else {
				$currency_array['name'] = $dmrfid_currencies[$dmrfid_currency];
			}
		}
		
		return $currency_array;
	}
