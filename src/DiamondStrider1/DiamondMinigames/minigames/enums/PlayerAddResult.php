<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\enums;

final class PlayerAddResult
{
    public const OK = 0;
    public const UNSPECIFIED_ERROR = 1;
    public const TOO_MANY_PLAYERS = 2;
    public const GAME_ALREADY_STARTED = 3;
}
