# Welcome to DiamondMinigames

<p align="center"><strong>DiamondMinigames gives you the power to make your dream minigame a reality.</strong></p>

My aim is to provide you with all the essentials for any minigame server including: <em>kits, win objectives, custom script handlers, and a whole lot more.</em>

<p align="center"><strong>This is a <a href="https://github.com/pmmp/PocketMine-MP"><code>pocketmine v4</code></a> plugin for Minecraft Bedrock Edition.</strong></p>

<p align="center"><strong>Take a look at <a href="#project-todos"><code>Project TODOs</code></a> and <a href="#about"><code>About</code></a> for information and examples.</strong></p>

<p align="center"><strong>See <a href="#contributing-and-question-asking"><code>Contributing and Question Asking</code></a> for info on how to contribute and learn about this project.</strong></p>

### Table of Contents

- [`Project TODOs`](#project-todos) lists features this project plans to add.
  - [`Optimizations`](#optimizations) is a list of TODOs that will makes this project more efficient.
- [`About`](#about) holds extra information on the core concepts of this plugin
  - [`Minigame Services`](#minigame-services) details the plugin's service system
  - [`Minigame Win Objectives`](#minigame-win-objectives) talks about win objectives thoroughly
- [`Contributing and Question Asking`](#contributing-and-question-asking) encourages you to use Github Issues for any problems you have or contributions you plan to make

# Project TODOs

- [ ] Support a basic minigame
  - [x] Create a region manager to backup and save parts of worlds for minigames
  - [ ] Design a modular minigame system
    - [ ] Support custom win-objectives. See [`Minigame Win Objectives`](#minigame-win-objectives) below.
    - [ ] Support a service system, where services (eg. `ChestLootService`) depend on one or more configurations (eg. `ChestLootConfig`) to be present on the minigame to bind to it (eg. if `ChestLootConfig` is put on the minigame, `ChestLootService` will fill chests with loot according to the schedule provided to `ChestLootConfig`). This will allow 3rd party plugins to easily interop with DiamondMinigames. See [`Minigame Services`](#minigame-services).

## Optimizations

|    System     | Imrovements                                                                                                                                     |
| :-----------: | :---------------------------------------------------------------------------------------------------------------------------------------------- |
| RegionManager | <ul><li>[ ] Save only the region selected, not the whole world file</li><li>[ ] Compress the world file, depending on a config option</li></ul> |

# About

The next sections talk about the core concepts of the DiamondMinigames project.

## Minigame Services

These are the building blocks of your minigames. Services may add any combination of mechanics (point scoring), win-objectives (win after scoring 50 points), and play-things (a chest, with restocking loot).

## Minigame Win Objectives

DiamondMinigames plans to support a win objective system. This allows you to configure many ways that your players might win. The first win objective to be met will **reward** the winning players and **end** the minigame.

# Contributing and Question Asking

Go create an issue on Github for any concerns, bug reports, questions about this plugin/project, or questions about contributing!
