<?php

use PHPUnit\Framework\TestCase;

require_once 'MultiplayerGuessingGameImpl.php';
require_once 'VocabularyCheckerImpl.php';

class MultiplayerGuessingGameTest extends TestCase
{
    private MultiplayerGuessingGameImpl $game;
    private VocabularyCheckerImpl $vocabularyChecker;

    protected function setUp(): void
    {
        $this->vocabularyChecker = new VocabularyCheckerImpl(); // Assuming this is a valid class
        $this->game = new MultiplayerGuessingGameImpl($this->vocabularyChecker, ['apple', 'banan', 'cherr', 'datee', 'elder']);

        /* Adding test players */
        $players = ['Ali', 'Abu', 'AhTan', 'AhKow', 'Muthu', 'AhMao'];
        foreach ($players as $playerName) {
            $this->game->addPlayer($playerName);
        }
    }

    public function testAddPlayer(): void
    {
        $this->game->addPlayer('testPlayer');
        $players = $this->game->getPlayers();
        $this->assertArrayHasKey('testPlayer', $players);
        $this->assertEquals(0, $players['testPlayer']);
    }
    public function testSubmitGuess(): void
    {
        $this->game->submitGuess('Ali', 'apple');
        $gameStrings = $this->game->getGameStrings();
        $this->assertNotEmpty($gameStrings);
    }
    public function testGuessWord(): void
    {
        $this->game->guessWord('Ali', 'apple');
        $gameStrings = $this->game->getGameStrings();
        $this->assertContains('a****', $gameStrings);

        // Test for a word that is not in the chosen words
        $this->game->guessWord('Ali', 'grape');
    }
}
