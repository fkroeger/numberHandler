<?php
class NumberHandler {

	const STANDARD_CURRENCY_ROUNDING = -2;
	const NO_ROUNDING = -1;
	
	const AUTO_DETECT_FORMAT = 1;
	const FORMAT_DE = 2;
	const FORMAT_EN = 3;
	
	const CUR_EUR = 1;
	const CUR_USD = 2;
	
	private $inputFormat;
	private $detectedFormat;
	private $rounding;
	private $currency;	
	
	/**
	 * Constructor
	 *
	 * @param  integer $format format of the passed value in convert function.
	 * @param  integer $rounding decimal precision for rounding
	 * @param  integer $currency currency type
	 * @return object.
	*/
	function __construct($format = self::AUTO_DETECT_FORMAT, $rounding = self::STANDARD_CURRENCY_ROUNDING, $currency = self::CUR_EUR) {
		$this->inputFormat = $this->detectedFormat = $format;
		$this->rounding = $rounding;
		$this->currency = $currency;
	}
	
	/* public methods */
	
	/**
	 * Converting input value to float
	 *
	 * Taking float, integer or string value converting
	 * it into a valid float value.
	 *
	 * @param  float/integer/string $value value to convert.
	 * @return float converted value.
	*/
	public function convertToFloat($value) {
		if (is_float($value)) {
			return $value;
		} else if (is_integer($value)) {
			return (float)$value;
		} else {
			switch($this->detectedFormat) {
				case self::AUTO_DETECT_FORMAT:
					$this->detectedFormat = $this->detectFormat($value);
					return $this->convertToFloat($value);
				break;
				case self::FORMAT_EN:
					return (float)preg_replace(array('/[,]/', '/[^-0-9\.]/'), '', $value);
				break;
				case self::FORMAT_DE:
					return (float)preg_replace(array('/[^-0-9\,]/', '/[,]/'), array('', '.'), $value);
				break;
				default:
					throw new Exception('Undefined format passed');
			}
		}
	}
	
	/**
	 * Formatting float value to currency.
	 *
	 * Taking any float value and adds thousands-
	 * and decimal separators to the value.
	 * Also adds the currency sign.
	 *
	 * @param  float $value value to format.
	 * @return string formatted value.
	*/
	public function convertToCurrency($value) {
		$value = $this->convertToFloat($value);
		
		if ($this->rounding > self::NO_ROUNDING) {
			$value = $this->roundValue($value);
		}
		
		$separators = $this->mapCurrencyToSeparator($this->currency);
		$formattedNumber = number_format($value, $this->mapCurrencyToRounding($this->currency), $separators['decimals'], $separators['thousands']);
		
		return $formattedNumber.$this->mapCurrencyToSign($this->currency);
	}
	
	/**
	 * Rounding any value to float value.
	 *
	 * Taking any float, integer or string value
	 * and converts it into a valid float and then
	 * rounds it.
	 *
	 * @param  float/integer/string $value value to convert and round.
	 * @return float converted and rounded value.
	*/
	public function roundValue($value) {
		$value = $this->convertToFloat($value);
		
		if ($this->rounding > self::NO_ROUNDING) {
			return round($value, $this->rounding);
		}
		
		return $value;
	}
	
	/* getter and setter */
	public function setFormat($format) {
		$this->format = $this->detectedFormat = $format;
	}
	
	public function getDetectedFormat() {
		return $this->detectedFormat;
	}
	
	public function setRounding($rounding) {
		$this->rounding = $rounding;
	}
	
	public function getRounding() {
		return $this->rounding;
	}
	
	public function setCurrency($currency) {
		$this->currency = $currency;
	}
	
	public function getCurrency() {
		return $this->currency;
	}
	
	/* private methods */
	private function detectFormat($value) {
		//e.g. 1.234.567 or 1.234 or 1.234,50 but not 1.23
		if (preg_match('/^[\d]([.][\d]{3})+([,][\d]+)?$/', $value)) {
			return self::FORMAT_DE;
		}
		
		//one comma
		if (preg_match('/^[^,]+[,][^,]+$/', $value)) {
			//comma is not decimal separator
			if (preg_match('/^[\d]+[,]([\d]{3})+$/', $value)) {
				return self::FORMAT_EN;
			}
			return self::FORMAT_DE;
		}
	
		return self::FORMAT_EN;
	}
	
	private function mapCurrencyToRounding($currency) {
		switch ($currency) {
			case self::CUR_EUR:
				return 2;
			break;
			case self::CUR_USD:
				return 2;
			break;
			default:
				return 2;
		}
	}
	
	private function mapCurrencyToSign($currency) {
		switch ($currency) {
			case self::CUR_EUR:
				return '€';
			break;
			case self::CUR_USD:
				return '$';
			break;
			default:
				return '€';
		}
	}
	
	private function mapCurrencyToSeparator($currency) {
		switch ($currency) {
			case self::CUR_EUR:
				return array( 'thousands' => '.', 'decimals' => ',');
			break;
			case self::CUR_USD:
				return array( 'thousands' => ',', 'decimals' => '.');
			break;
			default:
				return array( 'thousands' => '.', 'decimals' => ',');
		}
	}
}
?>
