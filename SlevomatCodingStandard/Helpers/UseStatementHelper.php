<?php declare(strict_types = 1);

namespace SlevomatCodingStandard\Helpers;

class UseStatementHelper
{

	public static function isAnonymousFunctionUse(\PHP_CodeSniffer\Files\File $phpcsFile, int $usePointer): bool
	{
		$tokens = $phpcsFile->getTokens();
		$nextPointer = TokenHelper::findNextEffective($phpcsFile, $usePointer + 1);
		$nextToken = $tokens[$nextPointer];

		return $nextToken['code'] === T_OPEN_PARENTHESIS;
	}

	public static function isTraitUse(\PHP_CodeSniffer\Files\File $phpcsFile, int $usePointer): bool
	{
		$typePointer = TokenHelper::findPrevious($phpcsFile, array_merge(TokenHelper::$typeKeywordTokenCodes, [T_ANON_CLASS]), $usePointer);
		if ($typePointer !== null) {
			$tokens = $phpcsFile->getTokens();
			$typeToken = $tokens[$typePointer];
			$openerPointer = $typeToken['scope_opener'];
			$closerPointer = $typeToken['scope_closer'];

			return ($usePointer > $openerPointer && $usePointer < $closerPointer);
		}

		return false;
	}

	public static function getNameAsReferencedInClassFromUse(\PHP_CodeSniffer\Files\File $phpcsFile, int $usePointer): string
	{
		$endPointer = TokenHelper::findNext($phpcsFile, [T_SEMICOLON, T_COMMA], $usePointer + 1);
		$asPointer = TokenHelper::findNext($phpcsFile, T_AS, $usePointer + 1, $endPointer);
		if ($asPointer !== null) {
			$tokens = $phpcsFile->getTokens();
			return $tokens[TokenHelper::findNext($phpcsFile, T_STRING, $asPointer + 1)]['content'];
		}
		$name = self::getFullyQualifiedTypeNameFromUse($phpcsFile, $usePointer);

		return NamespaceHelper::getUnqualifiedNameFromFullyQualifiedName($name);
	}

	public static function getFullyQualifiedTypeNameFromUse(\PHP_CodeSniffer\Files\File $phpcsFile, int $usePointer): string
	{
		$tokens = $phpcsFile->getTokens();

		$nameEndPointer = TokenHelper::findNext($phpcsFile, [T_SEMICOLON, T_AS, T_COMMA], $usePointer + 1) - 1;
		if (in_array($tokens[$nameEndPointer]['code'], TokenHelper::$ineffectiveTokenCodes, true)) {
			$nameEndPointer = TokenHelper::findPreviousEffective($phpcsFile, $nameEndPointer);
		}
		$nameStartPointer = TokenHelper::findPreviousExcluding($phpcsFile, TokenHelper::$nameTokenCodes, $nameEndPointer - 1) + 1;

		$name = TokenHelper::getContent($phpcsFile, $nameStartPointer, $nameEndPointer);

		return NamespaceHelper::normalizeToCanonicalName($name);
	}

	/**
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile
	 * @param int $openTagPointer
	 * @return \SlevomatCodingStandard\Helpers\UseStatement[] canonicalName(string) => useStatement(\SlevomatCodingStandard\Helpers\UseStatement)
	 */
	public static function getUseStatements(\PHP_CodeSniffer\Files\File $phpcsFile, int $openTagPointer): array
	{
		$names = [];
		$tokens = $phpcsFile->getTokens();
		foreach (self::getUseStatementPointers($phpcsFile, $openTagPointer) as $usePointer) {
			$nextTokenFromUsePointer = TokenHelper::findNextEffective($phpcsFile, $usePointer + 1);
			$type = UseStatement::TYPE_DEFAULT;
			if ($tokens[$nextTokenFromUsePointer]['code'] === T_STRING) {
				if ($tokens[$nextTokenFromUsePointer]['content'] === 'const') {
					$type = UseStatement::TYPE_CONSTANT;
				} elseif ($tokens[$nextTokenFromUsePointer]['content'] === 'function') {
					$type = UseStatement::TYPE_FUNCTION;
				}
			}
			$name = self::getNameAsReferencedInClassFromUse($phpcsFile, $usePointer);
			$useStatement = new UseStatement(
				$name,
				self::getFullyQualifiedTypeNameFromUse($phpcsFile, $usePointer),
				$usePointer,
				$type
			);
			$names[$useStatement->getCanonicalNameAsReferencedInFile()] = $useStatement;
		}

		return $names;
	}

	/**
	 * Searches for all use statements in a file, skips bodies of classes and traits.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile
	 * @param int $openTagPointer
	 * @return int[]
	 */
	private static function getUseStatementPointers(\PHP_CodeSniffer\Files\File $phpcsFile, int $openTagPointer): array
	{
		$tokens = $phpcsFile->getTokens();
		$pointer = $openTagPointer + 1;
		$pointers = [];
		while (true) {
			$typesToFind = array_merge([T_USE], TokenHelper::$typeKeywordTokenCodes);
			$pointer = TokenHelper::findNext($phpcsFile, $typesToFind, $pointer);
			if ($pointer === null) {
				break;
			}

			$token = $tokens[$pointer];
			if (in_array($token['code'], TokenHelper::$typeKeywordTokenCodes, true)) {
				$pointer = $token['scope_closer'] + 1;
				continue;
			}
			if (self::isAnonymousFunctionUse($phpcsFile, $pointer)) {
				$pointer++;
				continue;
			}
			$pointers[] = $pointer;
			$pointer++;
		}
		return $pointers;
	}

}
