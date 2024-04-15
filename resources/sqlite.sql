-- #! sqlite

-- #{ mana
-- #  { setup
CREATE TABLE IF NOT EXISTS playerdata(
    playerid TEXT,
    maxmana INTEGER
)
-- #  }

-- #  { newdata
-- #  :playerid string
-- #  :maxmana int
INSERT INTO playerdata(playerid, maxmana) VALUES (:playerid, :maxmana)
-- #  }

-- #  { getdata
-- #  :playerid string
SELECT * FROM playerdata WHERE playerid = :playerid
-- #  }

-- #  { setmaxmana
-- #  :playerid string
-- #  :maxmana int
UPDATE playerdata SET maxmana = :maxmana WHERE playerid = :playerid
-- #  }
-- #}