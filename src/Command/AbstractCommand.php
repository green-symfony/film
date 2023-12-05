<?php

namespace App\Command;

use GS\Command\Command\AbstractCommand as GSAbstractCommand;

abstract class AbstractCommand extends GSAbstractCommand
{
    public const DESCRIPTION = '!CHANGE ME!';


    //###> ABSTRACT REALIZATION ###

    /* AbstractCommand */
    protected static function getCommandDescription(): string
    {
        return static::DESCRIPTION;
    }

    /* AbstractCommand */
    protected static function getCommandHelp(): string
    {
        return static::DESCRIPTION;
    }

    //###< ABSTRACT REALIZATION ###
}
