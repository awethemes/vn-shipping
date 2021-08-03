<?php

namespace VNShipping\OptionsResolver;

use Closure;
use VNShipping\Vendor\Symfony\Component\OptionsResolver\Exception\AccessException;
use VNShipping\Vendor\Symfony\Component\OptionsResolver\Options;

final class OptionConfigurator {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var OptionsResolver
	 */
	private $resolver;

	/**
	 * OptionConfigurator constructor.
	 *
	 * @param string          $name
	 * @param OptionsResolver $resolver
	 */
	public function __construct( string $name, OptionsResolver $resolver ) {
		$this->name = $name;
		$this->resolver = $resolver;
		$this->resolver->setDefined( $name );
	}

	/**
	 * Force this option as integer.
	 *
	 * @return $this
	 */
	public function asInt( $nonNegative = true ) {
		return $this
			->allowedTypes( 'numeric' )
			->sanitize( $nonNegative ? 'absint' : 'intval' );
	}

	/**
	 * Force this option as numeric.
	 *
	 * @return $this
	 */
	public function asNumeric() {
		return $this->allowedTypes( 'numeric' );
	}

	/**
	 * Force this option as text.
	 *
	 * @return $this
	 */
	public function asString( $multiline = false ) {
		return $this
			->allowedTypes( 'string' )
			->sanitize( $multiline ? 'sanitize_textarea_field' : 'sanitize_text_field' );
	}

	/**
	 * Adds allowed types for this option.
	 *
	 * @param string ...$types One or more acceptable type.
	 * @return $this
	 *
	 * @throws AccessException If called from a lazy option or normalizer
	 */
	public function allowedTypes( string ...$types ): self {
		$this->resolver->setAllowedTypes( $this->name, $types );

		return $this;
	}

	/**
	 * Sets allowed values for this option.
	 *
	 * @param mixed ...$values One or more acceptable values/closures
	 * @return $this
	 *
	 * @throws AccessException If called from a lazy option or normalizer
	 */
	public function allowedValues( ...$values ): self {
		$this->resolver->setAllowedValues( $this->name, $values );

		return $this;
	}

	/**
	 * Sets the default value for this option.
	 *
	 * @param mixed $value The default value of the option
	 * @return $this
	 *
	 * @throws AccessException If called from a lazy option or normalizer
	 */
	public function default( $value ): self {
		$this->resolver->setDefault( $this->name, $value );

		return $this;
	}

	/**
	 * Defines an option configurator with the given name.
	 *
	 * @param string $option
	 * @return self
	 */
	public function define( string $option ): self {
		return $this->resolver->define( $option );
	}

	/**
	 * Marks this option as deprecated.
	 *
	 * @param string|Closure $message The deprecation message to use
	 * @return $this
	 */
	public function deprecated( $message = 'The option "%name%" is deprecated.' ): self {
		$this->resolver->setDeprecated( $message );

		return $this;
	}

	/**
	 * Sets the normalizer for this option.
	 *
	 * @return $this
	 *
	 * @throws AccessException If called from a lazy option or normalizer
	 */
	public function normalize( Closure $normalizer ): self {
		$this->resolver->setNormalizer( $this->name, $normalizer );

		return $this;
	}

	/**
	 * Marks this option as required.
	 *
	 * @return $this
	 *
	 * @throws AccessException If called from a lazy option or normalizer
	 */
	public function required(): self {
		$this->resolver->setRequired( $this->name );

		return $this;
	}

	/**
	 * Sets the sanitizer for this option.
	 *
	 * @return $this
	 *
	 * @throws AccessException If called from a lazy option or normalizer
	 */
	public function sanitize( callable $sanitizer ): self {
		$this->normalize(
			function ( Options $options, $value ) use ( $sanitizer ) {
				return $sanitizer( $value );
			}
		);

		return $this;
	}
}
