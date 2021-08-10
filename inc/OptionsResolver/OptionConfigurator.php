<?php

namespace VNShipping\OptionsResolver;

use Closure;
use VNShipping\Vendor\Symfony\Component\OptionsResolver\Exception\AccessException;
use VNShipping\Vendor\Symfony\Component\OptionsResolver\Options;
use VNShipping\Vendor\Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

final class OptionConfigurator {
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var SymfonyOptionsResolver
	 */
	private $resolver;

	/**
	 * @var array
	 */
	private $allowedTypes = [];

	/**
	 * OptionConfigurator constructor.
	 *
	 * @param string                 $name
	 * @param SymfonyOptionsResolver $resolver
	 */
	public function __construct( string $name, SymfonyOptionsResolver $resolver ) {
		$this->name = $name;
		$this->resolver = $resolver;
		$this->resolver->setDefined( $name );
	}

	/**
	 * @return $this
	 */
	public function nullable() {
		$this->allowedTypes( 'null' );

		return $this;
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
		$this->allowedTypes = array_merge( $this->allowedTypes, $types );

		$this->resolver->setAllowedTypes( $this->name, $this->allowedTypes );

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
