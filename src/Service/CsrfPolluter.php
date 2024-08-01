<?php

namespace App\Service;

use App\Enum\Direction;

class CsrfPolluter
{
    /**
     * An array containing all alphanumeric characters (a-z, A-Z, 0-9).
     *
     * @var array
     */
    private array $alphaNumericCharacters;

    /**
     * An associative array where each alphanumeric character is a key and its corresponding value 
     * is its index in the `$alphaNumericCharacters` array.
     *
     * @var array
     */
    private array $characterIndices;

    /**
     * Initializes the CSRF token polluter.
     */
    public function __construct()
    {
        $this->alphaNumericCharacters = array_merge(
            range('a', 'z'),
            range('A', 'Z'),
            range('0', '9')
        );

        $this->characterIndices = array_flip($this->alphaNumericCharacters);
    }

    /**
     * Pollutes the given CSRF token by modifying a certain number of characters based on the specified direction.
     *
     * @param string $token The CSRF token to pollute.
     * @param Direction $direction The direction in which to apply the pollution (LEFT or RIGHT).
     * @return string The polluted token.
     */
    public function polluteToken(string $token, Direction $direction): string
    {
        $numberOfCharactersToPollute = $this->getPollutionLimit($token, $direction);

        return $this->applyPollution($token, $numberOfCharactersToPollute, $direction);
    }

    /**
     * Determines the number of characters to pollute in the token based on the direction.
     *
     * Inspects the token from the specified direction (LEFT or RIGHT) and counts the number 
     * of alphanumeric characters until a non-alphanumeric character is encountered. If the 
     * direction is RIGHT, the token is reversed before inspection, and the count is returned 
     * as the pollution limit.
     *
     * @param string $token The CSRF token to inspect.
     * @param Direction $direction The direction to inspect the token (LEFT or RIGHT).
     * @return int The number of characters to pollute.
     */
    private function getPollutionLimit(string $token, Direction $direction): int
    {
        $tokenToInspect = $direction === Direction::LEFT ? $token : strrev($token);

        for ($i = 0, $length = strlen($tokenToInspect); $i < $length; $i++) {
            if (!in_array($tokenToInspect[$i], $this->alphaNumericCharacters)) {
                return $i;
            }
        }

        return strlen($tokenToInspect);
    }

    /**
     * Applies pollution to the specified number of characters in the token based on the direction.
     * 
     * The method iterates over the token, replacing each alphanumeric character within the specified limit
     * with the next character in the predefined alphanumeric sequence. Characters that do not match the 
     * pattern `[a-yA-Y0-8]` are left unchanged. If the direction is RIGHT, the token is reversed before 
     * and after the pollution is applied.
     *
     * @param string $token The CSRF token to pollute.
     * @param int $numberOfCharactersToPollute The number of characters to pollute.
     * @param Direction $direction The direction in which to apply the pollution (LEFT or RIGHT).
     * @return string The polluted token.
     */
    private function applyPollution(string $token, int $numberOfCharactersToPollute, Direction $direction): string
    {
        $pollutedToken = '';

        $tokenToPollute = $direction === Direction::LEFT ? $token : strrev($token);

        for ($i = 0; $i < $numberOfCharactersToPollute; $i++) {
            $currentCharacter = $tokenToPollute[$i];

            if (!preg_match('/[a-yA-Y0-8]/', $currentCharacter)) {
                $pollutedToken .= $currentCharacter;
                continue;
            }

            $currentIndex = $this->characterIndices[$currentCharacter];

            // Wrap around if needed
            $nextIndex = ($currentIndex + 1) % count($this->alphaNumericCharacters);

            $pollutedToken .= $this->alphaNumericCharacters[$nextIndex];
        }

        $pollutedToken .= substr($tokenToPollute, $numberOfCharactersToPollute);

        return $direction === Direction::LEFT ? $pollutedToken : strrev($pollutedToken);
    }
}
