-- #! sqlite

-- #{ mana
-- #  { setup
CREATE TABLE IF NOT EXISTS playerdata(
    playerid TEXT,
    maxmana INTEGER,
    manaregen INTEGER
)
-- #  }

-- #  { newdata
-- #  :playerid string
-- #  :maxmana int
-- #  :manaregen int
INSERT INTO playerdata(playerid, maxmana, manaregen) VALUES (:playerid, :maxmana, :manaregen)
-- #  }

-- #  { getdata
-- #  :playerid string
SELECT * FROM playerdata WHERE LOWER(playerid) = LOWER(:playerid)
-- #  }

-- #  { setmaxmana
-- #  :playerid string
-- #  :maxmana int
UPDATE playerdata SET maxmana = :maxmana WHERE LOWER(playerid) = LOWER(:playerid)
-- #  }

-- #  { setmanaregen
-- #  :playerid string
-- #  :manaregen int
UPDATE playerdata SET manaregen = :manaregen WHERE LOWER(playerid) = LOWER(:playerid)
-- #  }
-- #}