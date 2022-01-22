# Welcome to DiamondMinigames

**DiamondMinigames gives you the power to make your dream minigame a reality.**
My aim is to provide you with all the essentials for any minigame server including: _kits, win objectives, custom script handlers, and a whole lot more._

**This is a pocketmine plugin for Minecraft Bedrock Edition.** Before using DiamondMinigames you will need [`pocketmine`](https://github.com/pmmp/PocketMine-MP) `v4.0.0` or above.

Take a look at [`Project TODOs`](#project-todos), and [`About`](#about) for information and examples. See [`Contributing and Question Asking`](#contributing-and-question-asking) for info on how to contribute and learn about this project.

# Project TODOs

- [ ] Support a basic minigame
  - [x] Create a region manager to backup and save parts of worlds for minigames
  - [ ] Design a modular minigame system
    - [ ] Support two types of minigames, `Queued` or `Ongoing`. See [`Minigame Types`](#minigame-types) below.
    - [ ] Support custom win-objectives. See [`Minigame Win Objectives`](#minigame-win-objectives) below.

## Optimizations

|    System     | Imrovements                                                                                                                                     |
| :-----------: | :---------------------------------------------------------------------------------------------------------------------------------------------- |
| RegionManager | <ul><li>[ ] Save only the region selected, not the whole world file</li><li>[ ] Compress the world file, depending on a config option</li></ul> |

# About

The next sections talk about the core concepts of the DiamondMinigames project.

## Minigame Types

A checked box means the minigame type has been implemented.

- [ ] Queued
  - The player count is determined and fixed when the game starts (Think of BedWars).
  - Throughout the game players are eliminated, disqualifying them for a win.
  - Players may only rejoin after quitting this minigame, and will become a spectator if leaving the game caused them to be eliminated.
  - If all but one team is eliminated, that one team wins the game.
- [ ] Ongoing:
  - Players can join this game after it started unlike `Queued`.
  - These types of games may or may not have a win objective.
  - Players cannot be eliminated, so by default there is no winning (Think of SkyBlock)

For both types of minigames, once a win objective is met, the player/party is rewarded and the game ends.

## Minigame Win Objectives

DiamondMinigames plans to support a win objective system. This allows you to configure many ways that a player might win. The first win objective to be met will end the game.

- In a Queued minigame (see [Minigame Types](#minigame-types)) by default players win when they're team is the last to be eliminated, aka last team standing.
- You can set up other win objectives.
- The first win objective to be met (including the last-team-standing rule) will **reward** the winning players and **end** the minigame.

# Contributing and Question Asking

Go create an issue on Github for any concerns you may have, for any bug reports, questions of this plugin/project, or questions about contributing!
