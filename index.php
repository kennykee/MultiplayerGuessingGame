<?php

/* Let's play the game */
require __DIR__ . '/vendor/autoload.php';
require_once 'MultiplayerGuessingGameImpl.php';
require_once 'VocabularyCheckerImpl.php';

use DI\ContainerBuilder;

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

//Since Azarina asked me to resubmit, then I just google and add DI, although CI4.5 and Laravel comes prebuilt with it.
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
