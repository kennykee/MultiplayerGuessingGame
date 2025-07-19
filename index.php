<?php

/* Let's play the game */

require_once 'MultiplayerGuessingGameImpl.php';
require_once 'VocabularyCheckerImpl.php';

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
    /* Echoing the error message for convenience, but in production, this should be logged in log file */
    echo $e->getMessage();
}

/* Extract only those with $wordLength */
$fixedLengthWords = array_filter($words, function ($word) use ($wordLength) {
    return strlen($word) === $wordLength;
});

/* array_flip to make words as keys, then array_rand to get 5 random keys */
$randomWords = array_rand(array_flip($fixedLengthWords), $wordCount);

$gameInstance = new MultiplayerGuessingGameImpl(new VocabularyCheckerImpl(), $randomWords);

/* Add players */
$players = ['Ali', 'Abu', 'AhTan', 'AhKow', 'Muthu'];
foreach ($players as $playerName) {
    $gameInstance->addPlayer($playerName);
}

/* simulate players guessing words */
while ($gameInstance->isGameOver() === false) {

    /* Since the rule says it is NO turn rotation and anyone can submit as they like, so we just random choose 1 player. */
    $playerName = $players[array_rand($players)];

    /* Randomly choose a word from the chosen words */
    $randomWords = $words[array_rand($words)];
    $gameInstance->submitGuess($playerName, $randomWords);

    /* Print current game strings */
    echo ($randomWords . " => " . implode(",", $gameInstance->getGameStrings()) . "\n");
}

echo "Game Over!\n";
print_r(implode(",", $gameInstance->getGameStrings()));
print_r($gameInstance->getPlayers());
