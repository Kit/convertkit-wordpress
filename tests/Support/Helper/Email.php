<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to email addresses,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class Email extends \Codeception\Module
{
	/**
	 * Generates a unique email address for use in a test, comprising of a prefix,
	 * date + time and PHP version number.
	 *
	 * This ensures that if tests are run in parallel, the same email address
	 * isn't used for two tests across parallel testing runs.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   string $domain     Domain (default: kit.com).
	 */
	public function generateEmailAddress($domain = 'kit.com')
	{
		$date = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
		return 'wordpress-' . $date->format('Y-m-d-H-i-s-u') . '-php-' . PHP_VERSION_ID . '@' . $domain;
	}
}
