<?php

interface VocabularyChecker
{
    function exists(string $word): bool;
}

class VocabularyCheckerImpl implements VocabularyChecker
{
    //I used to think snake case is for variable but I checked PSR12 said use camelCase :D
    private array $validWords = [];

    // The txt file is hardcoded. How about put the path in the constructor so can switch to different file when testing?
    // There is a service container pattern getting popular, where you can do dependency injection into the constructor. CodeIgniter did this.
    public function __construct()
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
            1) In production environment especially if we use auto scaling like kubernetes or auto scaling group, 
            the log should best be routed to new relic or datadog or similar. Even if log into local log file, 
            we won't know which app server node captures the error. In test challenge, it's fine to echo.
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
