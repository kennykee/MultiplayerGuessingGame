<?php

require_once 'MultiplayerGuessingGame.php';

class MultiplayerGuessingGameImpl implements MultiplayerGuessingGame
{
    private array $players = [];        /* To keep track of players name and scores */
    private array $chosenWords = [];    /* This will hold the words chosen for the game */
    private array $gameStrings = [];    /* Current state of the list */
    private array $usedWords = []; /* To keep track of words already guessed */
    private VocabularyChecker $vocabularyChecker;

    public function __construct(VocabularyChecker $vocabularyChecker, array $chosenWords = [])
    {
        $this->vocabularyChecker = $vocabularyChecker;
        $this->chosenWords = $chosenWords;  /* Initialize chosen words if provided */

        /* Init gameStrings with asterisk */
        $lengthOfString = strlen($this->chosenWords[0] ?? 0);

        for ($i = 0; $i < count($this->chosenWords); $i++) {
            /* In its starting state, the game should reveal only one character of each of the words. */
            $randomPosition = rand(0, $lengthOfString - 1);
            $gameString = str_repeat('*', $lengthOfString);
            $gameString[$randomPosition] = $this->chosenWords[$i][$randomPosition]; // Reveal one character
            $this->gameStrings[] = $gameString;
        }
    }

    public function getGameStrings(): array
    {
        return $this->gameStrings;
    }

    public function submitGuess(string $playerName, string $word)
    {
        /* Extract out the method to another guessWord method in case we need to unit test. */
        /* Return: Nothing to return, at the front end, use getGameStrings() to print out the stats. */
        $this->guessWord($playerName, $word);
    }


    public function addPlayer(string $name): void
    {
        if (!isset($this->players[$name])) {
            $this->players[$name] = 0; // Initialize player's score
        }
    }

    public function guessWord(string $playerName, string $word)
    {
        /* 
        * The Rules:
        * 1. For each $word submission, check in each list of chosen words. 
        *    If the $word char matches any of the char in the a chosen word, then reveal that char.
        * 2. 1 point for each char revealed. If exact match of the $word, then 10 points. 
        */

        if (in_array($word, $this->usedWords)) {
            return; /* Word already guessed, do nothing */
        }

        /* Wrong length */
        if (strlen($word) !== strlen($this->chosenWords[0] ?? 0)) {
            return; /* Invalid submission, do nothing */
        }

        /* 
            Rule: To be valid the submission must have matching characters in all the revealed positions for at 
            least one partially revealed word that forms the game e.g. if a partially revealed word is p**g*a**** then 
            programmer is a valid player submission but protracted is not (there is a mismatch at the 4th character g <> t).
        */

        $validSubmission = false;
        foreach ($this->gameStrings as $index => $gameString) {
            $validSubmission = true; // Assume valid until proven otherwise

            for ($i = 0; $i < strlen($gameString); $i++) {
                if ($gameString[$i] === '*') {
                    continue; // Skip revealed characters
                }
                if ($gameString[$i] !== $word[$i]) {
                    $validSubmission = false; // Mismatch found
                    break;
                }
            }

            if ($validSubmission) {
                break; // Valid submission found, no need to check further
            }

            /* As noted in section 3, a submission should NOT be validated against a fully revealed word. */
            if ($gameString === $word) {
                return;
            }
        }

        if ($this->vocabularyChecker->exists($word)) {
            $this->usedWords[] = $word;

            /* Calculate scores based on rules. Loop each gameStrings */
            foreach ($this->gameStrings as $index => $gameString) {

                /* IMPORTANT In the case of an exact match NO hidden characters in other words in the game should be revealed. */

                if (strpos($gameString, '*') !== false) {
                    $chosenWord = $this->chosenWords[$index];
                    $revealedChars = '';

                    for ($i = 0; $i < strlen($chosenWord); $i++) {
                        if ($chosenWord[$i] === $word[$i]) {
                            $revealedChars .= $chosenWord[$i];
                        } else {
                            $revealedChars .= $gameString[$i];
                        }
                    }

                    /* Update the game string */
                    $this->gameStrings[$index] = $revealedChars;

                    /* Update player's score only $validSubmission is true. Coz the rules say
                       - Matching hidden characters should be revealed in all the words comprising the game even if 
                         the submitted word is NOT a valid guess (see section 3 above) for that particular word in the 
                         game e.g. if *o*tn*** is another of the partially revealed words in the game, with footnote as 
                         the underlying word, then even though software is not a valid submission for this particular partially
                         revealed word (mismatch at the 5th character w <> n) the result of the submission is to reveal the 
                         final e resulting in: *o*tn**e.
                       - Meaning get points only if valid submission but still update the game strings
                    */
                    if ($validSubmission) {
                        if ($revealedChars === $chosenWord) {
                            $this->players[$playerName] += 10; // Exact match
                        } else {
                            $this->players[$playerName] += abs(substr_count($gameString, '*') - substr_count($revealedChars, '*')); // Count revealed characters 
                        }
                    }
                }
            }
        }
    }

    public function isGameOver(): bool
    {
        /* Game over when all words have been guessed*/
        foreach ($this->gameStrings as $gameString) {
            if (strpos($gameString, '*') !== false) {
                return false;
            }
        }
        return true;
    }

    public function getPlayers(): array
    {
        return $this->players;
    }
}
