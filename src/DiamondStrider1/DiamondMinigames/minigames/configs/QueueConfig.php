<?php

declare(strict_types=1);

namespace DiamondStrider1\DiamondMinigames\minigames\configs;

use DiamondStrider1\DiamondMinigames\data\attributes\IntType;

class QueueConfig extends BaseConfig
{
    #[IntType("teams", "The number of teams in this game")]
    public int $teams;
    #[IntType("team-members", "The number of players per team")]
    public int $teamMembers;
    #[IntType("min-teams", "The smallest number of teams for this game")]
    public int $minTeams;
    #[IntType("min-team-members", "The smallest number of players per team")]
    public int $minTeamMembers;
}
