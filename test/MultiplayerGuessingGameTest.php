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
        $this->game = new MultiplayerGuessingGameImpl($this->vocabularyChecker, ['apple', 'banana', 'cherry', 'date', 'elderberry']);

        // Adding players for testing
        $this->game->addPlayer('Ali');
        $this->game->addPlayer('Abu');
        $this->game->addPlayer('AhTan');
        $this->game->addPlayer('AhKow');
        $this->game->addPlayer('Muthu');
    }

    public function testAddPlayer()
    {
        $this->game->addPlayer('testPlayer');
        $players = $this->game->getPlayers();
        $this->assertArrayHasKey('testPlayer', $players);
        $this->assertEquals(0, $players['testPlayer']);
    }
    public function testSubmitGuess()
    {
        $this->game->submitGuess('Ali', 'apple');
        $gameStrings = $this->game->getGameStrings();
        $this->assertNotEmpty($gameStrings);
    }
}
