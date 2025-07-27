<?php

require __DIR__ . '/../vendor/autoload.php';
require_once 'MultiplayerGuessingGameImpl.php';
require_once 'VocabularyCheckerImpl.php';

use PHPUnit\Framework\TestCase;
use DI\ContainerBuilder;

/*
> vendor\bin\phpunit
*/

class MultiplayerGuessingGameTest extends TestCase
{
    private MultiplayerGuessingGameImpl $game;

    protected function setUp(): void
    {
        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->build();
        $this->game = $container->make(MultiplayerGuessingGameImpl::class, [
            'vocabularyChecker' => new VocabularyCheckerImpl(),
            'chosenWords' => ['apple', 'banan', 'cherr', 'datee', 'elder']
        ]);

        /* Adding test players */
        $players = ['Ali', 'Abu', 'AhTan', 'AhKow', 'Muthu', 'AhMao'];
        foreach ($players as $playerName) {
            $this->game->addPlayer($playerName);
        }
    }

    //Test add player
    public function testAddPlayer(): void
    {
        $this->game->addPlayer('testPlayer');
        $players = $this->game->getPlayers();
        $this->assertArrayHasKey('testPlayer', $players);
        $this->assertEquals(0, $players['testPlayer']); //0 score
    }

    //Test submit wrong guess
    public function testSubmitWrongGuess(): void
    {
        $this->game->submitGuess('Ali', 'grape');
        $gameStrings = $this->game->getGameStrings();
        $this->assertNotContains('grape', $gameStrings);
    }

    //Test submit right guess
    public function testSubmitRightGuess(): void
    {
        $this->game->submitGuess('AhTan', 'apple');
        $gameStrings = $this->game->getGameStrings();
        $this->assertContains('apple', $gameStrings);
    }

    //Test getGameStrings
    public function testGetGameStrings(): void
    {
        $gameStrings = $this->game->getGameStrings();
        $this->assertIsArray($gameStrings);
        $this->assertNotEmpty($gameStrings);
    }

    //Test score
    public function testScore(): void
    {
        $this->game->submitGuess('Ali', 'guava'); //Guava doesn't match any char in the list
        $this->game->submitGuess('AhTan', 'apple');

        $scores = $this->game->getPlayers();

        $this->assertEquals(0, $scores['Ali']);

        //If apple chars are not first random reveal then 11, else 10
        $this->assertContains($scores['AhTan'], [10, 11]);
    }
}
