<?php


namespace Rudl\LibGitDb;


class UpdateRunner
{
    const ON_ERROR_SLEEP_TIME = 10;

    private $errorCount = 0;

    private $runCount = 0;

    private $currentRevison = null;
    
    public function __construct (
        private RudlGitDbClient $gitDbClient,
        private int $defaultSleepTime = 5
    ) {}

    public function runSingle(callable $fn)
    {
        $this->runCount++;
        try {
            while(($nextRev = $this->gitDbClient->getRevision()) === $this->currentRevison) {
                sleep($this->defaultSleepTime);
            }
            echo "[" . date ("Y-m-D H:i:s") . "] New revision $this->currentRevison -> $nextRev. Triggering update...\n";
            $fn($this);
            if ($this->errorCount > 0) {
                echo "[" . date ("Y-m-D H:i:s") . "] Recovered from previous error after " . ($this->errorCount * self::ON_ERROR_SLEEP_TIME) . " seconds System ok.\n";
            }
            echo "[" . date ("Y-m-D H:i:s") . "] Update successful\n";

            $this->errorCount = 0;
            $this->currentRevison = $nextRev;
            $this->gitDbClient->logOk("Update successful (Rev: $this->currentRevison)");
        } catch (\Exception|\Error $ex) {
            $this->gitDbClient->logError("Exception: " . $ex->getMessage());
            $this->errorCount++;
            echo "[" . date ("Y-m-D H:i:s") . "] Error: " . $ex->getMessage() . "\n";
        }

        sleep ($this->defaultSleepTime);
        sleep (self::ON_ERROR_SLEEP_TIME * $this->errorCount);
    }

    public function run(callable $fn)
    {
        while (true) {
            $this->runSingle($fn);
        }
    }

}