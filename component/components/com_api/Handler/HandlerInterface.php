<?php
namespace Api\Handler;

use JInput as Input;

interface HandlerInterface
{
    public function handle(Input $input);
}