CREATE TABLE IF NOT EXISTS player_factions (rowId INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(16), lastRename VARCHAR(60) DEFAULT '', deathsUntilRaidable FLOAT, regenCooldown INT DEFAULT 0, lastDtrUpdate INT DEFAULT 0, open INT DEFAULT 0, friendlyFire INT DEFAULT 0, lives INT DEFAULT 0, balance INT DEFAULT 0, points INT DEFAULT 0, announcement VARCHAR(60) DEFAULT '');

CREATE TABLE IF NOT EXISTS players (rowId INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(16), xuid TEXT, lives INT, balance INT, factionRowId INT, rankId INT);

CREATE TABLE IF NOT EXISTS faction_claims(rowId INT PRIMARY KEY, factionRowId INT, worldName VARCHAR(16), firstCorner TEXT, secondCorner TEXT);