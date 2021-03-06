<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Commands\GitHubPrReminderCommand;

class HelloCommandTest extends TestCase
{
    /** @test */
    public function it_checks_the_hello_command_output(): void
    {
        $this->app->call((new GitHubPrReminderCommand())->getName());

        $this->assertTrue(strpos($this->app->output(), 'Love beautiful code? We do too.') !== false);
    }
}
