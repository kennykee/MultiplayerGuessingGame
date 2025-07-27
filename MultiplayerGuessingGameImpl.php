<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'MultiplayerGuessingGame.php';
require_once 'VocabularyCheckerImpl.php';

use DI\ContainerBuilder;

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
            Rule: To be valid the submission, it must have matching characters in all the revealed positions for, at 
            least one partially revealed word that forms the game e.g. if a partially revealed word is p**g*a**** then 
            programmer is a valid player submission but protracted is not (there is a mismatch at the 4th character g <> t).
        */

        $validSubmission = false;

        foreach ($this->gameStrings as $index => $gameString) {
            $validSubmission = true; // Assume valid until proven otherwise

            for ($i = 0; $i < strlen($gameString); $i++) {
                if ($gameString[$i] === '*') {
                    continue; // Skip unrevealed characters
                }
                if ($gameString[$i] !== $word[$i]) {
                    $validSubmission = false; // Mismatch found
                    break;
                }
            }

            if ($validSubmission) {
                break; // At least 1 valid submission found, no need to check further
            }
        }

        /* As noted in section 3, a submission should NOT be validated against a fully revealed word. */
        if (in_array($word, $this->gameStrings)) {
            $validSubmission = false;
        }

        if ($this->vocabularyChecker->exists($word)) {
            $this->usedWords[] = $word;

            /* If there is a direct match, skip looping */
            /* IMPORTANT In the case of an exact match NO hidden characters in other words in the game should be revealed. */
            if ($validSubmission  && in_array($word, $this->chosenWords)) {
                $this->players[$playerName] += 10; // Exact match
                $this->gameStrings[$index] = $word;
                return;
            }

            /* Calculate scores based on rules. Loop each gameStrings */
            foreach ($this->gameStrings as $index => $gameString) {

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
                    if ($validSubmission === true) {
                        $this->players[$playerName] += abs(substr_count($gameString, '*') - substr_count($revealedChars, '*')); // Count revealed characters 
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

/************************
    Example usage:
    Run in command prompt by executing this file. Geektastic removed index.php so run this file directly.
    
    > php MultiplayerGuessingGameImpl.php

    How this works:
    1. It initializes the game with a vocabulary checker and a set of chosen words.
    2. It adds players to the game.
    3. It simulates players guessing words until the game is over.
    4. It prints the game strings and player scores at the end.

    Note: The score for each player will likely be very low because the rule says that submission only valid if it matches the revealed characters.

 ***********************/
class Demo
{
    public static function main()
    {
        $wordLength = 5; /* Assuming we want to guess words of length 5 */
        $wordCount = 5; /* Number of words to guess */
        $words = [];
        try {
            $handle = fopen(__DIR__ . '/wordlist.txt', 'r', false);
            if ($handle !== false) {
                while (($line = fgets($handle)) !== false) {
                    $words[] = trim($line);
                }
                fclose($handle);
            } else {
                throw new Exception("Failed to open wordlist.txt");
            }
        } catch (Exception $e) {
            /* Echoing the error message for convenience, but in production, this should be logged in server monitoring tools like NewRelic */
            echo $e->getMessage();
        }

        /* Extract only those with $wordLength */
        $fixedLengthWords = array_filter($words, function ($word) use ($wordLength) {
            return strlen($word) === $wordLength;
        });

        /* array_flip to make words as keys, then array_rand to get 5 random keys */
        $randomWords = array_rand(array_flip($fixedLengthWords), $wordCount);

        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->build();

        //Since Azarina asked me to resubmit, then I just google php-DI and add it, although CI4.5 and Laravel comes prebuilt with it.
        $gameServiceContainer = $container->make(MultiplayerGuessingGameImpl::class, [
            'vocabularyChecker' => new VocabularyCheckerImpl(),
            'chosenWords' => $randomWords
        ]);

        /* Add players */
        $players = ['Ali', 'Abu', 'AhTan', 'AhKow', 'Muthu'];
        foreach ($players as $playerName) {
            $gameServiceContainer->addPlayer($playerName);
        }

        /* simulate players guessing words */
        while ($gameServiceContainer->isGameOver() === false) {

            /* Since the rule says it is NO turn rotation and anyone can submit as they like, so we just random choose 1 player. */
            $playerName = $players[array_rand($players)];

            /* Randomly choose a word from the chosen words */
            $randomWords = $words[array_rand($words)];
            $gameServiceContainer->submitGuess($playerName, $randomWords);

            /* Print current game strings */
            echo ($randomWords . " => " . implode(",", $gameServiceContainer->getGameStrings()) . "\n");
        }

        echo "Game Over!\n";
        print_r(implode(",", $gameServiceContainer->getGameStrings()));
        print_r($gameServiceContainer->getPlayers());
    }
}

# Initialization
# > php MultiplayerGuessingGameImpl.php
if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME'])) { //Prevent running it when in unit test
    Demo::main();
}
