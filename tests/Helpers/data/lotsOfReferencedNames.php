<?php

namespace FooNamespace;

use Doctrine\ORM\Mapping as ORM;
use UsedNamespace\UsedNameFooBar as UsedNameFooBarBaz;

class FooClass extends \ExtendedClass implements \ImplementedInterface, \SecondImplementedInterface, \ThirdImplementedInterface
{

	use \FullyQualified\SomeOtherTrait, SomeDifferentTrait, \FullyQualified\SometTotallyDifferentTrait;
	use SomeTrait;

	/** @ORM\Column(name="foo") */
	private $foo;

	/** @var Bar */
	private $bar;

	/** @var Lorem[]|Ipsum|null */
	private $baz;

	/** @var Rasmus|Lerdorf[]|null|string|self|\Foo\BarBaz */
	private $barz;

	private $boo = 1, $hoo = SomeClass::CLASS_CONSTANT, $doo = TYPE_ONE;

	const ARRAY = [
		ArrayKey1::CONSTANT => true,
		ArrayKey2::CONSTANT => true,
	];

	/**
	 * @param TypeHintedName $foo
	 * @param AnotherTypeHintedName[] $bar
	 * @return Returned_TypeHinted_Underscored_Name
	 */
	public function fooMethod(TypeHintedName $foo, array $bar)
	{
		try {
			$var = new ClassInstance();
			$var->objectMethod();
			StaticClass::staticMethod();
			throw new \Foo\Bar\SpecificException();
		} catch (\Foo\Bar\Baz\SomeOtherException $e) {
			throw $e;
		}

		callToFunction(FOO_CONSTANT);
		$baz = BAZ_CONSTANT;
		$lorem = new LoremClass;
		$ipsum = IpsumClass::IPSUM_CONSTANT;

		$array = [Hoo::HOO_CONSTANT, BAR_CONSTANT];

		new Integer();
		new Boolean();

		function (Bobobo ...$bobobo) : array {
			return $bobobo;
		};

		function (Dododo &$dododo) : array {
			return $dododo;
		};
	}

}

interface FooInterface extends \ExtendedInterface, \SecondExtendedInterface, \ThirdExtendedInterface
{
}

trait FooTrait
{

	use SomeTrait;

}

const TYPE_ONE = 1, TYPE_TWO = 2, TYPE_THREE = 3;
