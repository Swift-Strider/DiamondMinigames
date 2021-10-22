# Welcome to DiamondMinigames

**DiamondMinigames gives you the power to make your dream minigame a reality.**
My aim is to provide you with all the essentials for any minigame server including: _kits, win objectives, custom script handlers, and a whole lot more._

**This is a pocketmine plugin for Minecraft Bedrock Edition.** Before using DiamondMinigames you will need a version of `pocketmine` of at least `v3.25.0`.

Take a look at `Feature Checklist`, and `How to Use` for information and examples.

# Feature Checklist

**[Not Implemented]** Provides kits on game start with a selection menu when multiple kits are available.

**[Not Implemented]** Lets you, the server admin, create basic minigames. This includes auto-filling chests with items (Duels, Skywars)

**[Not Implemented]** Gives you scripting tools to make more complex games. (Bedwars)

# How to Use

**TODO:** _The features in this section have yet to be implemented._

**Strategies** are how you tell DiamondMinigames how to handle your complex minigames.
Strategies come in multiple flavors like: `PlayerFillStrategy`, `KitStrategy`, and `WinStrategy`.

Imagine that you just had to have a good 1vs1 game for your server. You should employ the following:

- `TeamFill` with 2 teams and 1 player per team
- `KitVote` or `KitChoice`.
  - `KitVote` forces the kit with the popular vote to be used or a random selection of the kits that tied.
  - `KitChoice` allows every player to choose their own kit regardless of other players' votes
- `LastTeamStanding` to reward a team with a win after killing all other teams. In this scenario `LastTeamStanding` will be taken into effect after the first kill.

There are many other strategies that you can use to build your own custom minigame from the ground up. Look at [STRATEGIES.md](guides/STRATEGIES.md) to bolster your knowledge.

# Bug Reports and Help Requests

Go create an issue on Github for any concerns you may have.
