<?php
/**
 * Get the string Yes/No represenation of a boolean value
 *
 * @param val  Boolean value (or integers 0 or 1)
 * @return  String "Yes" or "No"
 */
function convertYesNo($val) {
	if ($val == 0)
		return "No";
	else if ($val == 1)
		return "Yes";
	else
		return "";
}

/**
 * Convert the FLSA value into the format specified by
 * the format parameter
 *
 * @param flsa  string or int representing the pay plan
 * @param format  format tow hich the user would like to
 *		convert the FSLA value
 * @return the converted form of the FLSA value
 */
function convertFLSA($flsa, $format) {

	$convertedFLSA = ''; // Return value

	if ($format == 'numeric') {
		switch ($flsa) {
			case 'N':
			case 'NE':
				$convertedFLSA = 0;
				break;
			case 'X':
			case 'E':
				$convertedFLSA = 1;
				break;
			case '1X N':
			case 'both':
				$convertedFLSA = 2;
				break;
		}
	}
	else if ($format == 'symbolic') {
		switch ($flsa) {
			case 0:
				$convertedFLSA = 'N';
				break;
			case 1:
				$convertedFLSA = 'X';
				break;
			case 2:
				$convertedFLSA = 'both';
				break;
		}
	}
	else if ($format == 'descr') {
		switch ($flsa) {
			case 'N':
			case 'NE':
				$convertedFLSA = 'Non-Exempt';
				break;
			case 'X':
			case 'E':
				$convertedFLSA = 'Exempt';
				break;
			case '1X N':
			case 'both':
				$convertedFLSA = 'Both';
				break;
		}
	}
	return $convertedFLSA;
}

/**
 *	Convert a string representation of money into a float
 *	representation. Remove all characters except decimals
 *	and integers.
 *	
 *	@param money 	String representation of money
 *	@return Float representation of money
 */
function parseMoney($money) {
	return preg_replace("/[^0-9.]/", "", $money);
}

?>
