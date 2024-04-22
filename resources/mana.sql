-- #! sqlite

-- #{ mana
-- #  { setup
CREATE TABLE IF NOT EXISTS playerdata(
    playerid TEXT,
    maxmana FLOAT,
    manaregen FLOAT
)
-- #  }

-- #  { newdata
-- #  :playerid string
-- #  :maxmana float
-- #  :manaregen float
INSERT INTO playerdata(playerid, maxmana, manaregen) VALUES (:playerid, :maxmana, :manaregen)
-- #  }

-- #  { getdata
-- #  :playerid string
SELECT * FROM playerdata WHERE LOWER(playerid) = LOWER(:playerid)
-- #  }

-- #  { setmaxmana
-- #  :playerid string
-- #  :maxmana float
UPDATE playerdata SET maxmana = :maxmana WHERE LOWER(playerid) = LOWER(:playerid)
-- #  }

-- #  { setmanaregen
-- #  :playerid string
-- #  :manaregen float
UPDATE playerdata SET manaregen = :manaregen WHERE LOWER(playerid) = LOWER(:playerid)
-- #  }
-- #}