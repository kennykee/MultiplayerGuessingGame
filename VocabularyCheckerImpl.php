<?php

interface VocabularyChecker
{
    function exists(string $word): bool;
}

class VocabularyCheckerImpl implements VocabularyChecker
{
    //I used to think snake case is for variable but I checked PSR12 said use camelCase :D
    private array $validWords = [];

    public function __construct() // The txt file is hardcoded. How about put the path in the constructor so can switch to different file when testing?
    {
        try {
            $handle = fopen(__DIR__ . '/wordlist.txt', 'r', false);
            if ($handle !== false) {
                while (($line = fgets($handle)) !== false) {
                    $this->validWords[] = trim($line);
                }
                fclose($handle);
            } else {
                throw new Exception("Failed to open wordlist.txt");
            }
        } catch (Exception $e) {
            /* 
            1) In test challenge, it's fine to echo it but I think the message should be logged in the logfile 
            or piped to log server like New Relic or similar. error_log()
            2) Should we just terminate the execution with exit() since we can't load wordlist.txt?
             */
            echo $e->getMessage();
        }
    }

    public function exists(string $word): bool
    {
        return in_array($word, $this->validWords);
    }
}
