<?php

namespace VNShipping\OptionsResolver;

use VNShipping\Vendor\Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use VNShipping\Vendor\Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

class OptionsResolver extends SymfonyOptionsResolver {
	/**
	 * Defines an option configurator with the given name.
	 *
	 * @param string $option
	 * @return OptionConfigurator
	 */
	public function define( string $option ): OptionConfigurator {
		if ( $this->isDefined( $option ) ) {
			throw new OptionDefinitionException( sprintf( 'The option "%s" is already defined.', $option ) );
		}

		return new OptionConfigurator( $option, $this );
	}
}
